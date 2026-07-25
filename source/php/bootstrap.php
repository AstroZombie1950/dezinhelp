<?php
// Общий бутстрап: конфиг, хелперы, данные для шапки и футера.
// Подключается из index.php, service.php, sitemap.php — везде, где рендерится страница.

// конфигурация: рабочий config.php, иначе шаблон
$configFile = __DIR__ . "/config.php";
$config = file_exists($configFile) ? require $configFile : require __DIR__ . "/config.sample.php";

// данные из /data — редактируются через админку
require __DIR__ . "/data.php";

// --- город из поддомена: решается до любого вывода ---
require __DIR__ . "/city.php";
$cityState = city_resolve();

// moskva.site.ru → основной домен, тем же путём
if ($cityState["status"] === "redirect") {
	header("Location: " . $cityState["url"], true, 301);
	exit;
}

// поддомена нет в реестре — честный 404, а не копия сайта
if ($cityState["status"] === "unknown") {
	require __DIR__ . "/../../404.php";
	exit;
}

$city = $cityState["city"];

// городские версии из поиска убираем: мета-тег плюс заголовок ответа.
// robots.txt не запрещаем — робот должен зайти и увидеть noindex
$robots = !empty($city["index"]) ? "index, follow" : "noindex, follow";
if (empty($city["index"])) {
	header("X-Robots-Tag: noindex, follow");
}

// плейсхолдеры {CITY...} меняются во всём HTML страницы разом
city_placeholders($city);
ob_start("city_render");

// публичные контакты правятся в админке: data/site.json перекрывает одноимённые
// поля конфига. Нет файла или поля — остаётся старое значение из config.php.
// Секреты (токен Telegram, chat_id) живут только в конфиге и сюда не попадают.
$site = data_load("site");
foreach (array("site_url", "email", "address") as $k) {
	if (isset($site[$k]) && $site[$k] !== "") { $config[$k] = $site[$k]; }
}
if (!empty($site["phones"]))     { $config["phones"] = $site["phones"]; }
if (!empty($site["messengers"])) { $config["messengers"] = array_merge($config["messengers"], $site["messengers"]); }

$phones = $config["phones"];
$email  = $config["email"];
$msg    = $config["messengers"];

// строки подвала: реквизиты и номер лицензии. Только из site.json — в конфиге
// лежит лицензия в другом формате, подставлять её в подвал нельзя
$legal   = isset($site["legal"]) ? $site["legal"] : "";
$license = isset($site["license"]) ? $site["license"] : "";

// базовый адрес сайта без хвостового слеша — для sitemap и canonical.
// Считается от текущего хоста, поэтому canonical и og:url сами держатся
// на том поддомене, который открыл посетитель
$siteUrl = city_scheme() . "://" . city_host();

// экранирование вывода в HTML
function h($s) { return htmlspecialchars($s, ENT_QUOTES, "UTF-8"); }

// Адрес статики: минифицированная версия, если она есть и не устарела, плюс
// метка времени файла. Хостинг отдаёт css/js с недельным кэшем — без метки
// посетитель неделю сидел бы на старой версии.
function asset($path) {
	$root = dirname(__DIR__, 2);
	$min  = preg_replace('/\.(css|js)$/', '.min.$1', $path);
	if ($min !== $path && is_file($root . $min) && filemtime($root . $min) >= filemtime($root . $path)) {
		$path = $min;
	}
	$file = $root . $path;
	return is_file($file) ? $path . "?v=" . filemtime($file) : $path;
}

$servicesData   = data_load("services");
$footerServices = services_footer($servicesData["services"]);