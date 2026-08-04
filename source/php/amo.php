<?php
// Отправка заявки в amoCRM: контакт (с поиском дубля по телефону) → сделка → примечание.
// Доступы — в config.php (amo_subdomain, amo_long_term_token), карта соответствий —
// в data/amo.json (её генерирует разведка tools/amo-dump.php, правится руками).
//
// Ошибки CRM не должны ронять заявку: вызывающий код смотрит на ["ok"], но в любом
// случае отвечает посетителю успехом, если ушло хотя бы одно уведомление.

require_once __DIR__ . "/data.php";

function amo_enabled($config)
{
	return !empty($config["amo_subdomain"]) && !empty($config["amo_long_term_token"]);
}

// пишем только отказы: разбираться придётся уже постфактум, по логу
function amo_log($message)
{
	$file = __DIR__ . "/../../data/.amo.log";
	if (file_exists($file) && filesize($file) > 262144) { @unlink($file); }
	@file_put_contents($file, date("d.m.Y H:i:s") . "  " . $message . "\n", FILE_APPEND | LOCK_EX);
}

function amo_request($config, $method, $path, $body = null)
{
	$url = "https://" . $config["amo_subdomain"] . ".amocrm.ru" . $path;
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
	curl_setopt($ch, CURLOPT_TIMEOUT, 8); // посетитель ждёт ответа формы — долго висеть нельзя
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		"Authorization: Bearer " . $config["amo_long_term_token"],
		"Content-Type: application/json",
	));
	if ($body !== null) {
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body, JSON_UNESCAPED_UNICODE));
	}
	$raw  = curl_exec($ch);
	$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	// curl_close не зовём: с PHP 8.5 он deprecated и ломает JSON-ответ страницы
	return array("code" => $code, "json" => json_decode((string) $raw, true), "raw" => (string) $raw);
}

// ё и регистр не должны мешать сопоставлению справочников
function amo_key($s)
{
	return str_replace("ё", "е", mb_strtolower(trim((string) $s)));
}

// значение списка по названию: справочник заказчика мы не перебираем, а ищем
function amo_enum($map, $group, $name)
{
	if ($name === "" || empty($map["enums"][$group])) { return 0; }
	$want = amo_key($name);
	foreach ($map["enums"][$group] as $title => $id) {
		if (amo_key($title) === $want) { return (int) $id; }
	}
	return 0;
}

function amo_field_text($map, $key, $value)
{
	$id = isset($map["fields"][$key]) ? (int) $map["fields"][$key] : 0;
	if (!$id || $value === "") { return null; }
	return array("field_id" => $id, "values" => array(array("value" => $value)));
}

function amo_field_enum($map, $key, $group, $names)
{
	$id = isset($map["fields"][$key]) ? (int) $map["fields"][$key] : 0;
	if (!$id) { return null; }
	$values = array();
	foreach ((array) $names as $n) {
		$enum = amo_enum($map, $group, $n);
		if ($enum) { $values[] = array("enum_id" => $enum); }
	}
	return $values ? array("field_id" => $id, "values" => $values) : null;
}

// номера сравниваем по последним 10 цифрам: 7XXX, 8XXX и +7XXX — один и тот же телефон
function amo_phone_key($value)
{
	$digits = preg_replace("/\D+/", "", (string) $value);
	return strlen($digits) > 10 ? substr($digits, -10) : $digits;
}

// В CRM номера пишем в формате 8 (999) 123-45-67 — так попросил заказчик,
// чтобы у всех источников был один вид и не расходился поиск дублей.
// На сайте маска остаётся +7: меняем только то, что уходит в amo
function amo_phone_display($value)
{
	$key = amo_phone_key($value);
	if (strlen($key) !== 10) { return (string) $value; }
	return "8 (" . substr($key, 0, 3) . ") " . substr($key, 3, 3) . "-" . substr($key, 6, 2) . "-" . substr($key, 8, 2);
}

