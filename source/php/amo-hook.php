<?php
// Приёмник вебхуков amoCRM: когда сделка доходит до финального этапа
// («Передано мастеру»), шлём сообщение в Telegram-чат.
//
// Подписи у вебхуков amoCRM нет, поэтому адрес защищён секретом в строке запроса:
//   https://dezinhelp.ru/source/php/amo-hook.php?key=<amo_hook_secret из config.php>
// Этапы, на которые реагируем, перечислены в data/amo.json → notify_statuses.

$configFile = __DIR__ . "/config.php";
$config = file_exists($configFile) ? require $configFile : require __DIR__ . "/config.sample.php";

$secret = $config["amo_hook_secret"] ?? "";
if ($secret === "" || !isset($_GET["key"]) || !hash_equals($secret, (string) $_GET["key"])) {
	http_response_code(403);
	exit;
}

require __DIR__ . "/amo.php";
require __DIR__ . "/telegram.php";

// amoCRM шлёт x-www-form-urlencoded вида leads[status][0][...]
$changed = $_POST["leads"]["status"] ?? array();
if (!is_array($changed) || !$changed) {
	echo "no data";
	exit;
}

$map     = data_load("amo");
$notify  = array_map("intval", (array) ($map["notify_statuses"] ?? array()));
$account = $config["amo_subdomain"] ?? "";

foreach ($changed as $lead) {
	if (!is_array($lead)) { continue; }
	$statusId = (int) ($lead["status_id"] ?? 0);
	$leadId   = (int) ($lead["id"] ?? 0);
	if (!$leadId || !in_array($statusId, $notify, true)) { continue; }

	$lines = array("🔧 Передано мастеру: " . (string) ($lead["name"] ?? "сделка " . $leadId));

	// подробности добираем из API: в вебхуке названий полей и телефона нет
	$r = amo_request($config, "GET", "/api/v4/leads/" . $leadId . "?with=contacts");
	if ($r["code"] === 200) {
		$want = array("city", "problem", "object", "rooms", "when");
		$ids  = array();
		foreach ($want as $key) {
			if (!empty($map["fields"][$key])) { $ids[(int) $map["fields"][$key]] = true; }
		}
		foreach ((array) ($r["json"]["custom_fields_values"] ?? array()) as $f) {
			if (!isset($ids[(int) ($f["field_id"] ?? 0)])) { continue; }
			$values = array();
			foreach ((array) $f["values"] as $v) {
				if (isset($v["value"]) && $v["value"] !== "") { $values[] = $v["value"]; }
			}
			if ($values) { $lines[] = $f["field_name"] . ": " . implode(", ", $values); }
		}

		$contactId = (int) ($r["json"]["_embedded"]["contacts"][0]["id"] ?? 0);
		if ($contactId) {
			$c = amo_request($config, "GET", "/api/v4/contacts/" . $contactId);
			if ($c["code"] === 200) {
				$lines[] = "👤 " . (string) ($c["json"]["name"] ?? "");
				foreach ((array) ($c["json"]["custom_fields_values"] ?? array()) as $f) {
					if ((string) ($f["field_code"] ?? "") !== "PHONE") { continue; }
					foreach ((array) $f["values"] as $v) {
						if (!empty($v["value"])) { $lines[] = "📞 " . $v["value"]; }
					}
				}
			}
		}
	}

	if ($account !== "") {
		$lines[] = "https://" . $account . ".amocrm.ru/leads/detail/" . $leadId;
	}

	if (!tg_send($config, implode("\n", $lines))) {
		amo_log("уведомление о сделке " . $leadId . " не ушло в Telegram");
	}
}

echo "ok";
