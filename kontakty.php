<?php
// Страница контактов. Тексты и адрес склада — data/contacts.json, телефоны/почта/
// мессенджеры/юр. адрес — общие из bootstrap.php (data/site.json), чтобы не дублировать.
require __DIR__ . "/source/php/bootstrap.php";

$contacts = data_load("contacts");
if (empty($contacts["visible"])) {
	require __DIR__ . "/404.php";
	exit;
}

// адрес приводим к каноническому виду с хвостовым слешем, как на страницах услуг
if (parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH) !== "/kontakty/") {
	header("Location: /kontakty/", true, 301);
	exit;
}

$cLabels = $contacts["labels"];
$cMap    = $contacts["map"];
$cCta    = $contacts["cta"];

// телефоны в site.json лежат списком: первый считаем городским, второй мобильным
$phoneMain   = isset($phones[0]) ? $phones[0] : null;
$phoneMobile = isset($phones[1]) ? $phones[1] : null;

// @-ник в телеграме заказчик пока не дал — до тех пор показываем обычную подпись
$tgHandle = trim($contacts["telegram_handle"]) !== "" ? $contacts["telegram_handle"] : $cLabels["telegram"];
?>
<!DOCTYPE html>
<html lang="ru">
<head>
	<?php
	$headTitle     = $contacts["seo"]["title"];
	$headDescr     = $contacts["seo"]["description"];
	$headKeywords  = $contacts["seo"]["keywords"];
	$headCanonical = $siteUrl . "/kontakty/";
	$headOgType    = "website";
	require __DIR__ . "/source/include/head.php";
	?>