// Ищем контакт по телефону: телефония и envybox уже создают карточки,
// без поиска один клиент расползётся на несколько контактов.
// Поиск в amo идёт по подстроке и по всем полям сразу — на «7968476» он вернул
// пять разных контактов. Поэтому мало найти, надо ещё убедиться, что у контакта
// действительно этот номер, иначе заявка прилипнет к чужой карточке.
function amo_find_contact($config, $map, $digits)
{
	$key = amo_phone_key($digits);
	if ($key === "") { return 0; }

	// ищем по последним 10 цифрам: по «8XXXXXXXXXX» amo ничего не находит, потому что
	// в базе номер лежит как «+7XXXXXXXXXX», а поиск строковый
	$r = amo_request($config, "GET", "/api/v4/contacts?" . http_build_query(array(
		"query" => $key,
		"limit" => 10,
	)));
	if ($r["code"] !== 200) { return 0; }

	$phoneField = (int) ($map["contact_fields"]["phone"] ?? 0);
	foreach ((array) ($r["json"]["_embedded"]["contacts"] ?? array()) as $contact) {
		foreach ((array) ($contact["custom_fields_values"] ?? array()) as $f) {
			$isPhone = (isset($f["field_id"]) && (int) $f["field_id"] === $phoneField)
				|| (isset($f["field_code"]) && $f["field_code"] === "PHONE");
			if (!$isPhone) { continue; }
			foreach ((array) ($f["values"] ?? array()) as $v) {
				if (amo_phone_key($v["value"] ?? "") === $key) { return (int) $contact["id"]; }
			}
		}
	}
	return 0; // совпадений по номеру нет — заведём новый контакт
}

// тело контакта — одинаковое и для отдельного создания, и для Неразобранного
function amo_contact_payload($map, $name, $phone)
{
	$phone = amo_phone_display($phone);
	$fields = array(array(
		"field_id" => (int) ($map["contact_fields"]["phone"] ?? 0),
		"values"   => array(array(
			"value"     => $phone,
			"enum_code" => $map["phone_enum"] ?? "MOB",
		)),
	));

	// согласие на обработку данных: во всех формах оно есть (галочкой или текстом
	// под кнопкой), а в CRM это единственное место, где факт согласия зафиксирован
	if (!empty($map["contact_fields"]["consent"])) {
		$fields[] = array(
			"field_id" => (int) $map["contact_fields"]["consent"],
			"values"   => array(array("value" => true)),
		);
	}

	return array(
		"name"                 => $name !== "" ? $name : $phone,
		"custom_fields_values" => $fields,
	);
}

function amo_create_contact($config, $map, $name, $phone)
{
	$contact = amo_contact_payload($map, $name, $phone);
	$contact["responsible_user_id"] = (int) ($map["responsible_user_id"] ?? 0);
	$r = amo_request($config, "POST", "/api/v4/contacts", array($contact));
	if ($r["code"] !== 200) {
		amo_log("контакт не создан: HTTP " . $r["code"] . " " . substr($r["raw"], 0, 300));
		return 0;
	}
	return (int) ($r["json"]["_embedded"]["contacts"][0]["id"] ?? 0);
}

/**
 * Собирает сделку по карте соответствий — без единого запроса наружу,
 * чтобы раскладку по полям можно было проверять локально (tools/amo-test.php).
 *
 * $order:
 *   name, phone, city, source, comment, service (slug),
 *   quiz  — array(индекс шага => array(тексты ответов)),
 *   track — array(utm_source, utm_medium, …, landing, page, referrer)
 */
