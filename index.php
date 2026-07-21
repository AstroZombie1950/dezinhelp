<?php
// конфиг, хелперы, данные для шапки и футера
require __DIR__ . "/source/php/bootstrap.php";

// данные только для главной
$prices       = data_load("prices");
$homeServices = data_load("home-services");
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
	<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;600;700;800&display=swap" rel="stylesheet">

	<!-- стили -->
	<link rel="stylesheet" href="/source/css/main.css">
</head>
<body>

	<?php require __DIR__ . "/source/include/header.html"; ?>

	<!-- герой: заголовок · абзац · чек-лист + форма · рабочий с ценой -->
	<section class="hero" id="hero">
		<div class="container">

			<!-- заголовок на всю ширину (акцент-заливка на «Санитарные услуги СЭС») -->
			<h1 class="hero__title"><span class="hero__title-mark">Санитарные услуги СЭС</span> в Центральном Федеральном округе</h1>

			<!-- лид-абзац; ключевые слова — фирменным акцентом -->
			<p class="hero__lead">МосКомДез — Избавим качественно и быстро от <a class="hero__hl" href="/unichtozhenie-nasekomyh/">насекомых</a>, <a class="hero__hl" href="/izbavitsya-ot-krys/">грызунов</a>, <a class="hero__hl" href="/obrabotka-ot-pleseni/">плесени</a>, <a class="hero__hl" href="/udalenie-zapahov/">запахов</a> и <a class="hero__hl" href="/unichtozhenie-borshchevika/">сорняков</a> в любом помещении и на участках, вылечим <a class="hero__hl" href="/lechenie-derevev/">деревья</a> от болезней и паразитов. Цена СЭС услуг одна из самых низких в Москве и МО</p>

			<!-- две колонки: слева контент+форма, справа рабочий -->
			<div class="hero__cols">

				<!-- левая колонка -->
				<div class="hero__left">

					<!-- чек-лист преимуществ (галочки — SVG, маркеры — акцент) -->
					<?php require __DIR__ . "/source/include/hero-checks.php"; ?>

					<!-- выноска-призыв над формой (хвостик указывает вниз) -->
					<div class="hero__bubble">СКИДКА 10%, ОСТАВЬТЕ ЗАЯВКУ ЗДЕСЬ</div>

					<!-- форма: одно поле — телефон с маской, отправка в Телеграм -->
					<form class="hero__form js-form" action="/source/php/order.php" method="post">
						<input type="hidden" name="source" value="Герой — заявка">
						<input type="text" name="website" class="hp" tabindex="-1" autocomplete="off" aria-hidden="true">
						<input type="tel" name="phone" placeholder="+7 (___) ___-__-__" autocomplete="tel" required>
						<button type="submit" class="btn btn--primary btn--block">Оставить заявку</button>
						<div class="form-status" role="status"></div>
						<div class="hero__form-note">Нажимая на кнопку, я соглашаюсь с <a href="/politika/">правилами обработки данных</a></div>
					</form>

					<!-- онлайн-чат: контурные иконки мессенджеров + почта -->
					<div class="hero__chat">
						<div class="hero__chat-title">Или напишите нам в онлайн-чат</div>
						<div class="hero__messengers">
							<a class="msg-line" href="<?= h($msg["max"]) ?>" target="_blank" rel="noopener" aria-label="Max"><svg><use href="#i-max"></use></svg></a>
							<a class="msg-line" href="<?= h($msg["whatsapp"]) ?>" target="_blank" rel="noopener" aria-label="WhatsApp"><svg><use href="#i-wa"></use></svg></a>
							<a class="msg-line" href="<?= h($msg["telegram"]) ?>" target="_blank" rel="noopener" aria-label="Telegram"><svg><use href="#i-tg"></use></svg></a>
							<a class="msg-line" href="mailto:<?= h($email) ?>" aria-label="Почта"><svg><use href="#i-mail"></use></svg></a>
						</div>
					</div>

				</div>

				<!-- правая колонка: рабочий + плашка цены -->
				<div class="hero__right">
					<div class="hero__figure">
						<img class="hero__worker" src="/source/img/hero/worker.webp" alt="Специалист службы дезинфекции в защитном костюме" width="880" height="619" loading="eager">
						<div class="hero__price">
							<span class="hero__price-top">от</span>
							<span class="hero__price-num">1500<span class="hero__price-cur">₽</span></span>
							<span class="hero__price-bottom">за услугу</span>
						</div>
					</div>
				</div>

			</div>
		</div>
	</section>

	<!-- бегущая строка: вредители -->
	<?php $marqueeItems = array("Короед", "Комары", "ФЗ-44", "Препараты по стандартам ЕврАзЭС", "Грызуны", "Клопы", "Тараканы", "Плесень", "Запахи", "Мухи", "Муравьи", "Блохи", "Клещи", "Осы и шершни"); ?>
	<?php require __DIR__ . "/source/include/block-marquee.php"; ?>

	<!-- услуги: слайдер карточек + прайс -->
	<section class="section section--alt" id="prices">
		<div class="container">
			<?php $srvData = $homeServices; ?>
			<?php require __DIR__ . "/source/include/block-services-slider.php"; ?>

			<?php $pricesData = $prices; $pricesLayout = "cols"; $pricesButton = ""; ?>
			<?php require __DIR__ . "/source/include/block-prices.php"; ?>
		</div>
	</section>

	<?php require __DIR__ . "/source/include/block-process.php"; ?>

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

	<?php require __DIR__ . "/source/include/block-methods.php"; ?>

	<!-- цифры о компании: счётчики с анимацией от нуля -->
	<?php require __DIR__ . "/source/include/block-stats.php"; ?>

	<!-- бегущая строка: только сертифицированные препараты -->
	<?php $marqueeItems = array_fill(0, 8, "ТОЛЬКО СЕРТИФИЦИРОВАННЫЕ ПРЕПАРАТЫ"); ?>
	<?php require __DIR__ . "/source/include/block-marquee.php"; ?>

	<?php require __DIR__ . "/source/include/block-certificates.php"; ?>

	<?php require __DIR__ . "/source/include/block-veterans.php"; ?>

	<?php require __DIR__ . "/source/include/block-reviews.php"; ?>

	<!-- примеры работ: слайдер «до/после» -->
	<?php require __DIR__ . "/source/include/block-works.php"; ?>

	<?php require __DIR__ . "/source/include/block-coverage.php"; ?>

	<!-- населённые пункты выезда: список городов -->
	<?php require __DIR__ . "/source/include/block-cities.php"; ?>

	<?php $faqData = data_load("faq"); $faqItems = $faqData["items"]; $faqTitle = $faqData["title"]; ?>
	<?php require __DIR__ . "/source/include/block-faq.php"; ?>

	<?php require __DIR__ . "/source/include/footer.html"; ?>

	<?php require __DIR__ . "/source/include/modal.php"; ?>

	<!-- лайтбокс сертификатов: увеличенный просмотр по клику на карточку -->
	<div class="lightbox" id="cert-lightbox" aria-hidden="true">
		<div class="lightbox__overlay" data-modal-close></div>
		<div class="lightbox__dialog">
			<button type="button" class="lightbox__close" data-modal-close aria-label="Закрыть">&times;</button>
			<img class="lightbox__img" src="" alt="">
		</div>
	</div>

	<script src="/source/js/main.js"></script>
</body>
</html>