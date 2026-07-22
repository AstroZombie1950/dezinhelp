<?php
// Общий бутстрап: конфиг, хелперы, данные для шапки и футера.
// Подключается из index.php, service.php, sitemap.php — везде, где рендерится страница.

// конфигурация: рабочий config.php, иначе шаблон
$configFile = __DIR__ . "/config.php";
$config = file_exists($configFile) ? require $configFile : require __DIR__ . "/config.sample.php";

// данные из /data — редактируются через админку
require __DIR__ . "/data.php";

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

// базовый адрес сайта без хвостового слеша — для sitemap и canonical
$siteUrl = isset($config["site_url"]) ? rtrim($config["site_url"], "/") : "";

// экранирование вывода в HTML
function h($s) { return htmlspecialchars($s, ENT_QUOTES, "UTF-8"); }

$servicesData   = data_load("services");
$footerServices = services_footer($servicesData["services"]);