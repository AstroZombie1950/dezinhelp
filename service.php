<?php
// Страница услуги. Сюда .htaccess отправляет всё, что похоже на /<slug>/.
// Пока это только обвязка роутера + каркас: порядок блоков ждём от клиента.
require __DIR__ . "/source/php/bootstrap.php";

// слаг из роутера, канонический вид — нижний регистр
$slug      = isset($_GET["slug"]) ? (string) $_GET["slug"] : "";
$canonical = strtolower($slug);

// нет в индексе или visible:false — честный 404, а не пустой шаблон с кодом 200
$service = service_find($servicesData["services"], $canonical);
if ($service === null) {
	require __DIR__ . "/404.php";
	exit;
}

// есть в индексе, но файла контента нет — тоже 404: показывать нечего
$page = service_page($canonical);
if (!$page) {
	require __DIR__ . "/404.php";
	exit;
}

// приводим адрес к каноническому: нижний регистр + хвостовой слеш.
// одно место на оба случая — /Obrabotka-Ot-Klopov и /obrabotka-ot-klopov
$path = "/" . $canonical . "/";
if (parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH) !== $path) {
	header("Location: " . $path, true, 301);
	exit;
}

$seo = isset($page["seo"]) ? $page["seo"] : array();
$url = $siteUrl . $path;
?>
<!DOCTYPE html>
<html lang="ru">
<head>
	<!-- кодировка и адаптив -->
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">

	<!-- основное SEO: всё из data/services/<slug>.json -->
	<title><?= h(isset($seo["title"]) ? $seo["title"] : $service["name"]) ?></title>
	<meta name="description" content="<?= h(isset($seo["description"]) ? $seo["description"] : "") ?>">
	<meta name="keywords" content="<?= h(isset($seo["keywords"]) ? $seo["keywords"] : "") ?>">
	<meta name="author" content="МосКомДез">
	<meta name="robots" content="index, follow">
	<link rel="canonical" href="<?= h($url) ?>">

	<!-- Open Graph -->
	<meta property="og:type" content="article">
	<meta property="og:title" content="<?= h(isset($seo["title"]) ? $seo["title"] : $service["name"]) ?>">
	<meta property="og:description" content="<?= h(isset($seo["description"]) ? $seo["description"] : "") ?>">
	<meta property="og:url" content="<?= h($url) ?>">
	<meta property="og:site_name" content="МосКомДез">
	<meta property="og:locale" content="ru_RU">
	<meta property="og:image" content="/source/img/og-cover.jpg">

	<!-- favicon -->
	<link rel="icon" type="image/svg+xml" href="/favicon.svg">
	<link rel="icon" type="image/x-icon" href="/favicon.ico">

	<meta name="theme-color" content="#ffffff">

	<!-- шрифт Roboto -->
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;600;700;800&display=swap" rel="stylesheet">

	<!-- стили -->
	<link rel="stylesheet" href="/source/css/main.css">
</head>
<body>

	<?php require __DIR__ . "/source/include/header.html"; ?>

	<main>
		<!-- КАРКАС. Порядок блоков ждём от клиента, пока только текстовое ядро -->
		<section class="section">
			<div class="container">
				<h1><?= h($page["h1"]) ?></h1>
				<?= $page["intro_html"] ?>
				<?= $page["seo_html"] ?>
			</div>
		</section>
	</main>

	<?php require __DIR__ . "/source/include/footer.html"; ?>

	<?php require __DIR__ . "/source/include/modal.php"; ?>

	<script src="/source/js/main.js"></script>
</body>
</html>
