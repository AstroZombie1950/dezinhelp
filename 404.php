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

	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700;800&display=swap" rel="stylesheet">

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

</body>
</html>