</head>
<body>

	<?php require __DIR__ . "/source/include/header.html"; ?>

	<main>

	<!-- 1. форма обратного звонка слева + карточка контактов справа -->
	<section class="section">
		<div class="container">
			<h1 class="section__title contacts__title"><?= h($contacts["h1"]) ?></h1>

			<div class="contacts">

				<div class="contacts__form-side">
					<p class="contacts__lead"><?= $contacts["form"]["lead"] ?></p>

					<form class="contacts__form js-form" action="/source/php/order.php" method="post">
						<input type="hidden" name="source" value="Страница контактов">
						<input type="text" name="website" class="hp" tabindex="-1" autocomplete="off" aria-hidden="true">
						<input type="text" name="name" placeholder="Имя" autocomplete="name" aria-label="Ваше имя" required>
						<input type="tel" name="phone" placeholder="+7 (___) ___-__-__" autocomplete="tel" aria-label="Номер телефона" required>
						<textarea name="comment" rows="4" placeholder="Комментарии" aria-label="Комментарии"></textarea>
						<div class="hero__form-note">Нажимая на кнопку, я соглашаюсь с <a href="/politika/">правилами обработки данных</a></div>
						<button type="submit" class="btn btn--accent"><?= h($contacts["form"]["button"]) ?></button>
						<div class="form-status" role="status"></div>
					</form>
				</div>

				<!-- тёмная карточка: у донора чёрная с жёлтыми уголками, у нас фирменный navy -->
				<div class="contacts__card">
					<ul class="contacts__list">

						<?php if ($phoneMain): ?>
						<li class="contacts__item">
							<span class="contacts__icon"><svg><use href="#i-phone"></use></svg></span>
							<span class="contacts__value">
								<a href="tel:<?= h($phoneMain["tel"]) ?>"><?= h($phoneMain["display"]) ?></a>
							</span>
						</li>
						<?php endif; ?>

						<li class="contacts__item">
							<span class="contacts__icon"><svg><use href="#i-tg"></use></svg></span>
							<span class="contacts__value">
								<a href="<?= h($msg["telegram"]) ?>" target="_blank" rel="noopener"><?= h($tgHandle) ?></a>
							</span>
						</li>

						<?php if ($phoneMobile): ?>
						<li class="contacts__item">
							<span class="contacts__icon"><svg><use href="#i-mobile"></use></svg></span>
							<span class="contacts__value">
								<a href="tel:<?= h($phoneMobile["tel"]) ?>"><?= h($phoneMobile["display"]) ?></a>
							</span>
						</li>
						<?php endif; ?>

						<li class="contacts__item">
							<span class="contacts__icon"><svg><use href="#i-mail"></use></svg></span>
							<span class="contacts__value">
								<a href="mailto:<?= h($email) ?>"><?= h($email) ?></a>
							</span>
						</li>

						<li class="contacts__item">
							<span class="contacts__icon"><svg><use href="#i-pin"></use></svg></span>
							<span class="contacts__value">
								<span class="contacts__label"><?= h($cLabels["warehouse"]) ?></span>
								<?= h($contacts["warehouse"]) ?>
							</span>
						</li>

						<li class="contacts__item">
							<span class="contacts__icon"><svg><use href="#i-pin"></use></svg></span>
							<span class="contacts__value">
								<span class="contacts__label"><?= h($cLabels["legal"]) ?></span>
								<?= h($config["address"]) ?>
							</span>
						</li>

						<li class="contacts__item">
							<span class="contacts__icon"><svg><use href="#i-truck"></use></svg></span>
							<span class="contacts__value"><?= h($contacts["schedule_visit"]) ?></span>
						</li>

						<li class="contacts__item">
							<span class="contacts__icon"><svg><use href="#i-24"></use></svg></span>
							<span class="contacts__value"><?= h($contacts["schedule_orders"]) ?></span>
						</li>

					</ul>
				</div>

			</div>
		</div>
	</section>

	<!-- 2. точка на карте -->
	<section class="section section--alt" id="map">
		<div class="container">
			<div class="contacts__eyebrow"><?= h($cMap["eyebrow"]) ?></div>
			<h2 class="section__title contacts__title"><?= h($cMap["title"]) ?></h2>

			<!-- своей кнопки «открыть в картах» не ставим: она уже есть внутри виджета -->
			<div class="contacts__map">
				<iframe src="<?= h($cMap["src"]) ?>" title="<?= h($cMap["title"]) ?> на карте" loading="lazy" allowfullscreen></iframe>
			</div>
		</div>
	</section>

	<!-- 3. повторный призыв: чек-лист слева, форма справа -->
	<section class="section contacts-cta">
		<div class="container">
			<div class="contacts-cta__inner">

				<div class="contacts-cta__text">
					<h2 class="contacts-cta__title"><?= h($cCta["title"]) ?></h2>
					<ul class="contacts-cta__checks">
						<?php foreach ($cCta["checks"] as $check): ?>
						<li><?= h($check) ?></li>
						<?php endforeach; ?>
					</ul>
				</div>

				<form class="contacts-cta__form js-form" action="/source/php/order.php" method="post">
					<input type="hidden" name="source" value="Контакты — вызвать специалиста">
					<input type="text" name="website" class="hp" tabindex="-1" autocomplete="off" aria-hidden="true">
					<div class="contacts-cta__form-title"><?= h($cCta["form_title"]) ?></div>
					<input type="text" name="name" placeholder="Имя" autocomplete="name" aria-label="Ваше имя" required>
					<input type="tel" name="phone" placeholder="+7 (___) ___-__-__" autocomplete="tel" aria-label="Номер телефона" required>
					<textarea name="comment" rows="3" placeholder="Комментарии" aria-label="Комментарии"></textarea>
					<div class="hero__form-note">Нажимая на кнопку, я соглашаюсь с <a href="/politika/">правилами обработки данных</a></div>
					<button type="submit" class="btn btn--accent btn--block"><?= h($cCta["button"]) ?></button>
					<div class="form-status" role="status"></div>

					<div class="contacts-cta__chat"><?= h($cCta["chat_title"]) ?></div>
					<!-- ссылки те же, что правятся в админке: max / whatsapp / telegram + почта.
					     Порядок как в героях главной и страницы услуги -->
					<div class="contacts-cta__messengers">
						<a class="msg-circle msg-circle--max" href="<?= h($msg["max"]) ?>" target="_blank" rel="noopener" aria-label="Max"><svg><use href="#i-max"></use></svg></a>
						<a class="msg-circle msg-circle--wa" href="<?= h($msg["whatsapp"]) ?>" target="_blank" rel="noopener" aria-label="WhatsApp"><svg><use href="#i-wa"></use></svg></a>
						<a class="msg-circle msg-circle--tg" href="<?= h($msg["telegram"]) ?>" target="_blank" rel="noopener" aria-label="Telegram"><svg><use href="#i-tg"></use></svg></a>
						<a class="msg-circle msg-circle--mail" href="mailto:<?= h($email) ?>" aria-label="Почта"><svg><use href="#i-mail"></use></svg></a>
					</div>
				</form>

			</div>
		</div>
	</section>

	</main>

	<?php require __DIR__ . "/source/include/footer.html"; ?>

	<?php require __DIR__ . "/source/include/modal.php"; ?>

	<script src="<?= h(asset("/source/js/main.js")) ?>"></script>
</body>
</html>
