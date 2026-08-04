<?php
// Разовая выгрузка структуры аккаунта amoCRM: воронки и этапы, дополнительные
// поля, пользователи, вебхуки, неразобранное, последние сделки с примечаниями.
// Только GET-запросы: ничего не создаёт и не меняет.
//
//   php tools/amo-dump.php <поддомен> <токен>
//   php tools/amo-dump.php <поддомен>          — токен из source/php/config.php
//                                                (amo_long_term_token, amo_token или amocrm_token)
//   php tools/amo-dump.php                     — ещё и поддомен из конфига (amo_subdomain)
//   ... --raw                                  — не маскировать телефоны и почты
//
// Если в панели нет кнопки долгосрочного токена — обменять код авторизации
// (живёт 20 минут) на токены:
//
//   php tools/amo-dump.php --auth <поддомен> <client_id> <секрет> <код> <redirect_uri>

if (PHP_SAPI !== "cli") { exit("Запускать только из командной строки.\n"); }

$args = array_slice($argv, 1);
$mask = true;
foreach ($args as $i => $a) {
	if ($a === "--raw") { $mask = false; unset($args[$i]); }
}
$args = array_values($args);

// поддомен принимаем и коротким, и полным
function amo_host($s)
{
	$s = trim(strtolower($s));
	$s = preg_replace("~^https?://~", "", $s);
	$s = rtrim($s, "/");
	return strpos($s, ".") === false ? $s . ".amocrm.ru" : $s;
}

// --- обмен кода авторизации на токены -----------------------------------

if (isset($args[0]) && $args[0] === "--auth") {
	if (count($args) < 6) { exit("Нужно: --auth <поддомен> <client_id> <секрет> <код> <redirect_uri>\n"); }
	$host = amo_host($args[1]);
	$ch = curl_init("https://" . $host . "/oauth2/access_token");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
	curl_setopt($ch, CURLOPT_TIMEOUT, 30);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
	curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(array(
		"client_id"     => $args[2],
		"client_secret" => $args[3],
		"grant_type"    => "authorization_code",
		"code"          => $args[4],
		"redirect_uri"  => $args[5],
	)));
	$body = (string) curl_exec($ch);
	$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	$json = json_decode($body, true);

	if ($code !== 200 || empty($json["access_token"])) {
		exit("Не вышло (HTTP " . $code . "):\n" . $body . "\n");
	}
	echo "access_token (живёт 24 часа, им и запускайте выгрузку):\n" . $json["access_token"] . "\n\n";
	echo "refresh_token (живёт 3 месяца, пригодится для боевой интеграции):\n" . $json["refresh_token"] . "\n";
	exit;
}

// --- доступы ------------------------------------------------------------

$configFile = __DIR__ . "/../source/php/config.php";
$config = file_exists($configFile) ? require $configFile : array();

// имена ключей в конфиге могли разойтись — принимаем несколько вариантов
function cfg($keys)
{
	foreach ($keys as $k) {
		if (!empty($GLOBALS["config"][$k])) { return trim($GLOBALS["config"][$k]); }
	}
	return "";
}

$HOST  = isset($args[0]) ? amo_host($args[0]) : amo_host(cfg(array("amo_subdomain", "amocrm_subdomain", "amo_host")));
$TOKEN = isset($args[1]) ? trim($args[1]) : cfg(array("amo_long_term_token", "amo_token", "amocrm_token"));

if ($HOST === "" || $HOST === ".amocrm.ru" || $TOKEN === "") {
	echo "Не хватает данных.\n";
	echo "  поддомен: " . ($HOST !== "" && $HOST !== ".amocrm.ru" ? $HOST : "не задан — передайте аргументом") . "\n";
	echo "  токен: " . ($TOKEN !== "" ? "найден" : "не найден ни в аргументах, ни в config.php") . "\n";
	exit("Запуск: php tools/amo-dump.php dezinhelp [токен]\n");
}

// --- запросы ------------------------------------------------------------

$errors = array();

