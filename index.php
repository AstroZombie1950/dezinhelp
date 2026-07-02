<?php
// загрузка конфигурации: рабочий config.php, иначе шаблон
$configFile = __DIR__ . "/source/php/config.php";
$config = file_exists($configFile) ? require $configFile : require __DIR__ . "/source/php/config.sample.php";
$phones = $config["phones"];
$email  = $config["email"];
$msg    = $config["messengers"];
function h($s) { return htmlspecialchars($s, ENT_QUOTES, "UTF-8"); }
?>
<!DOCTYPE html>
<html lang="ru">
<head>
	<!-- кодировка и адаптив -->
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">

	<!-- основное SEO -->
	<title>МосКомДез — служба дезинфекции, дезинсекции и дератизации</title>
	<meta name="description" content="Уничтожение тараканов, клопов, грызунов, клещей и других вредителей. Профессиональная дезинфекция, дезинсекция и дератизация в Москве и области. Гарантия, лицензия, выезд в день заявки.">
	<meta name="keywords" content="дезинфекция, дезинсекция, дератизация, уничтожение тараканов, уничтожение клопов, обработка от клещей, СЭС, МосКомДез">
	<meta name="author" content="МосКомДез">
	<meta name="robots" content="index, follow">
	<link rel="canonical" href="https://dezinhelp.ru/">

	<!-- Open Graph -->
	<meta property="og:type" content="website">
	<meta property="og:title" content="МосКомДез — служба дезинфекции, дезинсекции и дератизации">
	<meta property="og:description" content="Уничтожение тараканов, клопов, грызунов, клещей и других вредителей. Гарантия, лицензия, выезд в день заявки.">
	<meta property="og:url" content="https://dezinhelp.ru/">
	<meta property="og:site_name" content="МосКомДез">
	<meta property="og:locale" content="ru_RU">
	<meta property="og:image" content="/source/img/og-cover.jpg">

	<!-- Twitter card -->
	<meta name="twitter:card" content="summary_large_image">
	<meta name="twitter:title" content="МосКомДез — служба дезинфекции, дезинсекции и дератизации">
	<meta name="twitter:description" content="Уничтожение тараканов, клопов, грызунов, клещей и других вредителей. Гарантия, лицензия, выезд в день заявки.">
	<meta name="twitter:image" content="/source/img/og-cover.jpg">

	<!-- favicon -->
	<link rel="icon" type="image/svg+xml" href="/favicon.svg">
	<link rel="icon" type="image/x-icon" href="/favicon.ico">

	<!-- цвет темы для мобильных браузеров -->
	<meta name="theme-color" content="#ffffff">

	<!-- шрифт Roboto (как на referense-сайте safelychange.ru) -->
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;600;700&display=swap" rel="stylesheet">

	<!-- стили -->
	<link rel="stylesheet" href="/source/css/main.css">
