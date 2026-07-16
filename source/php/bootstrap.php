<?php
// Общий бутстрап: конфиг, хелперы, данные для шапки и футера.
// Подключается из index.php, service.php, sitemap.php — везде, где рендерится страница.

// конфигурация: рабочий config.php, иначе шаблон
$configFile = __DIR__ . "/config.php";
$config = file_exists($configFile) ? require $configFile : require __DIR__ . "/config.sample.php";

$phones = $config["phones"];
$email  = $config["email"];
$msg    = $config["messengers"];

// базовый адрес сайта без хвостового слеша — для sitemap и canonical
$siteUrl = isset($config["site_url"]) ? rtrim($config["site_url"], "/") : "";

// экранирование вывода в HTML
function h($s) { return htmlspecialchars($s, ENT_QUOTES, "UTF-8"); }

// данные из /data — редактируются через админку
require __DIR__ . "/data.php";
$servicesData   = data_load("services");
$footerServices = services_footer($servicesData["services"]);