function amo_url($url)
{
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_TIMEOUT, 30);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		"Authorization: Bearer " . $GLOBALS["TOKEN"],
		"Content-Type: application/json",
	));
	$body = (string) curl_exec($ch);
	$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	usleep(200000); // лимит API — 7 запросов в секунду, спешить некуда
	return array("code" => $code, "json" => json_decode($body, true), "body" => $body);
}

function amo_get($path, $query = array())
{
	$url = "https://" . $GLOBALS["HOST"] . $path;
	if ($query) { $url .= "?" . http_build_query($query); }
	$r = amo_url($url);
	if ($r["code"] === 204) { return array(); }
	if ($r["code"] !== 200) {
		$GLOBALS["errors"][] = $path . " → HTTP " . $r["code"] . " " . substr($r["body"], 0, 200);
		return null;
	}
	return $r["json"];
}

// списки отдаются постранично, идём по ссылке next.
// limit в запросе — размер страницы, а не всего: сколько страниц брать, решает $maxPages
function amo_list($path, $query, $key, $maxPages = 20)
{
	$items = array();
	$url = "https://" . $GLOBALS["HOST"] . $path . ($query ? "?" . http_build_query($query) : "");
	$page = 0;
	while ($url !== "" && $page < $maxPages) {
		$r = amo_url($url);
		if ($r["code"] === 204) { break; }
		if ($r["code"] !== 200) {
			$GLOBALS["errors"][] = $path . " → HTTP " . $r["code"] . " " . substr($r["body"], 0, 200);
			break;
		}
		if (!empty($r["json"]["_embedded"][$key])) {
			$items = array_merge($items, $r["json"]["_embedded"][$key]);
		}
		$url = isset($r["json"]["_links"]["next"]["href"]) ? $r["json"]["_links"]["next"]["href"] : "";
		$page++;
	}
	return $items;
}

// --- вывод --------------------------------------------------------------

function h($title)
{
	echo "\n\n===== " . $title . " " . str_repeat("=", max(0, 60 - strlen($title))) . "\n\n";
}

function dt($ts)
{
	return $ts ? date("d.m.Y H:i", (int) $ts) : "—";
}

// личные данные не должны уезжать в переписку целиком
function hide($v)
{
	if (!$GLOBALS["mask"]) { return $v; }
	$v = (string) $v;
	if (strpos($v, "@") !== false) {
		return preg_replace('/(?<=.).*(?=.@)/u', "***", $v);
	}
	if (preg_match_all('/\d/', $v) >= 10) {
		return preg_replace('/\d(?=\d{2})/', "*", $v);
	}
	return $v;
}

function val($field)
{
	$out = array();
	foreach ((array) ($field["values"] ?? array()) as $v) {
		$s = is_array($v) ? (string) ($v["value"] ?? "") : (string) $v;
		if ($s === "") { continue; }
		if (!empty($v["enum_code"])) { $s = $v["enum_code"] . ": " . $s; }
		$out[] = hide($s);
	}
	return implode(", ", $out);
}

echo "Аккаунт: " . $HOST . "\nВыгрузка: " . date("d.m.Y H:i") . ($mask ? " (телефоны и почты замаскированы)" : " (без маскировки)") . "\n";

// -- аккаунт
h("АККАУНТ");
$account = amo_get("/api/v4/account", array("with" => "amojo_id,users_groups,task_types,version"));
if ($account) {
	echo "id: " . ($account["id"] ?? "?") . "\n";
	echo "название: " . ($account["name"] ?? "?") . "\n";
	echo "валюта: " . ($account["currency"] ?? "?") . "\n";
	echo "версия API-аккаунта: " . ($account["version"] ?? "?") . "\n";
	foreach ((array) ($account["_embedded"]["users_groups"] ?? array()) as $g) {
		echo "группа: [" . $g["id"] . "] " . $g["name"] . "\n";
	}
	foreach ((array) ($account["_embedded"]["task_types"] ?? array()) as $t) {
		echo "тип задачи: [" . $t["id"] . "] " . $t["name"] . "\n";
	}
}

