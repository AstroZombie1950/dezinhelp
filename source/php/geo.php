<?php
// Геолокация: движки определения города + сверка со списком наших населённых пунктов.
// Файл ничего не выводит и ни на что не завязан — только считает. Стенд geotest.php
// дёргает эти функции, боевая логика потом возьмёт отсюда же выбранную связку.

// --- общий формат ответа любого движка ---
// Один и тот же массив у всех источников, чтобы связки не знали, кто именно ответил.
function geo_result($source, $data = array(), $error = "")
{
	return array_merge(array(
		"source"   => $source,
		"ok"       => $error === "",
		"city"     => "",
		"region"   => "",
		"country"  => "",
		"lat"      => null,
		"lon"      => null,
		"accuracy" => null,   // метры, заполняет только браузер
		"ms"       => 0,      // время работы движка на сервере
		"error"    => $error,
	), $data);
}

// --- IP клиента ---
// За прокси хостинга реальный адрес приезжает в заголовке, а не в REMOTE_ADDR.
function geo_client_ip()
{
	foreach (array("HTTP_X_FORWARDED_FOR", "HTTP_X_REAL_IP", "HTTP_CF_CONNECTING_IP", "REMOTE_ADDR") as $key) {
		if (empty($_SERVER[$key])) { continue; }
		$candidate = trim(explode(",", $_SERVER[$key])[0]); // XFF бывает списком — берём первый
		if (filter_var($candidate, FILTER_VALIDATE_IP)) { return $candidate; }
	}
	return "";
}

// --- HTTP-запрос наружу ---
// curl, если есть, иначе file_get_contents. Возврат: тело ответа или null.
function geo_http($url, $timeout = 4, $headers = array())
{
	if (function_exists("curl_init")) {
		$ch = curl_init($url);
		curl_setopt_array($ch, array(
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_TIMEOUT        => $timeout,
			CURLOPT_USERAGENT      => "dezinhelp.ru geo",
			CURLOPT_HTTPHEADER     => $headers,
		));
		$res = curl_exec($ch);
		$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		return ($res === false || $code >= 400) ? null : $res;
	}
	$ctx = stream_context_create(array("http" => array(
		"timeout" => $timeout,
		"header"  => implode("\r\n", array_merge(array("User-Agent: dezinhelp.ru geo"), $headers)),
	)));
	$res = @file_get_contents($url, false, $ctx);
	return $res === false ? null : $res;
}

// === движок 1: Sypex Geo — локальная база на хостинге ===
// Сети не требует, работает за пару миллисекунд, коммерческое использование разрешено.
function geo_sxgeo($ip)
{
	$t0 = microtime(true);
	$db  = __DIR__ . "/geo/SxGeoCity.dat";
	$api = __DIR__ . "/geo/SxGeo.php";
	if (!file_exists($db) || !file_exists($api)) {
		return geo_result("sxgeo", array(), "База не установлена: положите SxGeoCity.dat в source/php/geo/");
	}
	require_once $api;

	// SXGEO_FILE — чтение с диска: 2 мс и ноль памяти.
	// Режим MEMORY грузит все 37 МБ в память на каждый запрос, шареду это ни к чему.
	$sx = new SxGeo($db, SXGEO_FILE);
	$r  = $sx->getCityFull($ip);
	if (empty($r["city"]["name_ru"])) {
		return geo_result("sxgeo", array("ms" => round((microtime(true) - $t0) * 1000, 1)), "IP в базе не найден");
	}
	return geo_result("sxgeo", array(
		"city"    => $r["city"]["name_ru"],
		"region"  => isset($r["region"]["name_ru"]) ? $r["region"]["name_ru"] : "",
		"country" => isset($r["country"]["name_ru"]) ? $r["country"]["name_ru"] : "",
		"lat"     => (float) $r["city"]["lat"],
		"lon"     => (float) $r["city"]["lon"],
		"ms"      => round((microtime(true) - $t0) * 1000, 1),
	));
}

