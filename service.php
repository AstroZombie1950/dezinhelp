<?php
// Страница услуги. Сюда .htaccess отправляет всё, что похоже на /<slug>/.
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

// общие данные страниц услуг: хиро, галерея, заголовки — одно место на все 27
$servicePage  = data_load("service-page");
$homeServices = data_load("home-services");

$blocks = isset($page["blocks"]) ? $page["blocks"] : array();
$seo    = isset($page["seo"]) ? $page["seo"] : array();
$hero   = $servicePage["hero"];
// картинка и цена геро — свои у каждой услуги, иначе общий дефолт из service-page.json
$heroImg    = !empty($page["hero_image"]) ? $page["hero_image"] : $hero["worker"];
$heroImgAlt = !empty($page["hero_image_alt"]) ? $page["hero_image_alt"] : (isset($hero["worker_alt"]) ? $hero["worker_alt"] : "");
$heroPrice  = array_key_exists("hero_price", $page) ? trim((string) $page["hero_price"]) : (isset($hero["price"]) ? $hero["price"] : "");
$url    = $siteUrl . $path;
$title  = isset($seo["title"]) ? $seo["title"] : $service["name"];
$descr  = isset($seo["description"]) ? $seo["description"] : "";
?>
<!DOCTYPE html>
<html lang="ru">
<head>
	<!-- кодировка и адаптив -->
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">

	<!-- основное SEO: всё из data/services/<slug>.json -->
	<title><?= h($title) ?></title>
	<meta name="description" content="<?= h($descr) ?>">
	<meta name="keywords" content="<?= h(isset($seo["keywords"]) ? $seo["keywords"] : "") ?>">
	<meta name="author" content="МосКомДез">
	<meta name="robots" content="index, follow">
	<link rel="canonical" href="<?= h($url) ?>">

	<!-- Open Graph -->
	<meta property="og:type" content="article">
	<meta property="og:title" content="<?= h($title) ?>">
	<meta property="og:description" content="<?= h($descr) ?>">
	<meta property="og:url" content="<?= h($url) ?>">
	<meta property="og:site_name" content="МосКомДез">
	<meta property="og:locale" content="ru_RU">
	<meta property="og:image" content="<?= h($siteUrl) ?>/source/img/og-cover.jpg">

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

	<!-- 1. герой: каркас как на главной — заголовок и лид на всю ширину, слева контент+форма, справа картинка с ценой -->
	<section class="hero-srv">
		<div class="container">

			<h1 class="hero-srv__title"><?php
			// первые пару слов заголовка — фирменным градиентом, как на главной
			$hWords = preg_split('/\s+/', trim($page["h1"]));
			$hHead  = h(implode(" ", array_slice($hWords, 0, 2)));
			$hTail  = h(implode(" ", array_slice($hWords, 2)));
			echo '<span class="hero__title-mark">' . $hHead . '</span>' . ($hTail !== "" ? " " . $hTail : "");
			?></h1>

			<div class="hero-srv__cols">

				<div class="hero-srv__left">
					<?php require __DIR__ . "/source/include/hero-checks.php"; ?>

					<?php // сноска своя у каждой услуги; пусто — не выводим ?>
					<?php if (!empty($page["hero_note"])): ?>
					<p class="hero-srv__note"><span class="hero-srv__note-star">*</span><?= h($page["hero_note"]) ?></p>
					<?php endif; ?>

					<?php // блок заявки — тот же, что на главной: выноска + телефон + мессенджеры ?>
					<div class="hero__bubble">СКИДКА 10%, ОСТАВЬТЕ ЗАЯВКУ ЗДЕСЬ</div>

					<form class="hero__form js-form" action="/source/php/order.php" method="post">
						<input type="hidden" name="source" value="Услуга: <?= h($service["name"]) ?> — герой">
						<input type="text" name="website" class="hp" tabindex="-1" autocomplete="off" aria-hidden="true">
						<input type="tel" name="phone" placeholder="+7 (___) ___-__-__" autocomplete="tel" required>
						<button type="submit" class="btn btn--primary btn--block">Оставить заявку</button>
						<div class="form-status" role="status"></div>
						<div class="hero__form-note">Нажимая на кнопку, я соглашаюсь с <a href="/politika/">правилами обработки данных</a></div>
					</form>

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

				<!-- правая колонка: картинка услуги + плашка цены в углу (плашка — та же, что на главной) -->
				<div class="hero-srv__right">
					<div class="hero-srv__figure">
						<img class="hero-srv__img" src="<?= h($heroImg) ?>" alt="<?= h($heroImgAlt) ?>" loading="eager">
						<?php if ($heroPrice !== ""): ?>
						<div class="hero__price">
							<span class="hero__price-top">от</span>
							<span class="hero__price-num"><?= h($heroPrice) ?><span class="hero__price-cur">₽</span></span>
							<span class="hero__price-bottom">за услугу</span>
						</div>
						<?php endif; ?>
					</div>
				</div>

			</div>
		</div>
	</section>

	<!-- 2. бегущая строка: вредители -->
	<?php $marqueeItems = array("Короед", "Комары", "ФЗ-44", "Препараты по стандартам ЕврАзЭС", "Грызуны", "Клопы", "Тараканы", "Плесень", "Запахи", "Мухи", "Муравьи", "Блохи", "Клещи", "Осы и шершни"); ?>
	<?php require __DIR__ . "/source/include/block-marquee.php"; ?>

	<!-- 3. объекты санобработки -->
	<?php if (!empty($blocks["gallery"])): ?>
	<?php require __DIR__ . "/source/include/block-gallery.php"; ?>
	<?php endif; ?>

	<!-- 4. основной текст: вводный абзац виден всегда, остальное под кнопкой. Слева — картинка услуги (если задана) -->
	<section class="section" id="about">
		<div class="container">
			<?php
			// виден только первый абзац; остальное вводного текста и весь SEO-текст — под кнопкой
			list($textFirst, $textRest) = html_first_para($page["intro_html"]);
			$textMore = $textRest . $page["seo_html"];
			// картинка слева от текста — своя у каждой услуги, пусто = текст на всю ширину
			$textImg    = !empty($page["text_image"]) ? $page["text_image"] : "";
			$textImgAlt = !empty($page["text_image_alt"]) ? $page["text_image_alt"] : "";
			?>
			<div class="srv-about<?= $textImg !== "" ? " srv-about--media" : "" ?>">

				<?php if ($textImg !== ""): ?>
				<div class="srv-about__media">
					<img src="<?= h($textImg) ?>" alt="<?= h($textImgAlt) ?>" loading="lazy">
				</div>
				<?php endif; ?>

				<div class="srv-text">
					<div class="srv-text__intro"><?= $textFirst ?></div>

					<?php if (trim($textMore) !== ""): ?>
					<div class="srv-text__more js-collapse">
						<div class="srv-text__inner"><?= $textMore ?></div>
					</div>
					<button type="button" class="srv-text__toggle js-collapse-toggle" aria-expanded="false"
						data-more="<?= h($servicePage["text"]["more"]) ?>" data-less="<?= h($servicePage["text"]["less"]) ?>"><?= h($servicePage["text"]["more"]) ?></button>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</section>

	<!-- 5. цены: несколько таблиц, каждая скрывается отдельно; форма одна на секцию -->
	<?php if (!empty($page["prices"]["visible"]) && !empty($page["prices"]["groups"])): ?>
	<section class="section section--alt" id="prices">
		<div class="container">
			<?php $pricesData = $page["prices"]; $pricesLayout = "full"; $pricesButton = $servicePage["prices"]["button"]; ?>
			<?php require __DIR__ . "/source/include/block-prices.php"; ?>
		</div>
	</section>
	<?php endif; ?>

	<!-- 6. порядок обработки -->
	<?php if (!empty($blocks["process"])): ?>
	<?php require __DIR__ . "/source/include/block-process.php"; ?>
	<?php endif; ?>

	<!-- 7. методы обработки: две карточки и иллюстрация между ними -->
	<?php if (!empty($blocks["methods"])): ?>
	<?php require __DIR__ . "/source/include/block-methods-service.php"; ?>
	<?php endif; ?>

	<!-- 8. свидетельства -->
	<?php if (!empty($blocks["certificates"])): ?>
	<?php require __DIR__ . "/source/include/block-certificates.php"; ?>
	<?php endif; ?>

	<!-- 9. бегущая строка: только сертифицированные препараты -->
	<?php $marqueeItems = array_fill(0, 8, "ТОЛЬКО СЕРТИФИЦИРОВАННЫЕ ПРЕПАРАТЫ"); ?>
	<?php require __DIR__ . "/source/include/block-marquee.php"; ?>

	<!-- 10. скидка ветеранам -->
	<?php if (!empty($blocks["veterans"])): ?>
	<?php require __DIR__ . "/source/include/block-veterans.php"; ?>
	<?php endif; ?>

	<!-- 11. зона выезда -->
	<?php if (!empty($blocks["coverage"])): ?>
	<?php require __DIR__ . "/source/include/block-coverage.php"; ?>
	<?php endif; ?>

	<!-- 12. форма заявки (квиз): вариант текущей услуги на шаге «проблема» отмечен заранее -->
	<?php if (!empty($blocks["quiz"])): ?>
	<?php $quizData = data_load("quiz"); $quizSlug = $canonical; $quizSource = "Квиз — " . $service["name"]; ?>
	<?php require __DIR__ . "/source/include/block-quiz.php"; ?>
	<?php endif; ?>

	<!-- 13. виды услуг: текущую услугу из слайдера выкидываем -->
	<?php if (!empty($blocks["services"])): ?>
	<section class="section section--alt" id="services">
		<div class="container">
			<?php $srvData = $homeServices; $srvTitle = $servicePage["services"]["title"]; $srvExclude = $canonical; ?>
			<?php require __DIR__ . "/source/include/block-services-slider.php"; ?>
		</div>
	</section>
	<?php endif; ?>

	<!-- 14. частые вопросы: свои у каждой услуги -->
	<?php if (!empty($blocks["faq"])): ?>
	<?php $faqItems = $page["faq"]; $faqTitle = "Часто задаваемые вопросы"; ?>
	<?php require __DIR__ . "/source/include/block-faq.php"; ?>
	<?php endif; ?>

	</main>

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