// -- пользователи
h("ПОЛЬЗОВАТЕЛИ");
$users = amo_list("/api/v4/users", array("limit" => 250, "with" => "role,group"), "users");
$userName = array();
foreach ($users as $u) {
	$userName[$u["id"]] = $u["name"];
	$admin = !empty($u["rights"]["is_admin"]) ? "администратор" : "менеджер";
	$active = !empty($u["rights"]["is_active"]) ? "активен" : "отключён";
	echo "[" . $u["id"] . "] " . $u["name"] . " — " . hide($u["email"] ?? "") . " — " . $admin . ", " . $active . "\n";
}

// -- воронки и этапы
h("ВОРОНКИ И ЭТАПЫ");
$pipelines = amo_list("/api/v4/leads/pipelines", array(), "pipelines");
$pipeName = array();
$statusName = array();
foreach ($pipelines as $p) {
	$pipeName[$p["id"]] = $p["name"];
	echo "[" . $p["id"] . "] " . $p["name"]
		. (!empty($p["is_main"]) ? " (основная)" : "")
		. (!empty($p["is_unsorted_on"]) ? ", неразобранное включено" : ", неразобранное выключено") . "\n";
	foreach ((array) ($p["_embedded"]["statuses"] ?? array()) as $s) {
		$statusName[$s["id"]] = $s["name"];
		echo "    [" . $s["id"] . "] " . $s["name"]
			. " (порядок " . $s["sort"] . ", тип " . $s["type"] . ")"
			. (empty($s["is_editable"]) ? " — системный" : "") . "\n";
	}
}

// -- дополнительные поля
foreach (array("leads" => "СДЕЛОК", "contacts" => "КОНТАКТОВ", "companies" => "КОМПАНИЙ") as $entity => $label) {
	h("ПОЛЯ " . $label);
	$fields = amo_list("/api/v4/" . $entity . "/custom_fields", array("limit" => 250), "custom_fields");
	foreach ($fields as $f) {
		echo "[" . $f["id"] . "] " . $f["name"] . " — тип " . $f["type"]
			. (!empty($f["code"]) ? ", код " . $f["code"] : "")
			. (!empty($f["is_required"]) ? ", обязательное" : "") . "\n";
		foreach ((array) ($f["enums"] ?? array()) as $e) {
			echo "    · [" . $e["id"] . "] " . $e["value"] . "\n";
		}
	}
	if (!$fields) { echo "(нет)\n"; }
}

// -- теги сделок
h("ТЕГИ СДЕЛОК");
$tags = amo_list("/api/v4/leads/tags", array("limit" => 250), "tags");
foreach ($tags as $t) { echo "[" . $t["id"] . "] " . $t["name"] . "\n"; }
if (!$tags) { echo "(нет)\n"; }

// -- вебхуки: по ним видно, что уже подключено к аккаунту
h("ВЕБХУКИ");
$hooks = amo_list("/api/v4/webhooks", array(), "webhooks");
foreach ($hooks as $w) {
	echo "[" . ($w["id"] ?? "?") . "] " . ($w["destination"] ?? "?")
		. " — события: " . implode(", ", (array) ($w["settings"] ?? array()))
		. ", создан " . dt($w["created_at"] ?? 0) . "\n";
}
if (!$hooks) { echo "(нет)\n"; }

// -- списки (каталоги)
h("СПИСКИ");
$catalogs = amo_list("/api/v4/catalogs", array("limit" => 250), "catalogs");
foreach ($catalogs as $c) {
	echo "[" . $c["id"] . "] " . $c["name"] . " — тип " . ($c["type"] ?? "?") . "\n";
}
if (!$catalogs) { echo "(нет)\n"; }

// -- неразобранное
h("НЕРАЗОБРАННОЕ (последние 10)");
$unsorted = amo_get("/api/v4/leads/unsorted", array("limit" => 10, "order[created_at]" => "desc"));
if ($unsorted !== null) {
	if (isset($unsorted["_total_items"])) { echo "всего в неразобранном: " . $unsorted["_total_items"] . "\n\n"; }
	$items = (array) ($unsorted["_embedded"]["unsorted"] ?? array());
	foreach ($items as $u) {
		echo "· " . dt($u["created_at"] ?? 0)
			. " — категория " . ($u["category"] ?? "?")
			. ", источник " . ($u["source_name"] ?? "—")
			. ", воронка " . ($pipeName[$u["pipeline_id"] ?? 0] ?? ($u["pipeline_id"] ?? "?")) . "\n";
		$meta = (array) ($u["metadata"] ?? array());
		foreach ($meta as $k => $v) {
			if (is_scalar($v)) { echo "      " . $k . ": " . hide($v) . "\n"; }
		}
	}
	if (!$items) { echo "(пусто)\n"; }
}

