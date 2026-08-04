<?php
// Обновляет справочники в data/amo.json из живого аккаунта amoCRM.
// Ручные таблицы (services, quiz, pipelines, mode…) берутся из текущей карты
// и не трогаются — обновляются только enums и проверяется, что всё, на что
// ссылаются ручные таблицы, в CRM ещё существует.
//
//   php tools/amo-map.php            — обновить data/amo.json
//   php tools/amo-map.php --check    — только проверить, ничего не писать

if (PHP_SAPI !== "cli") { exit("Запускать только из командной строки.\n"); }

require __DIR__ . "/../source/php/amo.php";

$checkOnly = in_array("--check", $argv, true);

$config = require __DIR__ . "/../source/php/config.php";
if (empty($config["amo_subdomain"])) { $config["amo_subdomain"] = "dezinhelp"; }
if (!amo_enabled($config)) { exit("Нет доступов amoCRM в config.php\n"); }

$file = __DIR__ . "/../data/amo.json";
$map  = json_decode((string) file_get_contents($file), true);
if (empty($map["fields"])) { exit("Не читается data/amo.json\n"); }

// справочники тянем по id полей из карты — так обновление не зависит от названий
$groups = array("city" => "city", "problem" => "problem", "object" => "object", "rooms" => "rooms");

$r = amo_request($config, "GET", "/api/v4/leads/custom_fields?limit=250");
if ($r["code"] !== 200) { exit("Поля не получены: HTTP " . $r["code"] . "\n"); }

$byId = array();
foreach ((array) ($r["json"]["_embedded"]["custom_fields"] ?? array()) as $f) {
	$byId[(int) $f["id"]] = $f;
}

$enums = array();
foreach ($groups as $key => $group) {
	$id = (int) ($map["fields"][$key] ?? 0);
	if (!$id || !isset($byId[$id])) { exit("Поля «" . $key . "» (id " . $id . ") в аккаунте нет\n"); }
	$pairs = array();
	foreach ((array) ($byId[$id]["enums"] ?? array()) as $e) {
		$pairs[$e["value"]] = (int) $e["id"];
	}
	$enums[$group] = $pairs;
}

// сверяем ручные таблицы: если заказчик переименовал значение, это всплывёт здесь,
// а не молча пустым полем в карточке
$bad = array();
foreach ((array) ($map["services"] ?? array()) as $slug => $names) {
	foreach ((array) $names as $n) {
		if (!isset($enums["problem"][$n])) { $bad[] = "услуга " . $slug . ": «" . $n . "»"; }
	}
}
foreach ((array) ($map["quiz"]["problem"] ?? array()) as $q => $names) {
	foreach ((array) $names as $n) {
		if (!isset($enums["problem"][$n])) { $bad[] = "квиз, проблема «" . $q . "»: «" . $n . "»"; }
	}
}
foreach ((array) ($map["quiz"]["object"] ?? array()) as $q => $v) {
	if (!empty($v["object"]) && !isset($enums["object"][$v["object"]])) { $bad[] = "квиз, объект «" . $q . "»: «" . $v["object"] . "»"; }
	if (!empty($v["rooms"]) && !isset($enums["rooms"][$v["rooms"]])) { $bad[] = "квиз, комнат «" . $q . "»: «" . $v["rooms"] . "»"; }
}

// id полей тоже проверяем: опечатка иначе всплыла бы только на бою
foreach (array_merge((array) $map["fields"], (array) ($map["tracking"] ?? array())) as $key => $id) {
	if (!isset($byId[(int) $id])) { $bad[] = "поля " . $id . " (" . $key . ") нет в аккаунте"; }
}

if ($bad) {
	echo "Расхождения с аккаунтом:\n  " . implode("\n  ", $bad) . "\n";
	exit(1);
}

$was = $map["enums"];
$map["enums"] = $enums;

foreach ($groups as $group) {
	$added = array_diff_key($enums[$group], (array) ($was[$group] ?? array()));
	$gone  = array_diff_key((array) ($was[$group] ?? array()), $enums[$group]);
	echo str_pad($group, 9) . count($enums[$group]) . " значений"
		. ($added ? ", добавилось: " . implode(", ", array_keys($added)) : "")
		. ($gone ? ", пропало: " . implode(", ", array_keys($gone)) : "") . "\n";
}

if ($checkOnly) { exit("\nПроверка пройдена, файл не менялся.\n"); }

file_put_contents($file, json_encode($map, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n");
echo "\ndata/amo.json обновлён\n";