function amo_build_lead($map, $order)
{
	$quiz = (array) ($order["quiz"] ?? array());
	$steps = (array) ($map["quiz"]["steps"] ?? array());

	// разбираем ответы квиза по ролям шагов
	$roles = array();
	foreach ($steps as $index => $role) {
		if (!empty($quiz[$index])) { $roles[$role] = (array) $quiz[$index]; }
	}

	// физ- или юрлицо решает, в какую воронку класть
	$branch = "fiz";
	if (!empty($roles["legal"][0])) {
		$answer = amo_key($roles["legal"][0]);
		foreach ((array) ($map["quiz"]["legal"] ?? array()) as $text => $key) {
			if (amo_key($text) === $answer && isset($map["pipelines"][$key])) { $branch = $key; }
		}
	}
	$pipeline = $map["pipelines"][$branch];

	// «Какая проблема?»: ответ квиза точнее страницы услуги, поэтому он в приоритете
	$problems = array();
	if (!empty($roles["problem"])) {
		foreach ($roles["problem"] as $answer) {
			foreach ((array) ($map["quiz"]["problem"] ?? array()) as $text => $names) {
				if (amo_key($text) === amo_key($answer)) { $problems = array_merge($problems, (array) $names); }
			}
		}
	} elseif (!empty($order["service"]) && isset($map["services"][$order["service"]])) {
		$problems = (array) $map["services"][$order["service"]];
	}

	$objectName = "";
	$roomsName  = "";
	if (!empty($roles["object"][0])) {
		foreach ((array) ($map["quiz"]["object"] ?? array()) as $text => $v) {
			if (amo_key($text) === amo_key($roles["object"][0])) {
				$objectName = $v["object"] ?? "";
				$roomsName  = $v["rooms"] ?? "";
			}
		}
	}

	$fields = array();
	foreach (array(
		amo_field_text($map, "name", (string) ($order["name"] ?? "")),
		amo_field_text($map, "info", (string) ($order["comment"] ?? "")),
		amo_field_text($map, "when", !empty($roles["when"]) ? implode(", ", $roles["when"]) : ""),
		amo_field_text($map, "referer", (string) ($order["track"]["landing"] ?? $order["track"]["page"] ?? "")),
		amo_field_enum($map, "city", "city", array($order["city"] ?? "")),
		amo_field_enum($map, "problem", "problem", $problems),
		amo_field_enum($map, "object", "object", array($objectName)),
		amo_field_enum($map, "rooms", "rooms", array($roomsName)),
	) as $f) {
		if ($f !== null) { $fields[] = $f; }
	}

	// рекламные метки: у этих полей is_api_only, заполнить их можно только отсюда
	foreach ((array) ($map["tracking"] ?? array()) as $key => $fieldId) {
		$v = (string) ($order["track"][$key] ?? "");
		if ($v !== "") {
			$fields[] = array("field_id" => (int) $fieldId, "values" => array(array("value" => $v)));
		}
	}

	$title = trim((string) ($order["source"] ?? "Заявка с сайта"));
	if (!empty($order["city"])) { $title .= " — " . $order["city"]; }

	return array(
		"name"                 => $title,
		"pipeline_id"          => (int) $pipeline["pipeline_id"],
		"status_id"            => (int) $pipeline["status_id"],
		"responsible_user_id"  => (int) ($map["responsible_user_id"] ?? 0),
		"custom_fields_values" => $fields,
		"_embedded"            => array("tags" => array(array("name" => $map["tag"] ?? "сайт"))),
	);
}

// примечание: способ связи, ответы квиза, комментарий — часть из этого в полях не помещается
function amo_add_note($config, $leadId, $text)
{
	if (!$leadId || $text === "") { return; }
	amo_request($config, "POST", "/api/v4/leads/" . $leadId . "/notes", array(array(
		"note_type" => "common",
		"params"    => array("text" => $text),
	)));
}

/**
 * Отправка заявки. Режим задаётся в data/amo.json полем mode:
 *   unsorted — заявка попадает в «Неразобранное» воронки, менеджер принимает её руками
 *              (так просил заказчик; так же работала и интеграция Тильды до неё)
 *   leads    — сделка сразу на этап воронки, минуя Неразобранное
 * $order — как в amo_build_lead, плюс note (текст примечания) и ip
 */