// -- последние сделки: по ним видно, что и как реально приходит
h("ПОСЛЕДНИЕ СДЕЛКИ (50)");
$leads = amo_list("/api/v4/leads", array(
	"limit"             => 50,
	"order[created_at]" => "desc",
	"with"              => "contacts,source_id",
), "leads", 1);

$contactIds = array();
foreach ($leads as $l) {
	foreach ((array) ($l["_embedded"]["contacts"] ?? array()) as $c) { $contactIds[$c["id"]] = true; }
}

// контакты добираем одним запросом, чтобы видеть телефоны и заполненные поля
$contacts = array();
if ($contactIds) {
	$q = array("limit" => 250);
	foreach (array_keys($contactIds) as $i => $id) { $q["filter[id][" . $i . "]"] = $id; }
	foreach (amo_list("/api/v4/contacts", $q, "contacts") as $c) { $contacts[$c["id"]] = $c; }
}

foreach ($leads as $l) {
	echo "· [" . $l["id"] . "] " . ($l["name"] ?? "—") . "\n";
	echo "      создана: " . dt($l["created_at"] ?? 0) . ", бюджет: " . ($l["price"] ?? 0) . "\n";
	echo "      воронка: " . ($pipeName[$l["pipeline_id"] ?? 0] ?? "?")
		. " → " . ($statusName[$l["status_id"] ?? 0] ?? ($l["status_id"] ?? "?")) . "\n";
	echo "      ответственный: " . ($userName[$l["responsible_user_id"] ?? 0] ?? ($l["responsible_user_id"] ?? "?")) . "\n";
	foreach ((array) ($l["custom_fields_values"] ?? array()) as $f) {
		$v = val($f);
		if ($v !== "") { echo "      поле «" . $f["field_name"] . "»: " . $v . "\n"; }
	}
	foreach ((array) ($l["_embedded"]["tags"] ?? array()) as $t) { echo "      тег: " . $t["name"] . "\n"; }
	foreach ((array) ($l["_embedded"]["contacts"] ?? array()) as $c) {
		$full = $contacts[$c["id"]] ?? null;
		echo "      контакт: " . ($full ? $full["name"] : "[" . $c["id"] . "]") . "\n";
		foreach ((array) ($full["custom_fields_values"] ?? array()) as $f) {
			$v = val($f);
			if ($v !== "") { echo "          " . $f["field_name"] . ": " . $v . "\n"; }
		}
	}
	echo "\n";
}
if (!$leads) { echo "(сделок нет)\n"; }

// -- примечания трёх последних сделок: покажут формат текущего потока заявок
h("ПРИМЕЧАНИЯ ТРЁХ ПОСЛЕДНИХ СДЕЛОК");
foreach (array_slice($leads, 0, 3) as $l) {
	echo "· сделка [" . $l["id"] . "] " . ($l["name"] ?? "") . "\n";
	$notes = amo_list("/api/v4/leads/" . $l["id"] . "/notes", array("limit" => 10, "order[created_at]" => "desc"), "notes");
	foreach ($notes as $n) {
		$text = $n["params"]["text"] ?? json_encode($n["params"] ?? array(), JSON_UNESCAPED_UNICODE);
		echo "      [" . ($n["note_type"] ?? "?") . "] " . dt($n["created_at"] ?? 0) . ": "
			. hide(mb_substr(preg_replace('/\s+/u', " ", (string) $text), 0, 300)) . "\n";
	}
	if (!$notes) { echo "      (примечаний нет)\n"; }
}

// -- ошибки
if ($errors) {
	h("ОШИБКИ ЗАПРОСОВ");
	foreach ($errors as $e) { echo "!! " . $e . "\n"; }
	echo "\n403 обычно значит, что интеграции не выдали соответствующее право доступа.\n";
}

echo "\n";
