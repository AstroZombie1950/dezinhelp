<?php
// robots.txt собирается PHP: строка Sitemap обязана указывать на текущий хост.
// Статический файл жёстко называл боевой домен и врал на тесте и поддоменах.
require __DIR__ . "/source/php/bootstrap.php";

header("Content-Type: text/plain; charset=utf-8");

// карта сайта есть только у основного домена — городские версии её не отдают
$robotsSitemap = !empty($city["main"]) ? $siteUrl . "/sitemap.xml" : "";
?>
User-agent: *
Disallow: /source/php/
Disallow: /data/
Disallow: /admin/
Disallow: /404.php
<?php if ($robotsSitemap !== ""): ?>

Sitemap: <?= $robotsSitemap ?>

<?php endif; ?>
