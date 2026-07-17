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

	<!-- 1. герой: заголовок · чек-лист · сноска | форма. Рабочий и дым — фоновые слои, сетку не двигают -->
	<section class="hero-srv">
		<div class="hero-srv__smoke" aria-hidden="true"></div>
		<img class="hero-srv__worker" src="<?= h($hero["worker"]) ?>" alt="<?= h($hero["worker_alt"]) ?>" width="823" height="1000" loading="eager" aria-hidden="true">

		<div class="container">
			<div class="hero-srv__cols">

				<div class="hero-srv__left">
					<h1 class="hero-srv__title"><?= h($page["h1"]) ?></h1>

					<ul class="hero-srv__checks">
						<?php foreach ($hero["checks"] as $c): ?>
						<li class="hero-srv__check">
							<span class="hero-srv__check-icon"><svg aria-hidden="true"><use href="#<?= h($c["icon"]) ?>"></use></svg></span>
							<span class="hero-srv__check-text"><?= h($c["text"]) ?></span>
						</li>
						<?php endforeach; ?>
					</ul>

					<?php // сноска своя у каждой услуги; пусто — не выводим ?>
					<?php if (!empty($page["hero_note"])): ?>
					<p class="hero-srv__note"><span class="hero-srv__note-star">*</span><?= h($page["hero_note"]) ?></p>
					<?php endif; ?>
				</div>

				<div class="hero-srv__right">
					<form class="hero-srv__form js-form" action="/source/php/order.php" method="post">
						<input type="hidden" name="source" value="Услуга: <?= h($service["name"]) ?>">
						<input type="text" name="website" class="hp" tabindex="-1" autocomplete="off" aria-hidden="true">
						<div class="hero-srv__form-eyebrow"><?= h($hero["form"]["eyebrow"]) ?></div>
						<div class="hero-srv__form-title"><?= h($hero["form"]["title"]) ?></div>
						<div class="hero-srv__form-sub"><?= h($hero["form"]["subtitle"]) ?></div>
						<input type="text" name="name" placeholder="Ваше имя" autocomplete="name" required>
						<input type="tel" name="phone" placeholder="+7 (___) ___-__-__" autocomplete="tel" required>
						<button type="submit" class="btn btn--primary btn--block"><?= h($hero["form"]["button"]) ?></button>
						<div class="form-status" role="status"></div>
						<div class="hero-srv__form-note">Отправляя свои данные, вы соглашаетесь с <a href="/politika/">политикой конфиденциальности</a></div>
					</form>
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

	<!-- 4. основной текст: вводный абзац виден всегда, остальное под кнопкой -->
	<section class="section" id="about">
		<div class="container">
			<?php
			// виден только первый абзац; остальное вводного текста и весь SEO-текст — под кнопкой
			list($textFirst, $textRest) = html_first_para($page["intro_html"]);
			$textMore = $textRest . $page["seo_html"];
			?>
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

	<!-- 12. форма заявки (квиз) — ждём логику, разметки пока нет намеренно -->

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