// === движок 2: DaData — «город по IP» ===
// Только российские адреса, нужен токен, 10 000 запросов в сутки бесплатно.
// Отдаёт город с кодами ФИАС/КЛАДР — по ним наш список городов матчится однозначно.
function geo_dadata($ip, $token)
{
	$t0 = microtime(true);
	if ($token === "") {
		return geo_result("dadata", array(), "Нет токена: заполните dadata_token в config.php");
	}
	$url = "https://suggestions.dadata.ru/suggestions/api/4_1/rs/iplocate/address?ip=" . urlencode($ip);
	$raw = geo_http($url, 4, array("Accept: application/json", "Authorization: Token " . $token));
	$ms  = round((microtime(true) - $t0) * 1000, 1);
	if ($raw === null) {
		return geo_result("dadata", array("ms" => $ms), "Сервис не ответил (сеть, лимит или неверный токен)");
	}
	$j = json_decode($raw, true);
	$d = isset($j["location"]["data"]) ? $j["location"]["data"] : null;
	if (!$d) {
		return geo_result("dadata", array("ms" => $ms), "Адрес по этому IP не определён");
	}
	return geo_result("dadata", array(
		"city"    => (string) (isset($d["city"]) && $d["city"] !== null ? $d["city"] : ($d["settlement"] ?? "")),
		"region"  => (string) ($d["region_with_type"] ?? ""),
		"country" => (string) ($d["country"] ?? ""),
		"lat"     => isset($d["geo_lat"]) && $d["geo_lat"] !== null ? (float) $d["geo_lat"] : null,
		"lon"     => isset($d["geo_lon"]) && $d["geo_lon"] !== null ? (float) $d["geo_lon"] : null,
		"ms"      => $ms,
	));
}

// === движок 3: freeipapi — внешний сервис без ключа ===
// HTTPS, 60 запросов в минуту, коммерческое использование разрешено.
function geo_freeipapi($ip)
{
	$t0  = microtime(true);
	$raw = geo_http("https://free.freeipapi.com/api/json/" . urlencode($ip), 4, array("Accept: application/json"));
	$ms  = round((microtime(true) - $t0) * 1000, 1);
	if ($raw === null) {
		return geo_result("freeipapi", array("ms" => $ms), "Сервис не ответил");
	}
	$j = json_decode($raw, true);
	if (!is_array($j) || empty($j["cityName"])) {
		return geo_result("freeipapi", array("ms" => $ms), "Город не определён");
	}
	return geo_result("freeipapi", array(
		"city"    => (string) $j["cityName"],
		"region"  => isset($j["regionName"]) ? (string) $j["regionName"] : "",
		"country" => isset($j["countryName"]) ? (string) $j["countryName"] : "",
		"lat"     => isset($j["latitude"]) ? (float) $j["latitude"] : null,
		"lon"     => isset($j["longitude"]) ? (float) $j["longitude"] : null,
		"ms"      => $ms,
	));
}

// === движок 4: ip-api — ТОЛЬКО ДЛЯ СРАВНЕНИЯ ===
// На бесплатном тарифе коммерческое использование запрещено правилами сервиса,
// плюс он работает только по http. В боевую логику не берём, держим как эталон точности.
function geo_ipapi($ip)
{
	$t0     = microtime(true);
	$fields = "status,message,country,regionName,city,lat,lon,isp,mobile,proxy";
	$raw    = geo_http("http://ip-api.com/json/" . urlencode($ip) . "?lang=ru&fields=" . $fields, 4);
	$ms     = round((microtime(true) - $t0) * 1000, 1);
	if ($raw === null) {
		return geo_result("ipapi", array("ms" => $ms), "Сервис не ответил");
	}
	$j = json_decode($raw, true);
	if (!is_array($j) || (isset($j["status"]) && $j["status"] !== "success")) {
		return geo_result("ipapi", array("ms" => $ms), isset($j["message"]) ? $j["message"] : "Ошибка сервиса");
	}
	return geo_result("ipapi", array(
		"city"    => isset($j["city"]) ? (string) $j["city"] : "",
		"region"  => isset($j["regionName"]) ? (string) $j["regionName"] : "",
		"country" => isset($j["country"]) ? (string) $j["country"] : "",
		"lat"     => isset($j["lat"]) ? (float) $j["lat"] : null,
		"lon"     => isset($j["lon"]) ? (float) $j["lon"] : null,
		"ms"      => $ms,
	));
}

// === обратная геокодировка: координаты браузера → город ===

// DaData: тот же токен и лимит, что у «города по IP», данные по РФ подробные
function geo_reverse_dadata($lat, $lon, $token)
{
	$t0 = microtime(true);
	if ($token === "") {
		return geo_result("reverse-dadata", array(), "Нет токена");
	}
	$url = "https://suggestions.dadata.ru/suggestions/api/4_1/rs/geolocate/address?lat=" . urlencode($lat)
		. "&lon=" . urlencode($lon) . "&count=1";
	$raw = geo_http($url, 4, array("Accept: application/json", "Authorization: Token " . $token));
	$ms  = round((microtime(true) - $t0) * 1000, 1);
	if ($raw === null) {
		return geo_result("reverse-dadata", array("ms" => $ms), "Сервис не ответил");
	}
	$j = json_decode($raw, true);
	$d = isset($j["suggestions"][0]["data"]) ? $j["suggestions"][0]["data"] : null;
	if (!$d) {
		return geo_result("reverse-dadata", array("ms" => $ms), "Адрес не найден");
	}
	return geo_result("reverse-dadata", array(
		"city"    => (string) (isset($d["city"]) && $d["city"] !== null ? $d["city"] : ($d["settlement"] ?? "")),
		"region"  => (string) ($d["region_with_type"] ?? ""),
		"country" => (string) ($d["country"] ?? ""),
		"lat"     => (float) $lat,
		"lon"     => (float) $lon,
		"ms"      => $ms,
		"address" => isset($j["suggestions"][0]["value"]) ? $j["suggestions"][0]["value"] : "",
	));
}

