<?php
// Город из поддомена. Единственный источник правды — data/cities.json:
// slug = поддомен, main = основной домен, index = отдавать ли город в поиск.
// Файл ничего не выводит: разбор хоста, поиск в реестре и подстановка {CITY...}.

// --- реестр ---
function city_registry()
{
	static $reg = null;
	if ($reg === null) {
		$file = __DIR__ . "/../../data/cities.json";
		$json = file_exists($file) ? json_decode(file_get_contents($file), true) : null;
		$reg  = is_array($json) ? $json : array();
		if (!isset($reg["cities"]) || !is_array($reg["cities"])) { $reg["cities"] = array(); }
	}
	return $reg;
}

// включённые города в порядке файла — для списка на главной
function city_list()
{
	$out = array();
	foreach (city_registry()["cities"] as $c) {
		if (!empty($c["enabled"])) { $out[] = $c; }
	}
	return $out;
}

// город основного домена; без него сайт не соберётся, поэтому подстраховка
function city_main()
{
	foreach (city_registry()["cities"] as $c) {
		if (!empty($c["main"])) { return $c; }
	}
	return array(
		"slug" => "", "name" => "", "in" => "", "genitive" => "", "to" => "",
		"prepositional" => "", "main" => true, "enabled" => true, "index" => true,
	);
}

// город по слагу; выключенные не отдаём — для роутера это 404
function city_by_slug($slug)
{
	if ($slug === "") { return null; }
	foreach (city_registry()["cities"] as $c) {
		if (isset($c["slug"]) && $c["slug"] === $slug && !empty($c["enabled"])) { return $c; }
	}
	return null;
}

// --- текущий запрос ---

// хост без порта, в нижнем регистре
function city_host()
{
	$host = isset($_SERVER["HTTP_HOST"]) ? strtolower((string) $_SERVER["HTTP_HOST"]) : "";
	return preg_replace('/:\d+$/', "", $host);
}

function city_scheme()
{
	$https = (!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off")
		|| (isset($_SERVER["HTTP_X_FORWARDED_PROTO"]) && $_SERVER["HTTP_X_FORWARDED_PROTO"] === "https")
		|| (isset($_SERVER["SERVER_PORT"]) && (string) $_SERVER["SERVER_PORT"] === "443");
	return $https ? "https" : "http";
}

// базовый домен: из конфига, иначе две последние метки текущего хоста.
// автоопределение держит рабочими и тестовый домен, и localhost без правки конфига
function city_base_domain()
{
	static $base = null;
	if ($base !== null) { return $base; }

	$file = __DIR__ . "/config.php";
	if (!file_exists($file)) { $file = __DIR__ . "/config.sample.php"; }
	$cfg  = file_exists($file) ? require $file : array();
	$base = isset($cfg["base_domain"]) ? strtolower(trim($cfg["base_domain"])) : "";

	if ($base === "") {
		$host  = city_host();
		$parts = explode(".", $host);
		// IP и односегментные хосты (localhost) базой считаем целиком
		$base = (count($parts) >= 2 && !filter_var($host, FILTER_VALIDATE_IP))
			? implode(".", array_slice($parts, -2))
			: $host;
	}
	return $base;
}

// Что за хост пришёл. Статусы:
//   main     — основной домен, город Москва
//   city     — городской поддомен из реестра
//   redirect — поддомен основного города (moskva.site.ru), уводим на основной домен
//   unknown  — поддомен не из реестра или выключен, это 404
function city_resolve($host = null, $base = null)
{
	$host = $host === null ? city_host() : $host;
	$base = $base === null ? city_base_domain() : $base;
	$main = city_main();

	$suffix = "." . $base;
	$sub    = "";
	if ($host !== $base && strlen($host) > strlen($suffix) && substr($host, -strlen($suffix)) === $suffix) {
		$sub = substr($host, 0, -strlen($suffix));
	}

	// основной домен и www — Москва
	if ($sub === "" || $sub === "www") {
		return array("status" => "main", "city" => $main, "sub" => "");
	}

	// moskva.site.ru — тот же контент, что на основном домене: дубль не плодим
	if ($sub === $main["slug"]) {
		$path = isset($_SERVER["REQUEST_URI"]) ? $_SERVER["REQUEST_URI"] : "/";
		return array(
			"status" => "redirect",
			"city"   => $main,
			"sub"    => $sub,
			"url"    => city_scheme() . "://" . $base . $path,
		);
	}

	$found = city_by_slug($sub);
	if ($found === null) {
		return array("status" => "unknown", "city" => $main, "sub" => $sub);
	}
	return array("status" => "city", "city" => $found, "sub" => $sub);
}

// адрес города: основной домен для Москвы, поддомен для остальных
function city_url($city, $path = "/")
{
	$base = city_base_domain();
	$host = !empty($city["main"]) ? $base : $city["slug"] . "." . $base;
	return city_scheme() . "://" . $host . $path;
}

// --- подстановка названий ---

// карта плейсхолдеров текущего города; ставится один раз из бутстрапа
function city_placeholders($city = null)
{
	static $map = array();
	if ($city !== null) {
		$map = array(
			"{CITY}"          => isset($city["name"]) ? $city["name"] : "",
			"{CITY_IN}"       => isset($city["in"]) ? $city["in"] : "",
			"{CITY_GENITIVE}" => isset($city["genitive"]) ? $city["genitive"] : "",
			"{CITY_TO}"       => isset($city["to"]) ? $city["to"] : "",
			"{CITY_PREP}"     => isset($city["prepositional"]) ? $city["prepositional"] : "",
		);
	}
	return $map;
}

// Колбэк буфера вывода: меняет плейсхолдеры во всём HTML страницы разом.
// Так их можно писать где угодно — в шаблонах, в JSON из админки, в alt и title, —
// и ни одно место нельзя забыть подключить.
function city_render($html)
{
	$map = city_placeholders();
	return $map ? strtr($html, $map) : $html;
}
