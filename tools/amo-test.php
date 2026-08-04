<?php
// Проверка раскладки заявки по полям amoCRM. Ничего никуда не отправляет:
// собирает тело сделки по data/amo.json и печатает, во что превратились поля.
//
//   php tools/amo-test.php

if (PHP_SAPI !== "cli") { exit("Запускать только из командной строки.\n"); }

require __DIR__ . "/../source/php/amo.php";

$map = data_load("amo");
if (empty($map["fields"])) { exit("Нет data/amo.json — сгенерируйте карту соответствий.\n"); }

// обратные словари: по id значения показываем название, иначе в выводе одни числа
$byId = array();
foreach ($map["enums"] as $group => $pairs) {
	foreach ($pairs as $title => $id) { $byId[$id] = $group . ": " . $title; }
}
$fieldName = array_flip($map["fields"]) + array_flip($map["tracking"]);

$cases = array(
	"квиз, физлицо, 2-комнатная, тараканы" => array(
		"name" => "Иван", "phone" => "+7 (999) 123-45-67", "city" => "Балашиха",
		"source" => "Квиз — Обработка от клопов", "comment" => "",
		"quiz" => array(0 => array("физ. лицо"), 1 => array("Тараканы"),
			2 => array("2к. квартира"), 3 => array("Сегодня")),
		"track" => array("utm_source" => "yandex", "utm_medium" => "cpc",
			"landing" => "https://balashiha.dezinhelp.ru/?utm_source=yandex"),
	),
	"квиз, юрлицо, грызуны, склад" => array(
		"name" => "ООО Ромашка", "phone" => "+7 (495) 000-00-00", "city" => "Химки",
		"source" => "Квиз — главная",
		"quiz" => array(0 => array("юр. лицо"), 1 => array("Грызуны"),
			2 => array("Коммерческое помещение")),
		"track" => array(),
	),
	"страница услуги, без квиза" => array(
		"name" => "", "phone" => "+7 (926) 000-11-22", "city" => "Москва",
		"source" => "Услуга: Избавиться от крыс — герой",
		"service" => "izbavitsya-ot-krys",
		"track" => array("page" => "https://dezinhelp.ru/izbavitsya-ot-krys/"),
	),
	"город вне справочника amo" => array(
		"name" => "Пётр", "phone" => "+7 (903) 555-66-77", "city" => "Королёв",
		"source" => "Герой — заявка", "comment" => "перезвоните после 18",
		"track" => array(),
	),
);

foreach ($cases as $title => $order) {
	echo "\n=== " . $title . "\n";
	$lead = amo_build_lead($map, $order);

	$branch = "?";
	foreach ($map["pipelines"] as $key => $p) {
		if ((int) $p["pipeline_id"] === $lead["pipeline_id"]) { $branch = $key; }
	}
	echo "  название:  " . $lead["name"] . "\n";
	echo "  воронка:   " . $branch . " (" . $lead["pipeline_id"] . " → этап " . $lead["status_id"] . ")\n";
	echo "  тег:       " . $lead["_embedded"]["tags"][0]["name"] . "\n";

	foreach ($lead["custom_fields_values"] as $f) {
		$label = isset($f["field_id"]) ? ($fieldName[$f["field_id"]] ?? $f["field_id"]) : $f["field_code"];
		$vals = array();
		foreach ($f["values"] as $v) {
			$vals[] = isset($v["enum_id"]) ? ($byId[$v["enum_id"]] ?? ("enum " . $v["enum_id"])) : $v["value"];
		}
		echo "  " . str_pad($label, 10) . " " . implode(" + ", $vals) . "\n";
	}
}

echo "\n";
