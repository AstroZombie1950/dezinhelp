<?php
// Карта сайта. Собирается из индекса услуг — руками не поддерживается.
// lastmod берём с файла контента: админка правит JSON, дата обновляется сама.
require __DIR__ . "/source/php/bootstrap.php";

// карта нужна только основному домену: городские версии закрыты от индексации
if (empty($city["main"])) {
	require __DIR__ . "/404.php";
	exit;
}

header("Content-Type: application/xml; charset=utf-8");

// база — текущий хост: sitemap требует абсолютных адресов
$base = $siteUrl;

// главная + все видимые услуги; priority и changefreq не выводим — их всё равно игнорируют
$urls = array(
	array("loc" => $base . "/", "lastmod" => filemtime(__DIR__ . "/index.php")),
	array("loc" => $base . "/kontakty/", "lastmod" => filemtime(__DIR__ . "/kontakty.php")),
);
foreach ($servicesData["services"] as $s) {
	if (empty($s["visible"])) {
		continue;
	}
	$urls[] = array(
		"loc"     => $base . "/" . $s["slug"] . "/",
		"lastmod" => service_mtime($s["slug"]),
	);
}

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<?php foreach ($urls as $u): ?>
	<url>
		<loc><?= h($u["loc"]) ?></loc>
		<lastmod><?= date("Y-m-d", $u["lastmod"]) ?></lastmod>
	</url>
<?php endforeach; ?>
</urlset>
