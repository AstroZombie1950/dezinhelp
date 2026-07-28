<?php
// Определение города через DaData: по координатам браузера и, если их не дали,
// по IP. Файл ничего не выводит и ничего не решает — только ходит в сервис и
// приводит ответ к общему виду. Кто и когда его зовёт — см. source/php/detect.php.

// --- общий формат ответа ---
// Один и тот же массив у обоих способов, чтобы вызывающий код не разбирался, кто ответил.
function geo_result($source, $data = array(), $error = "")
{
	return array_merge(array(
		"source"  => $source,
		"ok"      => $error === "",
		"city"    => "",
		"region"  => "",
		"country" => "",
		"lat"     => null,
		"lon"     => null,
		"error"   => $error,
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
		// curl_close с PHP 8.5 deprecated, а с 8.0 и так ничего не делает
		return ($res === false || $code >= 400) ? null : $res;
	}
	$ctx = stream_context_create(array("http" => array(
		"timeout" => $timeout,
		"header"  => implode("\r\n", array_merge(array("User-Agent: dezinhelp.ru geo"), $headers)),
	)));
	$res = @file_get_contents($url, false, $ctx);
	return $res === false ? null : $res;
}

// --- город по IP ---
// Только российские адреса, нужен токен, 10 000 запросов в сутки бесплатно.
function geo_dadata($ip, $token)
{
	if ($token === "") {
		return geo_result("dadata", array(), "Нет токена: заполните dadata_token в config.php");
	}
	$url = "https://suggestions.dadata.ru/suggestions/api/4_1/rs/iplocate/address?ip=" . urlencode($ip);
	$raw = geo_http($url, 4, array("Accept: application/json", "Authorization: Token " . $token));
	if ($raw === null) {
		return geo_result("dadata", array(), "Сервис не ответил (сеть, лимит или неверный токен)");
	}
	$j = json_decode($raw, true);
	$d = isset($j["location"]["data"]) ? $j["location"]["data"] : null;
	if (!$d) {
		return geo_result("dadata", array(), "Адрес по этому IP не определён");
	}
	return geo_result("dadata", array(
		"city"    => (string) (isset($d["city"]) && $d["city"] !== null ? $d["city"] : ($d["settlement"] ?? "")),
		"region"  => (string) ($d["region_with_type"] ?? ""),
		"country" => (string) ($d["country"] ?? ""),
		"lat"     => isset($d["geo_lat"]) && $d["geo_lat"] !== null ? (float) $d["geo_lat"] : null,
		"lon"     => isset($d["geo_lon"]) && $d["geo_lon"] !== null ? (float) $d["geo_lon"] : null,
	));
}

// --- город по координатам браузера (обратная геокодировка) ---
// Тот же токен и лимит, что у «города по IP».
function geo_reverse_dadata($lat, $lon, $token)
{
	if ($token === "") {
		return geo_result("reverse-dadata", array(), "Нет токена");
	}
	$url = "https://suggestions.dadata.ru/suggestions/api/4_1/rs/geolocate/address?lat=" . urlencode($lat)
		. "&lon=" . urlencode($lon) . "&count=1";
	$raw = geo_http($url, 4, array("Accept: application/json", "Authorization: Token " . $token));
	if ($raw === null) {
		return geo_result("reverse-dadata", array(), "Сервис не ответил");
	}
	$j = json_decode($raw, true);
	$d = isset($j["suggestions"][0]["data"]) ? $j["suggestions"][0]["data"] : null;
	if (!$d) {
		return geo_result("reverse-dadata", array(), "Адрес не найден");
	}
	return geo_result("reverse-dadata", array(
		"city"    => (string) (isset($d["city"]) && $d["city"] !== null ? $d["city"] : ($d["settlement"] ?? "")),
		"region"  => (string) ($d["region_with_type"] ?? ""),
		"country" => (string) ($d["country"] ?? ""),
		"lat"     => (float) $lat,
		"lon"     => (float) $lon,
	));
}

// --- сверка названий ---
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
