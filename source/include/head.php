<?php
// Общее содержимое <head> главной и страниц услуг: мета, OG, favicon, шрифты, стили.
// Ждёт: $headTitle, $headDescr, $headKeywords, $headCanonical, $headOgType
// («website» — главная, «article» — услуга); необязательно $headOgDescr — своё
// описание для соцсетей, по умолчанию берётся $headDescr.
// Плюс $robots и $siteUrl из bootstrap.php. Плейсхолдеры {CITY...} подставит буфер.
$headOgDescr = !empty($headOgDescr) ? $headOgDescr : $headDescr;
?>
	<!-- кодировка и адаптив -->
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">

	<!-- основное SEO -->
	<title><?= h($headTitle) ?></title>
	<meta name="description" content="<?= h($headDescr) ?>">
	<meta name="keywords" content="<?= h($headKeywords) ?>">
	<meta name="author" content="МосКомДез">
	<meta name="robots" content="<?= h($robots) ?>">
	<link rel="canonical" href="<?= h($headCanonical) ?>">

	<!-- Open Graph -->
	<meta property="og:type" content="<?= h($headOgType) ?>">
	<meta property="og:title" content="<?= h($headTitle) ?>">
	<meta property="og:description" content="<?= h($headOgDescr) ?>">
	<meta property="og:url" content="<?= h($headCanonical) ?>">
	<meta property="og:site_name" content="МосКомДез">
	<meta property="og:locale" content="ru_RU">
	<meta property="og:image" content="<?= h($siteUrl) ?>/source/img/og-cover.jpg">

	<!-- Twitter card -->
	<meta name="twitter:card" content="summary_large_image">
	<meta name="twitter:title" content="<?= h($headTitle) ?>">
	<meta name="twitter:description" content="<?= h($headOgDescr) ?>">
	<meta name="twitter:image" content="<?= h($siteUrl) ?>/source/img/og-cover.jpg">

	<!-- favicon -->
	<link rel="icon" type="image/svg+xml" href="/favicon.svg">
	<link rel="icon" type="image/x-icon" href="/favicon.ico">

	<!-- цвет темы для мобильных браузеров -->
	<meta name="theme-color" content="#ffffff">

	<!-- шрифт Roboto — свои файлы, @font-face в main.css.
	     Кириллица и латиница нужны для первого экрана -->
	<link rel="preload" href="/source/fonts/roboto-cyrillic.woff2" as="font" type="font/woff2" crossorigin>
	<link rel="preload" href="/source/fonts/roboto-latin.woff2" as="font" type="font/woff2" crossorigin>

	<!-- стили -->
	<link rel="stylesheet" href="<?= h(asset("/source/css/main.css")) ?>">

	<!-- счётчики аналитики — один файл на все страницы сайта -->
	<?php require __DIR__ . "/matrika.html"; ?>
