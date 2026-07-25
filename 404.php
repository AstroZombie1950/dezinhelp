<?php
// Страница 404. Зовётся из service.php (слаг не найден) и из ErrorDocument в .htaccess.
// Намеренно самостоятельная: ни шапки, ни футера, ни данных — только выход на главную.
http_response_code(404);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">

	<title>Страница не найдена — МосКомДез</title>
	<meta name="robots" content="noindex, follow">

	<link rel="icon" type="image/svg+xml" href="/favicon.svg">
	<link rel="icon" type="image/x-icon" href="/favicon.ico">
	<meta name="theme-color" content="#ffffff">

	<link rel="preload" href="/source/fonts/roboto-cyrillic.woff2" as="font" type="font/woff2" crossorigin>

	<link rel="stylesheet" href="/source/css/main.css">
</head>
<body>

	<main class="e404">
		<div class="container">
			<div class="e404__code">404</div>
			<h1 class="e404__title">Страница не найдена</h1>
			<p class="e404__text">Возможно, в адресе опечатка или страницу удалили.</p>
			<a class="btn btn--accent" href="/">На главную</a>
		</div>
	</main>

	<?php // счётчики аналитики — один файл на все страницы сайта ?>
	<?php require __DIR__ . "/source/include/matrika.html"; ?>
</body>
</html>