</head>
<body>

	<!-- SVG-спрайт: иконки соцсетей, оплаты и типов объектов -->
	<svg class="svg-sprite" aria-hidden="true" focusable="false">
		<!-- мессенджеры / соцсети (монохром) -->
		<symbol id="i-tg" viewBox="0 0 24 24"><path fill="currentColor" d="M9.8 15.6l-.3 3.9c.5 0 .7-.2.9-.5l1.8-1.7 3.8 2.8c.7.4 1.2.2 1.4-.6l2.5-11.9c.2-1-.3-1.4-1-1.1L2.9 10.2c-1 .4-1 .9-.2 1.2l4.3 1.3L17 6.9c.5-.3.9-.1.6.2z"/></symbol>
		<symbol id="i-max" viewBox="0 0 24 24"><path fill="currentColor" d="M4 3h16a2 2 0 012 2v10a2 2 0 01-2 2H9l-4 4v-4H4a2 2 0 01-2-2V5a2 2 0 012-2zm3 4v7h2v-3.6l3 3.4 3-3.4V14h2V7h-2l-3 3.4L9 7z"/></symbol>
		<symbol id="i-wa" viewBox="0 0 24 24"><path fill="currentColor" d="M12 2a10 10 0 00-8.6 15l-1.4 5 5.1-1.3A10 10 0 1012 2zm0 2a8 8 0 11-4.2 14.8l-.3-.2-2.6.7.7-2.5-.2-.3A8 8 0 0112 4zm-3.5 3.6c-.2 0-.5.1-.7.4-.3.3-.9.9-.9 2.1s.9 2.4 1.1 2.6c.1.2 1.7 2.8 4.3 3.8 2.1.9 2.5.7 3 .6.5 0 1.5-.6 1.7-1.2.2-.6.2-1.1.2-1.2l-.6-.3c-.3-.2-1.5-.7-1.7-.8-.2-.1-.4-.1-.6.2l-.7.9c-.1.2-.3.2-.5.1-.7-.3-1.4-.6-2.1-1.5-.5-.6-.8-1.2-.9-1.4-.1-.2 0-.4.1-.5l.4-.4.2-.4c.1-.1 0-.3 0-.4l-.8-1.8c-.2-.5-.4-.4-.6-.4z"/></symbol>
		<!-- оплата (цветные) -->
		<symbol id="i-sbp" viewBox="0 0 62 16"><path fill="#5B57A2" d="M2 4l3 4-3 4z"/><path fill="#D90751" d="M6 2l3 4-3 4z"/><path fill="#F48120" d="M6 6l3 4-3 4z"/><path fill="#00A94F" d="M10 4l3 4-3 4z"/><text x="17" y="12" font-family="Arial, sans-serif" font-weight="700" font-size="11" fill="#1D1D1B">СБП</text></symbol>
		<symbol id="i-mir" viewBox="0 0 52 16"><rect x="2" y="4" width="6" height="8" rx="1" fill="#0F9D58"/><text x="12" y="13" font-family="Arial, sans-serif" font-weight="800" font-size="13" fill="#0F9D58" letter-spacing="1">МИР</text></symbol>
		<symbol id="i-visa" viewBox="0 0 52 16"><text x="4" y="13" font-family="Arial, sans-serif" font-weight="700" font-style="italic" font-size="14" fill="#1A1F71" letter-spacing="1">VISA</text></symbol>
		<symbol id="i-mc" viewBox="0 0 40 24"><circle cx="16" cy="12" r="8" fill="#EB001B"/><circle cx="24" cy="12" r="8" fill="#F79E1B"/><path fill="#FF5F00" d="M20 6a8 8 0 000 12 8 8 0 000-12z"/></symbol>
		<!-- типы объектов (монохром, обводка) -->
		<symbol id="i-food" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M7 3v6M10 3v6M8.5 3v6M8.5 9v12"/><path d="M15 3c2 0 3 3 3 6s-1 4-2 4v8"/></g></symbol>
		<symbol id="i-hotel" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 17h18M4 17v-5a2 2 0 012-2h8a3 3 0 013 3v4M4 12V8M8 12v-1a1 1 0 011-1h2a1 1 0 011 1v1M4 17v3M20 17v3"/></g></symbol>
		<symbol id="i-retail" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M6 8h12l-1 12H7L6 8zM9 8V6a3 3 0 016 0v2"/></g></symbol>
		<symbol id="i-med" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3l7 3v5c0 4-3 7-7 9-4-2-7-5-7-9V6l7-3zM12 8.5v6M9 11.5h6"/></g></symbol>
		<symbol id="i-transport" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h11v9H3zM14 9h4l3 3v3h-3M11 15H8"/><circle cx="6.5" cy="16.5" r="1.6"/><circle cx="17.5" cy="16.5" r="1.6"/></g></symbol>
		<symbol id="i-home" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 11l8-6 8 6M6 10v9h12v-9M10 19v-5h4v5"/></g></symbol>
		<symbol id="i-industry" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21V10l5 3V10l5 3V7l6 4v10zM3 21h18M7 21v-3M12 21v-3M17 21v-3"/></g></symbol>
		<symbol id="i-office" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M5 21V4h9v17M14 21V9h5v12M3 21h18M8 8h3M8 12h3M8 16h3M17 13h1M17 17h1"/></g></symbol>
	</svg>

	<!-- шапка -->
	<header class="header" id="top">
		<div class="container header__row">
			<a href="/" class="header__logo">
				МосКомДез
				<span class="header__logo-tagline">служба дезинфекции</span>
			</a>

			<nav class="nav">
				<ul class="nav__list">
					<li class="nav__item nav__item--dropdown">
						<button type="button" class="nav__toggle">
							Обработки
							<span class="nav__toggle-arrow"></span>
						</button>
						<div class="nav__submenu">
							<a class="nav__submenu-link" href="/tarakany/">Тараканы</a>
							<a class="nav__submenu-link" href="/klopy/">Клопы</a>
							<a class="nav__submenu-link" href="/komary/">Комары</a>
							<a class="nav__submenu-link" href="/kleshchi/">Клещи</a>
							<a class="nav__submenu-link" href="/zapah/">Запах</a>
							<a class="nav__submenu-link" href="/plesen/">Плесень</a>
							<a class="nav__submenu-link" href="/gryzuny/">Кроты</a>
							<a class="nav__submenu-link" href="/gryzuny/">Крысы, мыши</a>
							<a class="nav__submenu-link" href="/sushka/">Сушка дома</a>
							<a class="nav__submenu-link" href="/muravyi/">Муравьи</a>
							<a class="nav__submenu-link" href="/osy-shershni/">Осы, шершни</a>
							<a class="nav__submenu-link" href="/koroed/">Короед</a>
							<a class="nav__submenu-link" href="/uborka-posle-smerti/">Уборка после смерти</a>
							<a class="nav__submenu-link" href="/nasekomye/">От всех насекомых</a>
							<a class="nav__submenu-link" href="/borshchevik/">Борщевик</a>
							<a class="nav__submenu-link" href="/rasteniya/">Лечение растений</a>
							<a class="nav__submenu-link" href="/posle-pozhara/">После пожара</a>
							<a class="nav__submenu-link" href="/kto-kusaet/">Кто-то кусает</a>
						</div>
					</li>
					<li class="nav__item"><a class="nav__link" href="#reviews">Отзывы</a></li>
					<li class="nav__item"><a class="nav__link" href="#contacts">Контакты</a></li>
					<li class="nav__item nav__item--cta"><a class="nav__link" href="#order">Заказать онлайн</a></li>
				</ul>
			</nav>

			<div class="header__side">
				<div class="header__phones">
					<?php foreach ($phones as $p): ?>
					<a href="tel:<?= h($p["tel"]) ?>"><?= h($p["display"]) ?></a>
					<?php endforeach; ?>
				</div>
				<a href="#order" class="btn btn--primary btn--sm">Заказать онлайн</a>
				<button type="button" class="header__burger" aria-label="Меню"><span></span></button>
			</div>
		</div>
	</header>

	<!-- герой: преимущества, форма заявки, сетка услуг -->
	<section class="hero" id="hero">
		<div class="container hero__grid">
			<div class="hero__col">
				<ul class="hero__badges">
					<li>Срочный заказ</li>
					<li>Безопасные препараты без запаха</li>
					<li>Государственная аттестация</li>
					<li>Гарантия по договору 3 года</li>
					<li>Решения под ключ</li>
				</ul>

				<form class="hero__form js-form" action="/source/php/order.php" method="post">
					<input type="hidden" name="source" value="Герой — консультация">
					<input type="text" name="website" class="hp" tabindex="-1" autocomplete="off" aria-hidden="true">
					<div class="hero__form-title">Оформить заявку</div>
					<div class="hero__form-sub">Заполните поля для связи с вами</div>
					<input type="text" name="name" placeholder="Ваше имя" required>
					<input type="tel" name="phone" placeholder="+7 (___) ___-__-__" required>
					<button type="submit" class="btn btn--accent btn--block">Получить консультацию</button>
					<div class="form-status" role="status"></div>
					<div class="hero__form-note">Отправляя свои данные, вы соглашаетесь с <a href="/politika/">политикой конфиденциальности</a></div>
				</form>

				<div class="hero__support">Круглосуточная поддержка</div>
			</div>

			<div class="hero__services">
				<a class="service-tile" href="/gryzuny/"><span class="service-tile__icon">🐀</span>Грызуны</a>
				<a class="service-tile" href="/klopy/"><span class="service-tile__icon">🛏️</span>Клопы</a>
				<a class="service-tile" href="/tarakany/"><span class="service-tile__icon">🪳</span>Тараканы</a>
				<a class="service-tile" href="/plesen/"><span class="service-tile__icon">🍄</span>Плесень</a>
				<a class="service-tile" href="/zapah/"><span class="service-tile__icon">💨</span>Запахи</a>
				<a class="service-tile" href="/nasekomye/"><span class="service-tile__icon">🪰</span>Мухи</a>
				<a class="service-tile" href="/muravyi/"><span class="service-tile__icon">🐜</span>Муравьи</a>
				<a class="service-tile" href="/nasekomye/"><span class="service-tile__icon">🦗</span>Блохи</a>
				<a class="service-tile" href="/kleshchi/"><span class="service-tile__icon">🕷️</span>Клещи</a>
				<a class="service-tile" href="/osy-shershni/"><span class="service-tile__icon">🐝</span>Осы, шершни</a>
				<a class="service-tile" href="/koroed/"><span class="service-tile__icon">🪵</span>Короед</a>
				<a class="service-tile" href="/komary/"><span class="service-tile__icon">🦟</span>Комары</a>
			</div>
		</div>
	</section>

	<!-- бегущая строка -->
	<div class="marquee">
		<div class="marquee__track">
			<span>ТОЛЬКО СЕРТИФИЦИРОВАННЫЕ ПРЕПАРАТЫ</span>
			<span>ТОЛЬКО СЕРТИФИЦИРОВАННЫЕ ПРЕПАРАТЫ</span>
			<span>ТОЛЬКО СЕРТИФИЦИРОВАННЫЕ ПРЕПАРАТЫ</span>
			<span>ТОЛЬКО СЕРТИФИЦИРОВАННЫЕ ПРЕПАРАТЫ</span>
			<span>ТОЛЬКО СЕРТИФИЦИРОВАННЫЕ ПРЕПАРАТЫ</span>
			<span>ТОЛЬКО СЕРТИФИЦИРОВАННЫЕ ПРЕПАРАТЫ</span>
		</div>
	</div>

	<!-- цены: двухколоночный блок по образцу klining24.ru -->
	<section class="section" id="prices">
		<div class="container">
			<h2 class="section__title">Цены на наши услуги</h2>
			<div class="prices__columns">
				<div>
					<div class="price-card">
						<div class="price-card__header">
							<div class="price-card__title">Обработка помещений от насекомых</div>
							<div class="price-card__from">от 2 300 ₽</div>
						</div>
						<div class="price-card__rows">
							<div class="price-card__row"><span>10–30 м²</span><span>2 300 ₽</span></div>
							<div class="price-card__row"><span>30–50 м²</span><span>2 900 ₽</span></div>
							<div class="price-card__row"><span>&gt;50 м²</span><span>от 3 500 ₽</span></div>
							<div class="price-card__row"><span>&gt;100 м²</span><span>от 6 500 ₽</span></div>
							<div class="price-card__row"><span>&gt;300 м²</span><span>от 14 700 ₽</span></div>
						</div>
						<a href="#order" class="btn btn--outline btn--sm btn--block">Подробнее</a>
					</div>

					<div class="price-card">
						<div class="price-card__header">
							<div class="price-card__title">Обработка участков от насекомых</div>
							<div class="price-card__from">от 200 ₽/сот.</div>
						</div>
						<div class="price-card__rows">
							<div class="price-card__row"><span>до 10 соток</span><span>7 000 ₽</span></div>
							<div class="price-card__row"><span>10–15 соток</span><span>от 9 000 ₽</span></div>
							<div class="price-card__row"><span>15–30 соток</span><span>от 10 500 ₽</span></div>
							<div class="price-card__row"><span>30–40 соток</span><span>от 15 000 ₽</span></div>
							<div class="price-card__row"><span>40–60 соток</span><span>от 18 000 ₽</span></div>
							<div class="price-card__row"><span>&gt;70 соток, 1 га и более</span><span>от 20 000 ₽</span></div>
						</div>
						<a href="#order" class="btn btn--outline btn--sm btn--block">Подробнее</a>
					</div>

					<div class="price-card">
						<div class="price-card__header">
							<div class="price-card__title">Дезинфекция от плесени</div>
							<div class="price-card__from">от 1 500 ₽/м²</div>
						</div>
						<div class="price-card__rows">
							<div class="price-card__row"><span>Механическая чистка</span><span>500 ₽/м²</span></div>
							<div class="price-card__row"><span>Химическая чистка</span><span>от 1 500 ₽/м²</span></div>
							<div class="price-card__row"><span>Защитная плёнка</span><span>1 000 ₽/м²</span></div>
							<div class="price-card__row"><span>Демонтаж, технология</span><span>по запросу</span></div>
						</div>
						<a href="#order" class="btn btn--outline btn--sm btn--block">Подробнее</a>
					</div>

					<div class="price-card">
						<div class="price-card__header">
							<div class="price-card__title">Уничтожение крыс, кротов</div>
							<div class="price-card__from">от 6 000 ₽</div>
						</div>
						<div class="price-card__rows">
							<div class="price-card__row"><span>до 50 м²</span><span>6 000 ₽</span></div>
							<div class="price-card__row"><span>до 100 м²</span><span>12 000 ₽</span></div>
							<div class="price-card__row"><span>&gt;100 м²</span><span>по запросу</span></div>
							<div class="price-card__row"><span>до 15 соток</span><span>от 9 000 ₽</span></div>
							<div class="price-card__row"><span>&gt;15 соток</span><span>по запросу</span></div>
						</div>
						<a href="#order" class="btn btn--outline btn--sm btn--block">Подробнее</a>
					</div>
				</div>

				<div>
					<div class="price-card">
						<div class="price-card__header">
							<div class="price-card__title">Сушка помещений</div>
							<div class="price-card__from">от 5 500 ₽/сут.</div>
						</div>
						<div class="price-card__rows">
							<div class="price-card__row"><span>Сбор, слив воды</span><span>от 7 000 ₽/м³</span></div>
							<div class="price-card__row"><span>Демонтаж</span><span>по запросу</span></div>
							<div class="price-card__row"><span>Сушка</span><span>под ключ</span></div>
							<div class="price-card__row"><span>Дезинфекция</span><span>от 1 500 ₽/м²</span></div>
						</div>
						<a href="#order" class="btn btn--outline btn--sm btn--block">Подробнее</a>
					</div>

					<div class="price-card">
						<div class="price-card__header">
							<div class="price-card__title">Устранение запаха</div>
							<div class="price-card__from">от 2 300 ₽</div>
						</div>
						<div class="price-card__rows">
							<div class="price-card__row"><span>10–30 м²</span><span>3 500 ₽</span></div>
							<div class="price-card__row"><span>40–60 м²</span><span>от 6 000 ₽</span></div>
							<div class="price-card__row"><span>70–90 м²</span><span>от 9 000 ₽</span></div>
							<div class="price-card__row"><span>100–150 м²</span><span>от 15 000 ₽</span></div>
							<div class="price-card__row"><span>другое</span><span>по запросу</span></div>
						</div>
						<a href="#order" class="btn btn--outline btn--sm btn--block">Подробнее</a>
					</div>

					<div class="price-card">
						<div class="price-card__header">
							<div class="price-card__title">Дезинфекция после смерти</div>
							<div class="price-card__from">от 10 000 ₽</div>
						</div>
						<div class="price-card__rows">
							<div class="price-card__row"><span>Уборка мусора</span><span>по запросу</span></div>
							<div class="price-card__row"><span>Демонтаж</span><span>по запросу</span></div>
							<div class="price-card__row"><span>Дезинфекция пятна</span><span>от 1 500 ₽/м²</span></div>
							<div class="price-card__row"><span>Дезодорация</span><span>от 500 ₽/м²</span></div>
						</div>
						<a href="#order" class="btn btn--outline btn--sm btn--block">Подробнее</a>
					</div>

					<div class="price-card">
						<div class="price-card__header">
							<div class="price-card__title">Уничтожение борщевика</div>
							<div class="price-card__from">от 700 ₽/сот.</div>
						</div>
						<div class="price-card__rows">
							<div class="price-card__row"><span>до 10 соток</span><span>10 000 ₽</span></div>
							<div class="price-card__row"><span>10–15 соток</span><span>от 10 000 ₽</span></div>
							<div class="price-card__row"><span>15–20 соток</span><span>от 14 000 ₽</span></div>
							<div class="price-card__row"><span>20–30 соток</span><span>от 17 000 ₽</span></div>
							<div class="price-card__row"><span>40–60 соток</span><span>от 25 000 ₽</span></div>
							<div class="price-card__row"><span>&gt;70 соток, 1 га и более</span><span>от 30 000 ₽</span></div>
						</div>
						<a href="#order" class="btn btn--outline btn--sm btn--block">Подробнее</a>
					</div>
				</div>
			</div>
		</div>
	</section>

	<!-- клининг: карточки с фото (образец klining24) + прайс -->
	<section class="section section--alt" id="cleaning">
		<div class="container">
			<h2 class="section__title">Клининговые услуги</h2>
			<div class="cleaning__cards">
				<div class="cleaning-card">
					<div class="cleaning-card__img" style="background-image:url(/source/img/cleaning/uborka-kvartir.webp)"></div>
					<div class="cleaning-card__body">
						<div class="cleaning-card__title">Уборка квартир</div>
						<div class="cleaning-card__price">от 80 ₽/м²</div>
						<div class="cleaning-card__meta"><span>Время: 2–7 ч.</span><span>Специалистов: 1–4</span></div>
						<a href="#order" class="btn btn--outline btn--sm btn--block cleaning-card__btn">Подробнее</a>
					</div>
				</div>
				<div class="cleaning-card">
					<div class="cleaning-card__img" style="background-image:url(/source/img/cleaning/generalnaya.webp)"><span class="cleaning-card__badge">Популярное</span></div>
					<div class="cleaning-card__body">
						<div class="cleaning-card__title">Генеральная уборка</div>
						<div class="cleaning-card__price">от 140 ₽/м²</div>
						<div class="cleaning-card__meta"><span>Время: 3–8 ч.</span><span>Специалистов: 1–4</span></div>
						<a href="#order" class="btn btn--outline btn--sm btn--block cleaning-card__btn">Подробнее</a>
					</div>
				</div>
				<div class="cleaning-card">
					<div class="cleaning-card__img" style="background-image:url(/source/img/cleaning/posle-remonta.webp)"></div>
					<div class="cleaning-card__body">
						<div class="cleaning-card__title">Уборка после ремонта</div>
						<div class="cleaning-card__price">от 180 ₽/м²</div>
						<div class="cleaning-card__meta"><span>Время: 6–10 ч.</span><span>Специалистов: 1–5</span></div>
						<a href="#order" class="btn btn--outline btn--sm btn--block cleaning-card__btn">Подробнее</a>
					</div>
				</div>
				<div class="cleaning-card">
					<div class="cleaning-card__img" style="background-image:url(/source/img/cleaning/podderzhivayushchaya.webp)"></div>
					<div class="cleaning-card__body">
						<div class="cleaning-card__title">Поддерживающая уборка</div>
						<div class="cleaning-card__price">от 80 ₽/м²</div>
						<div class="cleaning-card__meta"><span>Время: 2–4 ч.</span><span>Специалистов: 1–2</span></div>
						<a href="#order" class="btn btn--outline btn--sm btn--block cleaning-card__btn">Подробнее</a>
					</div>
				</div>
				<div class="cleaning-card">
					<div class="cleaning-card__img" style="background-image:url(/source/img/cleaning/uborka-domov.webp)"></div>
					<div class="cleaning-card__body">
						<div class="cleaning-card__title">Уборка домов</div>
						<div class="cleaning-card__price">от 80 ₽/м²</div>
						<div class="cleaning-card__meta"><span>Время: 5–7 ч.</span><span>Специалистов: 2–5</span></div>
						<a href="#order" class="btn btn--outline btn--sm btn--block cleaning-card__btn">Подробнее</a>
					</div>
				</div>
				<div class="cleaning-card">
					<div class="cleaning-card__img" style="background-image:url(/source/img/cleaning/kottedzhi.webp)"></div>
					<div class="cleaning-card__body">
						<div class="cleaning-card__title">Уборка коттеджей</div>
						<div class="cleaning-card__price">от 80 ₽/м²</div>
						<div class="cleaning-card__meta"><span>Время: 5–7 ч.</span><span>Специалистов: 2–5</span></div>
						<a href="#order" class="btn btn--outline btn--sm btn--block cleaning-card__btn">Подробнее</a>
					</div>
				</div>
				<div class="cleaning-card">
					<div class="cleaning-card__img" style="background-image:url(/source/img/cleaning/mytyo-okon.webp)"></div>
					<div class="cleaning-card__body">
						<div class="cleaning-card__title">Мытьё окон</div>
						<div class="cleaning-card__price">от 350 ₽/м²</div>
						<div class="cleaning-card__meta"><span>Время: 1–3 ч.</span><span>Специалистов: 1–2</span></div>
						<a href="#order" class="btn btn--outline btn--sm btn--block cleaning-card__btn">Подробнее</a>
					</div>
				</div>
				<div class="cleaning-card">
					<div class="cleaning-card__img" style="background-image:url(/source/img/cleaning/himchistka.webp)"></div>
					<div class="cleaning-card__body">
						<div class="cleaning-card__title">Химчистка</div>
						<div class="cleaning-card__price">от 5 000 ₽/ед.</div>
						<div class="cleaning-card__meta"><span>Время: 1–3 ч.</span><span>Специалистов: 1–2</span></div>
						<a href="#order" class="btn btn--outline btn--sm btn--block cleaning-card__btn">Подробнее</a>
					</div>
				</div>
			</div>

			<h3 class="cleaning__subtitle">Цены на клининг</h3>
			<div class="cleaning-prices">
				<div class="cleaning-price-group">
					<div class="cleaning-price-group__title">Уборка квартир</div>
					<div class="cleaning-price-row"><span>Уборка квартир</span><span>от 80 ₽/м²</span></div>
					<div class="cleaning-price-row"><span>После ремонта</span><span>от 180 ₽/м²</span></div>
					<div class="cleaning-price-row"><span>Поддерживающая</span><span>от 80 ₽/м²</span></div>
					<div class="cleaning-price-row"><span>Генеральная</span><span>от 140 ₽/м²</span></div>
					<div class="cleaning-price-row"><span>После потопа</span><span>от 350 ₽/м²</span></div>
					<div class="cleaning-price-row"><span>После пожара</span><span>от 400 ₽/м²</span></div>
					<div class="cleaning-price-row"><span>Запущенных квартир</span><span>от 150 ₽/м²</span></div>
					<div class="cleaning-price-row"><span>Влажная</span><span>от 80 ₽/м²</span></div>
					<div class="cleaning-price-row"><span>Комплексная</span><span>от 80 ₽/м²</span></div>
					<div class="cleaning-price-row"><span>Элитной квартиры</span><span>от 200 ₽/м²</span></div>
					<div class="cleaning-price-row"><span>Однокомнатной</span><span>от 80 ₽/м²</span></div>
					<div class="cleaning-price-row"><span>Двухкомнатной</span><span>от 80 ₽/м²</span></div>
					<div class="cleaning-price-row"><span>Трёхкомнатной</span><span>от 80 ₽/м²</span></div>
				</div>
				<!-- правая колонка: два листа поменьше (как у донора) -->
				<div class="cleaning-prices__col">
					<div class="cleaning-price-group">
						<div class="cleaning-price-group__title">Уборка домов</div>
						<div class="cleaning-price-row"><span>Уборка домов</span><span>от 80 ₽/м²</span></div>
						<div class="cleaning-price-row"><span>После ремонта</span><span>от 180 ₽/м²</span></div>
						<div class="cleaning-price-row"><span>Коттеджа</span><span>от 80 ₽/м²</span></div>
						<div class="cleaning-price-row"><span>Таунхауса</span><span>от 80 ₽/м²</span></div>
						<div class="cleaning-price-row"><span>Дачи</span><span>от 80 ₽/м²</span></div>
					</div>
					<div class="cleaning-price-group">
						<div class="cleaning-price-group__title">Уборка офисов</div>
						<div class="cleaning-price-row"><span>Уборка офисов</span><span>от 30 ₽/м²</span></div>
						<div class="cleaning-price-row"><span>Генеральная</span><span>от 90 ₽/м²</span></div>
						<div class="cleaning-price-row"><span>После ремонта</span><span>от 140 ₽/м²</span></div>
						<div class="cleaning-price-row"><span>Ежедневная</span><span>от 30 ₽/м²</span></div>
						<div class="cleaning-price-row"><span>Разовая</span><span>от 30 ₽/м²</span></div>
						<div class="cleaning-price-row"><span>Утренняя</span><span>от 30 ₽/м²</span></div>
						<div class="cleaning-price-row"><span>Вечерняя</span><span>от 30 ₽/м²</span></div>
					</div>
				</div>
			</div>
		</div>
	</section>

	<!-- порядок обработки -->
	<section class="section" id="process">
		<div class="container">
			<h2 class="section__title">Порядок обработки</h2>
			<div class="steps">
				<div class="step">
					<div class="step__num">1</div>
					<div class="step__title">ПОДГОТОВКА</div>
					<div class="step__text">Оператор проконсультирует об услуге: как подготовиться, что убирать, что освободить, порядке проведения работ, противопоказаниях и технике безопасности.</div>
				</div>
				<div class="step">
					<div class="step__num">2</div>
					<div class="step__title">ДОКУМЕНТЫ</div>
					<div class="step__text">Перед началом работ заключается договор с памяткой о безопасности, сроках и дальнейших действиях после обработок.</div>
				</div>
				<div class="step">
					<div class="step__num">3</div>
					<div class="step__title">ПРОВЕДЕНИЕ РАБОТ</div>
					<div class="step__text">Применение методов и технических средств с препаратами классов опасности (I, II, III, IV) на объекте, необходимых и допустимых для конкретной ситуации.</div>
				</div>
			</div>
		</div>
	</section>

	<!-- типы объектов -->
	<section class="section section--alt" id="objects">
		<div class="container">
			<h2 class="section__title">Работаем на всех типах объектов</h2>
			<div class="objects">
				<div class="objects__item"><svg class="objects__icon"><use href="#i-food"></use></svg><span>Предприятия питания</span></div>
				<div class="objects__item"><svg class="objects__icon"><use href="#i-hotel"></use></svg><span>Гостиничный бизнес</span></div>
				<div class="objects__item"><svg class="objects__icon"><use href="#i-retail"></use></svg><span>Розничная торговля</span></div>
				<div class="objects__item"><svg class="objects__icon"><use href="#i-med"></use></svg><span>Мед. учреждения</span></div>
				<div class="objects__item"><svg class="objects__icon"><use href="#i-transport"></use></svg><span>Транспорт</span></div>
				<div class="objects__item"><svg class="objects__icon"><use href="#i-home"></use></svg><span>Квартиры и дома</span></div>
				<div class="objects__item"><svg class="objects__icon"><use href="#i-industry"></use></svg><span>Производство, склад</span></div>
				<div class="objects__item"><svg class="objects__icon"><use href="#i-office"></use></svg><span>Офисные помещения</span></div>
			</div>
		</div>
	</section>

	<!-- методы обработки -->
	<section class="section" id="methods">
		<div class="container">
			<h2 class="section__title">Методы обработки</h2>
			<div class="methods">
				<div class="method-card">
					<div class="method-card__title">Горячий туман</div>
					<div class="method-card__text">Раскалённый газ нагревает рабочий раствор до 70°C. Образовавшийся конденсат подаётся наружу в виде облака тумана, который оседает на поверхностях, ядохимикаты при этом работают наиболее эффективно. (Опасный метод.)</div>
				</div>
				<div class="method-card">
					<div class="method-card__title">Холодный туман</div>
					<div class="method-card__text">Химический раствор под давлением с температурой 25°C. Облако заполняет все труднодоступные места в помещениях или, оседая на почву, на участках и фасадах зданий, не портя мебель, технику и растения.</div>
				</div>
				<div class="method-card">
					<div class="method-card__title">Дератизация</div>
					<div class="method-card__text">Приготовление, закладка ядоприманок от крыс и мышей в домах, помещениях и на территориях.</div>
				</div>
				<div class="method-card">
					<div class="method-card__title">Фумигация</div>
					<div class="method-card__text">Уничтожение насекомых в зернохранилищах, в частности от короеда в деревянных домах, от кротов — отравляющими ядовитыми газами.</div>
				</div>
				<div class="method-card">
					<div class="method-card__title">Дезинфекция патогенов</div>
					<div class="method-card__text">Комплекс мер уничтожения очагов инфекций, вирусов, плесени на 100% снизит риск вреда здоровью, уберёт проблему и обеспокоенность жителей — с применением технических средств, технологических карт с соблюдением нормативных актов технадзора и пожеланий клиентов.</div>
				</div>
				<div class="method-card">
					<div class="method-card__title">Дезинфекция воздуха, газация</div>
					<div class="method-card__text">Уничтожение запахов на молекулярном уровне — не маскирует запахи гари, затхлости, табака, разложения, химии.</div>
				</div>
				<div class="method-card">
					<div class="method-card__title">Гербицидная обработка</div>
					<div class="method-card__text">От борщевика Сосновского и других сорняков.</div>
				</div>
			</div>
		</div>
	</section>

	<!-- назначить специалиста / скидка ветеранам -->
	<section class="section section--alt">
		<div class="container">
			<div class="veterans">
				<div class="veterans__text">
					<strong>Назначить специалиста</strong><br>
					Ветеранам боевых действий, героям, ликвидаторам радиационных катастроф, ветеранам труда действует скидка.
				</div>
				<form class="veterans__form js-form" action="/source/php/order.php" method="post">
					<input type="hidden" name="source" value="Назначить специалиста">
					<input type="text" name="website" class="hp" tabindex="-1" autocomplete="off" aria-hidden="true">
					<input type="text" name="name" placeholder="Ваше имя" required>
					<input type="tel" name="phone" placeholder="+7 (___) ___-__-__" required>
					<button type="submit" class="btn btn--accent btn--block">Получить консультацию</button>
					<div class="form-status" role="status"></div>
					<div class="form-messengers">
						<span>Или напишите нам:</span>
						<a href="<?= h($msg["telegram"]) ?>" aria-label="Telegram"><svg><use href="#i-tg"></use></svg></a>
						<a href="<?= h($msg["max"]) ?>" aria-label="Max"><svg><use href="#i-max"></use></svg></a>
						<a href="<?= h($msg["whatsapp"]) ?>" aria-label="WhatsApp"><svg><use href="#i-wa"></use></svg></a>
					</div>
				</form>
			</div>
		</div>
	</section>

	<!-- зона обслуживания -->
	<section class="section">
		<div class="container">
			<div class="coverage">
				<div class="coverage__text">
					<strong>Наши специалисты выезжают во все районы Москвы и Московской области</strong>
					Обработка помещений — до 80 км от МКАД. Обработка участков — до 80 км от МКАД.
				</div>
				<a href="#order" class="btn btn--primary">Оставить заявку</a>
			</div>
		</div>
	</section>

	<!-- отзывы: карусель -->
	<section class="section section--alt" id="reviews">
		<div class="container">
			<h2 class="section__title">Отзывы наших клиентов</h2>
			<div class="reviews__ratings">
				<div class="reviews__rating"><strong>4.8</strong>средний рейтинг по отзывам клиентов</div>
			</div>
			<div class="reviews__carousel">
				<div class="reviews__track">
					<div class="review-card">
						<div class="review-card__head">
							<div class="review-card__avatar" style="background:#2f9e44">А</div>
							<div><div class="review-card__name">Анна К.</div><div class="review-card__date">12 марта 2026</div></div>
						</div>
						<div class="review-card__stars">★★★★★</div>
						<div class="review-card__text">Вызывали из-за тараканов на кухне. Приехали в тот же день, обработали холодным туманом — запаха почти нет. Через неделю никого не осталось. Спасибо за оперативность!</div>
					</div>
					<div class="review-card">
						<div class="review-card__head">
							<div class="review-card__avatar" style="background:#237a35">Д</div>
							<div><div class="review-card__name">Дмитрий С.</div><div class="review-card__date">28 января 2026</div></div>
						</div>
						<div class="review-card__stars">★★★★★</div>
						<div class="review-card__text">Клопы завелись после поездки. Мастер всё объяснил, дал договор с гарантией 3 года. Повторная обработка не понадобилась. Рекомендую.</div>
					</div>
					<div class="review-card">
						<div class="review-card__head">
							<div class="review-card__avatar" style="background:#1d6b8f">Е</div>
							<div><div class="review-card__name">Елена В.</div><div class="review-card__date">5 февраля 2026</div></div>
						</div>
						<div class="review-card__stars">★★★★☆</div>
						<div class="review-card__text">Обрабатывали участок от клещей перед дачным сезоном. Приехали вовремя, работу выполнили аккуратно. Единственное — ждала звонка-напоминания, но в целом всё отлично.</div>
					</div>
					<div class="review-card">
						<div class="review-card__head">
							<div class="review-card__avatar" style="background:#0b2039">И</div>
							<div><div class="review-card__name">Игорь П.</div><div class="review-card__date">19 ноября 2025</div></div>
						</div>
						<div class="review-card__stars">★★★★★</div>
						<div class="review-card__text">Заказывали дератизацию на складе. Работают по договору, всё официально, с актами. Грызунов больше не видим. Будем сотрудничать дальше.</div>
					</div>
					<div class="review-card">
						<div class="review-card__head">
							<div class="review-card__avatar" style="background:#57a03f">М</div>
							<div><div class="review-card__name">Марина Л.</div><div class="review-card__date">3 декабря 2025</div></div>
						</div>
						<div class="review-card__stars">★★★★★</div>
						<div class="review-card__text">Долго не могли избавиться от запаха в квартире после залива. Специалисты сделали озонирование — запах ушёл полностью. Очень довольны результатом.</div>
					</div>
					<div class="review-card">
						<div class="review-card__head">
							<div class="review-card__avatar" style="background:#3d7d2e">С</div>
							<div><div class="review-card__name">Сергей Т.</div><div class="review-card__date">22 октября 2025</div></div>
						</div>
						<div class="review-card__stars">★★★★☆</div>
						<div class="review-card__text">Была плесень в ванной и на балконе. Обработали, нанесли защитную плёнку, дали рекомендации по проветриванию. Прошло два месяца — не возвращается.</div>
					</div>
				</div>
				<div class="reviews__nav">
					<button type="button" class="reviews__btn" data-dir="prev" aria-label="Предыдущий отзыв">‹</button>
					<button type="button" class="reviews__btn" data-dir="next" aria-label="Следующий отзыв">›</button>
				</div>
			</div>
		</div>
	</section>

	<!-- FAQ -->
	<section class="section" id="faq">
		<div class="container">
			<h2 class="section__title">Часто задаваемые вопросы</h2>
			<div class="faq__list">
				<div class="faq-item">
					<button type="button" class="faq-item__question">Время обработки?<span class="faq-item__plus"></span></button>
					<div class="faq-item__answer"><div class="faq-item__answer-inner">В помещении: 40-60 минут<br>На участке: 40-120 минут и более</div></div>
				</div>
				<div class="faq-item">
					<button type="button" class="faq-item__question">Через сколько вернуться в помещение после обработки?<span class="faq-item__plus"></span></button>
					<div class="faq-item__answer"><div class="faq-item__answer-inner">Перед обработкой необходимо покинуть объект на 2 часа, в некоторых случаях на трое суток</div></div>
				</div>
				<div class="faq-item">
					<button type="button" class="faq-item__question">Может ли испортиться одежда и мебель?<span class="faq-item__plus"></span></button>
					<div class="faq-item__answer"><div class="faq-item__answer-inner">Наша химия не оставляет следов, не портит одежду, паркет, ламинат, фурнитуру и электронную технику.<br>Исключение - дезинфекция от плесени, специалист обеспечивает сохранность вещей</div></div>
				</div>
				<div class="faq-item">
					<button type="button" class="faq-item__question">Как понять, есть ли опасные насекомые в доме?<span class="faq-item__plus"></span></button>
					<div class="faq-item__answer"><div class="faq-item__answer-inner">Первые признаки является зуд, укусы, точки и покраснения на коже. Насекомые бывают микроскопических размеров, крупные оставляют экскременты в виде черных или коричневых точек мягкой мебели или элементах интерьера.</div></div>
				</div>
				<div class="faq-item">
					<button type="button" class="faq-item__question">Гарантии?<span class="faq-item__plus"></span></button>
					<div class="faq-item__answer"><div class="faq-item__answer-inner">Мы предоставляем гарантии по договору и безналичный расчет по желанию клиентов</div></div>
				</div>
				<div class="faq-item">
					<button type="button" class="faq-item__question">Какие вы используете препараты?<span class="faq-item__plus"></span></button>
					<div class="faq-item__answer"><div class="faq-item__answer-inner">Мы используем более 30 наименовании безопасных и сертифицированных средств от насекомых, грызунов, плесени</div></div>
				</div>
				<div class="faq-item">
					<button type="button" class="faq-item__question">Почему собственные методы не работают?<span class="faq-item__plus"></span></button>
					<div class="faq-item__answer"><div class="faq-item__answer-inner">У насекомых вырабатывается иммунитет к бесконтрольному применению общедоступных средств.<br>Только профессиональная дезинфекция, правильная дозировка препаратов и комбинирование могут дать результат на который вы рассчитываете.</div></div>
				</div>
				<div class="faq-item">
					<button type="button" class="faq-item__question">Почему МосКомДез?<span class="faq-item__plus"></span></button>
					<div class="faq-item__answer"><div class="faq-item__answer-inner">Наша компания работает на рынке 8 лет. Специалисты регулярно проходят переаттестацию, повышают квалификацию и гарантируют качество и результат. Мы дорожим доверием крупных промышленных предприятий, торговых объектов, горожан и некоммерческих организаций.</div></div>
				</div>
				<div class="faq-item">
					<button type="button" class="faq-item__question">Это опасно для ребёнка или аллергика?<span class="faq-item__plus"></span></button>
					<div class="faq-item__answer"><div class="faq-item__answer-inner">При соблюдении требований безопасности и применения безопасных средств нашими специалистами никакой опасности быть не может</div></div>
				</div>
			</div>
		</div>
	</section>

	<!-- контакты и карта -->
	<section class="section" id="contacts">
		<div class="container contacts__grid">
			<div class="contacts__list">
				<div class="contacts__phones"><b>Телефоны</b><?php foreach ($phones as $p): ?><a href="tel:<?= h($p["tel"]) ?>"><?= h($p["display"]) ?></a><?php endforeach; ?></div>
				<div><b>Почта</b><a href="mailto:<?= h($email) ?>"><?= h($email) ?></a></div>
				<div><b>Адрес</b><?= h($config["address"]) ?></div>
				<div><b>Лицензия</b><?= h($config["license"]) ?></div>
				<a href="#order" class="btn btn--primary">Оставить заявку</a>
			</div>
			<!-- Яндекс-карта: координаты временные, заменить на адрес офиса -->
			<div class="contacts__map">
				<iframe src="https://yandex.ru/map-widget/v1/?ll=37.617700%2C55.755800&amp;z=12" title="Карта — зона обслуживания" loading="lazy" allowfullscreen></iframe>
			</div>
		</div>
	</section>

	<!-- заказ -->
	<section class="section section--alt" id="order">
		<div class="container">
			<h2 class="section__title">Оставить заявку на обработку</h2>
			<form class="hero__form order__form js-form" action="/source/php/order.php" method="post">
				<input type="hidden" name="source" value="Заявка на обработку">
				<input type="text" name="website" class="hp" tabindex="-1" autocomplete="off" aria-hidden="true">
				<input type="text" name="name" placeholder="Ваше имя" required>
				<input type="tel" name="phone" placeholder="+7 (___) ___-__-__" required>
				<input type="text" name="comment" placeholder="Комментарий (необязательно)">
				<button type="submit" class="btn btn--primary btn--block">Отправить заявку</button>
				<div class="form-status" role="status"></div>
				<div class="hero__form-note">Отправляя свои данные, вы соглашаетесь с <a href="/politika/">политикой конфиденциальности</a></div>
			</form>
		</div>
	</section>

	<!-- футер по образцу topol-eco.ru -->
	<footer class="footer">
		<div class="container">
			<div class="footer__columns">
				<div class="footer__col">
					<div class="footer__col-title">О компании</div>
					<ul>
						<li><a href="#process">Порядок обработки</a></li>
						<li><a href="#methods">Методы обработки</a></li>
						<li><a href="/politika/">Политика конфиденциальности</a></li>
					</ul>
				</div>
				<div class="footer__col">
					<div class="footer__col-title">Обработки</div>
					<ul>
						<li><a href="/tarakany/">Тараканы</a></li>
						<li><a href="/klopy/">Клопы</a></li>
						<li><a href="/kleshchi/">Клещи</a></li>
						<li><a href="/komary/">Комары</a></li>
						<li><a href="/gryzuny/">Грызуны</a></li>
						<li><a href="/plesen/">Плесень</a></li>
					</ul>
				</div>
				<div class="footer__col">
					<div class="footer__col-title">Цены</div>
					<ul>
						<li><a href="#prices">Цены на услуги</a></li>
						<li><a href="#cleaning">Клининг</a></li>
						<li><a href="#faq">Частые вопросы</a></li>
						<li><a href="#reviews">Отзывы</a></li>
					</ul>
				</div>
				<div class="footer__col">
					<div class="footer__col-title">Пользователям</div>
					<ul>
						<li><a href="#order">Заказать онлайн</a></li>
						<li><a href="#contacts">Контакты</a></li>
					</ul>
				</div>
				<div class="footer__col">
					<div class="footer__col-title">Контакты</div>
					<ul>
						<?php foreach ($phones as $p): ?>
						<li><a href="tel:<?= h($p["tel"]) ?>"><?= h($p["display"]) ?></a></li>
						<?php endforeach; ?>
						<li><a href="mailto:<?= h($email) ?>"><?= h($email) ?></a></li>
						<li><?= h($config["address"]) ?></li>
					</ul>
				</div>
			</div>

			<div class="footer__brand">
				<div>
					<div class="footer__brand-name">МосКомДез</div>
					<div class="footer__brand-legal">
						Региональная служба дезинфекции. ИП Мунтаниол А.Ю., ИНН 391103958473.<br>
						Номер лицензии: 77.00165.0.25 (ЕРУЛ № Л04-00111-70197).
					</div>
				</div>
				<!-- CTA + иконки оплаты под кнопкой -->
				<div class="footer__order">
					<a href="#order" class="btn btn--accent">Заказать онлайн</a>
					<div class="footer__pay">
						<svg class="pay-icon" aria-label="СБП"><use href="#i-sbp"></use></svg>
						<svg class="pay-icon" aria-label="Мир"><use href="#i-mir"></use></svg>
						<svg class="pay-icon" aria-label="Visa"><use href="#i-visa"></use></svg>
						<svg class="pay-icon pay-icon--mc" aria-label="Mastercard"><use href="#i-mc"></use></svg>
					</div>
				</div>
			</div>

			<!-- соцсети отдельным блоком -->
			<div class="footer__socials">
				<span class="footer__col-title">Мы в соцсетях</span>
				<div class="footer__social">
					<a href="<?= h($msg["telegram"]) ?>" aria-label="Telegram"><svg><use href="#i-tg"></use></svg></a>
					<a href="<?= h($msg["max"]) ?>" aria-label="Max"><svg><use href="#i-max"></use></svg></a>
				</div>
			</div>

			<div class="footer__bottom">
				<div>© 2026 Региональная служба дезинфекции. Все права защищены.</div>
				<a href="/politika/">Политика конфиденциальности</a>
			</div>
		</div>
	</footer>

	<script src="/source/js/main.js"></script>
</body>
</html>