// OpenStreetMap: без ключа, но лимит 1 запрос в секунду и просьба не грузить продакшеном
function geo_reverse_osm($lat, $lon)
{
	$t0  = microtime(true);
	$url = "https://nominatim.openstreetmap.org/reverse?format=json&accept-language=ru&zoom=13&lat="
		. urlencode($lat) . "&lon=" . urlencode($lon);
	$raw = geo_http($url, 5, array("Accept: application/json"));
	$ms  = round((microtime(true) - $t0) * 1000, 1);
	if ($raw === null) {
		return geo_result("reverse-osm", array("ms" => $ms), "Сервис не ответил");
	}
	$j = json_decode($raw, true);
	$a = isset($j["address"]) ? $j["address"] : null;
	if (!$a) {
		return geo_result("reverse-osm", array("ms" => $ms), "Адрес не найден");
	}
	// у OSM город лежит в разных полях в зависимости от типа населённого пункта
	$city = "";
	foreach (array("city", "town", "village", "municipality", "county") as $k) {
		if (!empty($a[$k])) { $city = $a[$k]; break; }
	}
	return geo_result("reverse-osm", array(
		"city"    => $city,
		"region"  => isset($a["state"]) ? $a["state"] : "",
		"country" => isset($a["country"]) ? $a["country"] : "",
		"lat"     => (float) $lat,
		"lon"     => (float) $lon,
		"ms"      => $ms,
		"address" => isset($j["display_name"]) ? $j["display_name"] : "",
	));
}

// === сверка с нашим списком населённых пунктов ===

// Приводим название к сравнимому виду: без «г»/«пос», без ё, в нижнем регистре.
// mbstring не используем — на шареде его может не быть, кириллицу опускаем таблицей.
function geo_norm_name($s)
{
	static $map = null;
	if ($map === null) {
		$up = preg_split('//u', "АБВГДЕЁЖЗИЙКЛМНОПРСТУФХЦЧШЩЪЫЬЭЮЯ", -1, PREG_SPLIT_NO_EMPTY);
		$lo = preg_split('//u', "абвгдеёжзийклмнопрстуфхцчшщъыьэюя", -1, PREG_SPLIT_NO_EMPTY);
		$map = array_combine($up, $lo);
	}
	$s = strtolower(strtr(trim($s), $map));
	$s = preg_replace('/^(г|гор|город|пос|посёлок|поселок|дер|деревня|рп|пгт)\.?\s+/u', "", $s);
	return str_replace("ё", "е", $s);
}

// Расстояние между точками по большому кругу, км
function geo_distance($lat1, $lon1, $lat2, $lon2)
{
	$r = 6371;
	$dLat = deg2rad($lat2 - $lat1);
	$dLon = deg2rad($lon2 - $lon1);
	$a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;
	return round($r * 2 * atan2(sqrt($a), sqrt(1 - $a)), 1);
}

// Ищем город в нашем списке: сперва по названию, потом по ближайшим координатам.
// Возвращает массив с точным совпадением, ближайшим городом и расстоянием до него.
// Дальше этого расстояния «ближайший город» смысла не имеет — это уже не наша зона
define("GEO_NEAR_KM", 100);

function geo_match($cities, $coords, $name, $lat = null, $lon = null)
{
	$out = array("exact" => "", "nearest" => "", "km" => null, "in_list" => false, "far" => false);

	if ($name !== "") {
		$n = geo_norm_name($name);
		foreach ($cities as $c) {
			if (geo_norm_name($c) === $n) {
				$out["exact"] = $c;
				$out["in_list"] = true;
				break;
			}
		}
	}

	if ($lat !== null && $lon !== null) {
		$best = null;
		foreach ($coords as $city => $ll) {
			$d = geo_distance($lat, $lon, $ll[0], $ll[1]);
			if ($best === null || $d < $best[1]) { $best = array($city, $d); }
		}
		if ($best !== null) {
			$out["nearest"] = $best[0];
			$out["km"] = $best[1];
			$out["far"] = $best[1] > GEO_NEAR_KM;
		}
	}

	return $out;
}