function amo_send($config, $order)
{
	if (!amo_enabled($config)) { return array("ok" => false, "error" => "off"); }

	$map = data_load("amo");
	if (empty($map["fields"]) || empty($map["pipelines"])) {
		amo_log("нет карты соответствий data/amo.json");
		return array("ok" => false, "error" => "map");
	}

	$lead = amo_build_lead($map, $order);
	$name  = (string) ($order["name"] ?? "");
	$phone = (string) ($order["phone"] ?? "");

	// контакт ищем в обоих режимах: телефония и envybox уже наплодили карточек,
	// и заявка должна лечь к существующему клиенту, а не завести двойника
	$digits    = preg_replace("/\D+/", "", $phone);
	$contactId = $digits !== "" ? amo_find_contact($config, $map, $digits) : 0;

	if (($map["mode"] ?? "unsorted") === "unsorted") {
		return amo_send_unsorted($config, $map, $order, $lead, $contactId, $name, $phone);
	}

	if (!$contactId) {
		$contactId = amo_create_contact($config, $map, $name, $phone);
	}
	if ($contactId) {
		$lead["_embedded"]["contacts"] = array(array("id" => $contactId));
	}

	$r = amo_request($config, "POST", "/api/v4/leads", array($lead));
	if ($r["code"] !== 200) {
		amo_log("сделка не создана: HTTP " . $r["code"] . " " . substr($r["raw"], 0, 500));
		return array("ok" => false, "error" => "lead");
	}
	$leadId = (int) ($r["json"]["_embedded"]["leads"][0]["id"] ?? 0);
	amo_add_note($config, $leadId, (string) ($order["note"] ?? ""));

	return array("ok" => true, "lead_id" => $leadId, "contact_id" => $contactId);
}

// «Неразобранное»: отдельный метод API. Этап не указывается — заявка ложится
// в предбанник воронки, и сделка появляется, только когда менеджер её примет
function amo_send_unsorted($config, $map, $order, $lead, $contactId, $name, $phone)
{
	$pipelineId = $lead["pipeline_id"];
	unset($lead["pipeline_id"], $lead["status_id"], $lead["responsible_user_id"]);

	$contact = $contactId
		? array("id" => $contactId)
		: amo_contact_payload($map, $name, $phone);

	$page = (string) ($order["track"]["page"] ?? $order["track"]["landing"] ?? "");

	$body = array(array(
		"source_name" => $map["source_name"] ?? "Сайт",
		"source_uid"  => "site-" . bin2hex(random_bytes(8)),
		"pipeline_id" => $pipelineId,
		"created_at"  => time(),
		"metadata"    => array(
			"ip"           => (string) ($order["ip"] ?? ""),
			"form_id"      => $map["form_id"] ?? "site",
			"form_name"    => (string) ($order["source"] ?? "Заявка с сайта"),
			"form_page"    => $page,
			"form_sent_at" => time(),
			"referer"      => (string) ($order["track"]["referrer"] ?? ""),
		),
		"_embedded" => array(
			"leads"    => array($lead),
			"contacts" => array($contact),
		),
	));

	$r = amo_request($config, "POST", "/api/v4/leads/unsorted/forms", $body);
	if ($r["code"] !== 200) {
		amo_log("неразобранное не создано: HTTP " . $r["code"] . " " . substr($r["raw"], 0, 500));
		return array("ok" => false, "error" => "unsorted");
	}

	$created = $r["json"]["_embedded"]["unsorted"][0] ?? array();
	$leadId  = (int) ($created["_embedded"]["leads"][0]["id"] ?? 0);
	amo_add_note($config, $leadId, (string) ($order["note"] ?? ""));

	return array(
		"ok"         => true,
		"uid"        => (string) ($created["uid"] ?? ""),
		"lead_id"    => $leadId,
		"contact_id" => (int) ($created["_embedded"]["contacts"][0]["id"] ?? $contactId),
	);
}
