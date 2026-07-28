<?php
// Определение города посетителя: по координатам браузера, а если их не дали —
// по IP через DaData. Отвечает JSON и ничего не решает сам: редирект и поп-ап
// делает main.js. Ничего не сохраняет, кроме счётчика запросов для лимита.

header("Content-Type: application/json; charset=utf-8");
header("Cache-Control: no-store");

// конфиг: рабочий config.php, иначе шаблон
$configFile = __DIR__ . "/config.php";
$config = file_exists($configFile) ? require $configFile : require __DIR__ . "/config.sample.php";

require __DIR__ . "/city.php";
require __DIR__ . "/geo.php";
city_config($config);

// Лимит частоты: каждое определение — вызов DaData, а её квота 10 000 в сутки.
// В норме браузер спрашивает один раз на посетителя (дальше работает кука).
// IP — REMOTE_ADDR: заголовки подделываются, для лимита они не годятся
function detect_rate_ok($ip, $limit = 20, $window = 3600)
{
	if ($ip === "") { return true; }
	$file = __DIR__ . "/../../data/.geolimit.json";
	$fh = @fopen($file, "c+");
	if (!$fh) { return true; } // файл недоступен — определение важнее лимита
	flock($fh, LOCK_EX);
	$all = json_decode((string) stream_get_contents($fh), true);
	if (!is_array($all)) { $all = array(); }

	// старые отметки выбрасываем по всем адресам — файл не растёт
	$from = time() - $window;
	foreach ($all as $k => $times) {
		$all[$k] = array_values(array_filter((array) $times, function ($t) use ($from) { return $t > $from; }));
		if (!$all[$k]) { unset($all[$k]); }
	}

	$ok = count(isset($all[$ip]) ? $all[$ip] : array()) < $limit;
	if ($ok) { $all[$ip][] = time(); }

	ftruncate($fh, 0);
	rewind($fh);
	fwrite($fh, json_encode($all));
	flock($fh, LOCK_UN);
	fclose($fh);
	return $ok;
}

// Город из нашего реестра по названию от сервиса. Сравниваем нормализованно:
// «г. Королев» и «Королёв» — один и тот же город
function detect_city_by_name($name)
{
	if ($name === "") { return null; }
	$n = geo_norm_name($name);
	foreach (city_registry()["cities"] as $c) {
		if (empty($c["enabled"])) { continue; }
		if (geo_norm_name($c["name"]) === $n) { return $c; }
	}
	return null;
}

// ответ одного формата на все случаи: город, куда вести, и что вообще нашлось
function detect_answer($city, $found, $detected, $source, $error = "")
{
	echo json_encode(array(
		"ok"       => $error === "",
		"source"   => $source,
		"detected" => $detected,          // как город назвал сервис
		"found"    => $found,             // нашёлся ли он в нашем реестре
		"slug"     => $city["slug"],
		"name"     => $city["name"],
		"origin"   => rtrim(city_url($city, "/"), "/"), // схема + хост, путь добавит клиент
		"error"    => $error,
	), JSON_UNESCAPED_UNICODE);
	exit;
}

$main  = city_main();
$token = isset($config["dadata_token"]) ? (string) $config["dadata_token"] : "";

if (!detect_rate_ok(isset($_SERVER["REMOTE_ADDR"]) ? $_SERVER["REMOTE_ADDR"] : "")) {
	http_response_code(429);
	detect_answer($main, false, "", "limit", "Слишком много запросов");
}

// координаты браузера точнее IP, поэтому если они пришли — идём обратной геокодировкой
$lat = isset($_GET["lat"]) ? (float) $_GET["lat"] : null;
$lon = isset($_GET["lon"]) ? (float) $_GET["lon"] : null;
$hasCoords = $lat !== null && $lon !== null
	&& $lat >= -90 && $lat <= 90 && $lon >= -180 && $lon <= 180
	&& !($lat === 0.0 && $lon === 0.0);

if ($hasCoords) {
	$res = geo_reverse_dadata($lat, $lon, $token);
} else {
	$ip = geo_client_ip();
	$res = $ip === ""
		? geo_result("dadata", array(), "IP клиента не определён")
		: geo_dadata($ip, $token);
}

// сервис не ответил — остаёмся на основном домене, клиент просто покажет поп-ап
if (empty($res["ok"])) {
	detect_answer($main, false, "", $res["source"], $res["error"]);
}

// города нет в списке — открываем Москву (основной домен)
$city = detect_city_by_name($res["city"]);
detect_answer($city === null ? $main : $city, $city !== null, $res["city"], $res["source"]);
