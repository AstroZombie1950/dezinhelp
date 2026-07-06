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

	<?php require __DIR__ . "/source/include/header.html"; ?>

	<!-- герой: заголовок · абзац · чек-лист + форма · рабочий с ценой -->
	<section class="hero" id="hero">
		<div class="container">

			<!-- заголовок на всю ширину (акцент-заливка на «Санитарные услуги СЭС») -->
			<h1 class="hero__title"><span class="hero__title-mark">Санитарные услуги СЭС</span> в Центральном Федеральном округе</h1>

			<!-- лид-абзац; ключевые слова — фирменным акцентом -->
			<p class="hero__lead">МосКомДез — Избавим качественно и быстро от <span class="hero__hl">насекомых</span>, <span class="hero__hl">грызунов</span>, <span class="hero__hl">плесени</span>, <span class="hero__hl">запахов</span> и <span class="hero__hl">сорняков</span> в любом помещении и на участках, вылечим <span class="hero__hl">деревья</span> от болезней и паразитов. Цена СЭС услуг одна из самых низких в Москве и МО</p>

			<!-- две колонки: слева контент+форма, справа рабочий -->
			<div class="hero__cols">

				<!-- левая колонка -->
				<div class="hero__left">

					<!-- чек-лист преимуществ (галочки — SVG, маркеры — акцент) -->
					<ul class="hero__checks">
						<li class="hero__check">
							<span class="hero__check-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="4"/><path d="M8 12.5l2.5 2.5 5-5.5"/></svg></span>
							<div class="hero__check-body">
								<div class="hero__check-title">Устраним проблему <mark class="hero__mark">за 1 час на 100%.</mark></div>
								<div class="hero__check-sub">Если не поможет — приедем бесплатно еще раз!</div>
							</div>
						</li>
						<li class="hero__check">
							<span class="hero__check-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="4"/><path d="M8 12.5l2.5 2.5 5-5.5"/></svg></span>
							<div class="hero__check-body">
								<div class="hero__check-title">Гарантия <mark class="hero__mark">до 2-х лет.</mark></div>
								<div class="hero__check-sub">Прописана в договоре.</div>
							</div>
						</li>
						<li class="hero__check">
							<span class="hero__check-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="4"/><path d="M8 12.5l2.5 2.5 5-5.5"/></svg></span>
							<div class="hero__check-body">
								<div class="hero__check-title"><mark class="hero__mark">Лицензия</mark> СЭС</div>
								<div class="hero__check-sub">Сотрудники предъявят документы по приезду.</div>
							</div>
						</li>
						<li class="hero__check">
							<span class="hero__check-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="4"/><path d="M8 12.5l2.5 2.5 5-5.5"/></svg></span>
							<div class="hero__check-body">
								<div class="hero__check-title"><mark class="hero__mark">НАШЛИ ДЕШЕВЛЕ?</mark></div>
								<div class="hero__check-sub">Мы сделаем цену еще ниже!</div>
							</div>
						</li>
					</ul>

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

	<!-- бегущая строка -->
	<?php
	// пункты бегущей строки (дублируются дважды — для бесшовной прокрутки)
	$marquee = array("Короед", "Комары", "ФЗ-44", "Препараты по стандартам ЕврАзЭС", "Грызуны", "Клопы", "Тараканы", "Плесень", "Запахи", "Мухи", "Муравьи", "Блохи", "Клещи", "Осы и шершни");
	?>
	<div class="marquee">
		<div class="marquee__track">
			<?php for ($i = 0; $i < 2; $i++): foreach ($marquee as $m): ?>
			<span class="marquee__item"><?= h($m) ?></span>
			<span class="marquee__sep" aria-hidden="true">&#8855;</span>
			<?php endforeach; endfor; ?>
		</div>
	</div>

	<!-- цены: карточки с данными донора, наш визуал -->
	<section class="section section--alt" id="prices">
		<div class="container">
			<h2 class="section__title">Цены на наши услуги</h2>
			<div class="price__cards">

				<!-- обработка участков -->
				<div class="price-card">
					<div class="price-card__img" style="background-image:url(/source/img/our_services/1.webp)"></div>
					<div class="price-card__body">
						<div class="price-card__title">ОБРАБОТКА УЧАСТКОВ<span>НАСЕКОМЫЕ</span></div>
						<ul class="price-card__list">
							<li>ДО 10 СОТОК - 7000Р</li>
							<li>10 - 15 СОТОК - ОТ 9000Р (900Р/С)</li>
							<li>15- 30 СОТОК - ОТ 10500Р (700Р/С)</li>
							<li>30-40 СОТОК - ОТ 15000Р (500Р/С)</li>
							<li>40-60 СОТОК - ОТ 18000Р (450Р/С)</li>
							<li>&gt;70 СОТОК, 1ГА И БОЛЕЕ - ОТ 20000Р</li>
						</ul>
						<div class="price-card__from">ОТ 200₽/СОТ</div>
						<div class="price-card__actions">
							<a href="#" class="btn btn--primary btn--sm">Подробнее</a>
							<a href="#" class="btn btn--outline btn--sm js-order-open">Заказать</a>
						</div>
					</div>
				</div>

				<!-- обработка помещений -->
				<div class="price-card">
					<div class="price-card__img" style="background-image:url(/source/img/our_services/2.webp)"></div>
					<div class="price-card__body">
						<div class="price-card__title">ОБРАБОТКА ПОМЕЩЕНИЙ<span>НАСЕКОМЫЕ</span></div>
						<ul class="price-card__list">
							<li>10-30 КВ.М. - 2300Р</li>
							<li>30-50 КВ.М. - 2900Р</li>
							<li>&gt; 50 КВ.М. - ОТ 3500Р</li>
							<li>&gt;100 КВ.М. - ОТ 6500Р</li>
							<li>&gt; 300 КВ.М. - ОТ 14700Р</li>
						</ul>
						<div class="price-card__from">ОТ 2300 ₽</div>
						<div class="price-card__actions">
							<a href="#" class="btn btn--primary btn--sm">Подробнее</a>
							<a href="#" class="btn btn--outline btn--sm js-order-open">Заказать</a>
						</div>
					</div>
				</div>

				<!-- дезинфекция: плесень -->
				<div class="price-card">
					<div class="price-card__img" style="background-image:url(/source/img/our_services/3.webp)"></div>
					<div class="price-card__body">
						<div class="price-card__title">ДЕЗИНФЕКЦИЯ<span>ПЛЕСЕНЬ</span></div>
						<ul class="price-card__list">
							<li>МЕХАНИЧЕСКАЯ ЧИСТКА - 500Р КВ.М.</li>
							<li>ХИМИЧЕСКАЯ ЧИСТКА ОТ - 1500Р КВ.М.</li>
							<li>ЗАЩИТНАЯ ПЛЕНКА - 1000Р КВ.М.</li>
							<li>ДЕМОНТАЖ, ГАЗАЦИЯ - ПО ЗАПРОСУ</li>
						</ul>
						<div class="price-card__from">ОТ 1500 ₽/М²</div>
						<div class="price-card__actions">
							<a href="#" class="btn btn--primary btn--sm">Подробнее</a>
							<a href="#" class="btn btn--outline btn--sm js-order-open">Заказать</a>
						</div>
					</div>
				</div>

				<!-- уничтожение грызунов -->
				<div class="price-card">
					<div class="price-card__img" style="background-image:url(/source/img/our_services/4.webp)"></div>
					<div class="price-card__body">
						<div class="price-card__title">УНИЧТОЖЕНИЕ<span>КРЫС, МЫШЕЙ, КРОТОВ</span></div>
						<ul class="price-card__list">
							<li>ДО 50 КВ.М. - 6000Р</li>
							<li>ДО 100 КВ.М. - 12000Р</li>
							<li>&gt;100 КВ.М. - ПО ЗАПРОСУ</li>
							<li>ДО 15 СОТОК - ОТ 9000Р</li>
							<li>&gt;15 СОТОК - ПО ЗАПРОСУ</li>
						</ul>
						<div class="price-card__from">ОТ 6000₽, 600₽/СОТ</div>
						<div class="price-card__actions">
							<a href="#" class="btn btn--primary btn--sm">Подробнее</a>
							<a href="#" class="btn btn--outline btn--sm js-order-open">Заказать</a>
						</div>
					</div>
				</div>

				<!-- сушка помещений -->
				<div class="price-card">
					<div class="price-card__img" style="background-image:url(/source/img/our_services/5.webp)"></div>
					<div class="price-card__body">
						<div class="price-card__title">СУШКА<span>ПОМЕЩЕНИЙ</span></div>
						<ul class="price-card__list">
							<li>СЛИВ, ОТКАЧКА ВОДЫ - 2500Р ЗА М³</li>
							<li>ДЕМОНТАЖ - ПО ЗАПРОСУ</li>
							<li>СУШКА - 800Р ЗА КВ.М.</li>
							<li>ДЕЗИНФЕКЦИЯ - ОТ 1500Р КВ.М.</li>
						</ul>
						<div class="price-card__from">ОТ 5500 ₽/СУТ</div>
						<div class="price-card__actions">
							<a href="#" class="btn btn--primary btn--sm">Подробнее</a>
							<a href="#" class="btn btn--outline btn--sm js-order-open">Заказать</a>
						</div>
					</div>
				</div>

				<!-- дезинфекция: запах -->
				<div class="price-card">
					<div class="price-card__img" style="background-image:url(/source/img/our_services/6.webp)"></div>
					<div class="price-card__body">
						<div class="price-card__title">ДЕЗИНФЕКЦИЯ<span>ЗАПАХ</span></div>
						<ul class="price-card__list">
							<li>10-30 К.ВМ. - 3500Р</li>
							<li>40-60 КВ.М.- ОТ 6000Р</li>
							<li>70-90 КВ.М. - ОТ 9000Р</li>
							<li>100 КВ.М. - 150 КВ.М. - ОТ 15000Р</li>
							<li>ДРУГОЕ - ПО ЗАПРОСУ</li>
						</ul>
						<div class="price-card__from">ОТ 2300 ₽</div>
						<div class="price-card__actions">
							<a href="#" class="btn btn--primary btn--sm">Подробнее</a>
							<a href="#" class="btn btn--outline btn--sm js-order-open">Заказать</a>
						</div>
					</div>
				</div>

				<!-- уничтожение борщевика -->
				<div class="price-card">
					<div class="price-card__img" style="background-image:url(/source/img/our_services/7.webp)"></div>
					<div class="price-card__body">
						<div class="price-card__title">УНИЧТОЖЕНИЕ<span>БОРЩЕВИКА</span></div>
						<ul class="price-card__list">
							<li>ДО 10 СОТОК - 10000Р</li>
							<li>10 - 15 СОТОК - ОТ 10000Р (1000Р/С)</li>
							<li>15-20 СОТОК - ОТ 14000Р (930Р/С)</li>
							<li>20-30 СОТОК - ОТ 17000Р (850Р/С)</li>
							<li>40-60 СОТОК - ОТ 25000Р (625Р/С)</li>
							<li>&gt;70 СОТОК, 1ГА И БОЛЕЕ - ОТ 30000Р</li>
						</ul>
						<div class="price-card__from">ОТ 700 ₽/СОТ.</div>
						<div class="price-card__actions">
							<a href="#" class="btn btn--primary btn--sm">Подробнее</a>
							<a href="#" class="btn btn--outline btn--sm js-order-open">Заказать</a>
						</div>
					</div>
				</div>
			</div>

			<!-- клининг: доп. блок цен (данные донора-клинингового сайта, наш визуал) -->
			<div class="clean-prices-wrap">
				<h3 class="clean-prices__title">Цены на клининг</h3>
				<div class="clean-prices">

					<!-- высокая карточка слева -->
					<div class="clean-price-card clean-price-card--tall">
						<div class="clean-price-card__row clean-price-card__row--head">
							<span class="clean-price-card__name">Уборка квартир</span>
							<span class="clean-price-card__value">от 80 руб./м2</span>
						</div>
						<div class="clean-price-card__row">
							<span class="clean-price-card__name">После ремонта</span>
							<span class="clean-price-card__value">от 180 руб./м2</span>
						</div>
						<div class="clean-price-card__row">
							<span class="clean-price-card__name">Поддерживающая</span>
							<span class="clean-price-card__value">от 80 руб./м2</span>
						</div>
						<div class="clean-price-card__row">
							<span class="clean-price-card__name">Генеральная</span>
							<span class="clean-price-card__value">от 140 руб./м2</span>
						</div>
						<div class="clean-price-card__row">
							<span class="clean-price-card__name">После потопа</span>
							<span class="clean-price-card__value">от 350 руб./м2</span>
						</div>
						<div class="clean-price-card__row">
							<span class="clean-price-card__name">После пожара</span>
							<span class="clean-price-card__value">от 400 руб./м2</span>
						</div>
						<div class="clean-price-card__row">
							<span class="clean-price-card__name">Запущенных квартир</span>
							<span class="clean-price-card__value">от 150 руб./м2</span>
						</div>
						<div class="clean-price-card__row">
							<span class="clean-price-card__name">Влажная</span>
							<span class="clean-price-card__value">от 80 руб./м2</span>
						</div>
						<div class="clean-price-card__row">
							<span class="clean-price-card__name">Комплексная</span>
							<span class="clean-price-card__value">от 80 руб./м2</span>
						</div>
						<div class="clean-price-card__row">
							<span class="clean-price-card__name">Элитной квартиры</span>
							<span class="clean-price-card__value">от 200 руб./м2</span>
						</div>
						<div class="clean-price-card__row">
							<span class="clean-price-card__name">Однокомнатной</span>
							<span class="clean-price-card__value">от 80 руб./м2</span>
						</div>
						<div class="clean-price-card__row">
							<span class="clean-price-card__name">Двухкомнатной</span>
							<span class="clean-price-card__value">от 80 руб./м2</span>
						</div>
						<div class="clean-price-card__row">
							<span class="clean-price-card__name">Трёхкомнатной</span>
							<span class="clean-price-card__value">от 80 руб./м2</span>
						</div>
					</div>

					<!-- правая колонка: две карточки поменьше, суммарно равны левой по высоте -->
					<div class="clean-prices__col">

						<div class="clean-price-card">
							<div class="clean-price-card__row clean-price-card__row--head">
								<span class="clean-price-card__name">Уборка домов</span>
								<span class="clean-price-card__value">от 80 руб./м2</span>
							</div>
							<div class="clean-price-card__row">
								<span class="clean-price-card__name">После ремонта</span>
								<span class="clean-price-card__value">от 180 руб./м2</span>
							</div>
							<div class="clean-price-card__row">
								<span class="clean-price-card__name">Коттеджа</span>
								<span class="clean-price-card__value">от 80 руб./м2</span>
							</div>
							<div class="clean-price-card__row">
								<span class="clean-price-card__name">Таунхауса</span>
								<span class="clean-price-card__value">от 80 руб./м2</span>
							</div>
							<div class="clean-price-card__row">
								<span class="clean-price-card__name">Дачи</span>
								<span class="clean-price-card__value">от 80 руб./м2</span>
							</div>
						</div>

						<div class="clean-price-card">
							<div class="clean-price-card__row clean-price-card__row--head">
								<span class="clean-price-card__name">Уборка офисов</span>
								<span class="clean-price-card__value">от 30 руб./м2</span>
							</div>
							<div class="clean-price-card__row">
								<span class="clean-price-card__name">Генеральная</span>
								<span class="clean-price-card__value">от 90 руб./м2</span>
							</div>
							<div class="clean-price-card__row">
								<span class="clean-price-card__name">После ремонта</span>
								<span class="clean-price-card__value">от 140 руб./м2</span>
							</div>
							<div class="clean-price-card__row">
								<span class="clean-price-card__name">Ежедневная</span>
								<span class="clean-price-card__value">от 30 руб./м2</span>
							</div>
							<div class="clean-price-card__row">
								<span class="clean-price-card__name">Разовая</span>
								<span class="clean-price-card__value">от 30 руб./м2</span>
							</div>
							<div class="clean-price-card__row">
								<span class="clean-price-card__name">Утренняя</span>
								<span class="clean-price-card__value">от 30 руб./м2</span>
							</div>
							<div class="clean-price-card__row">
								<span class="clean-price-card__name">Вечерняя</span>
								<span class="clean-price-card__value">от 30 руб./м2</span>
							</div>
						</div>

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

	<!-- бегущая строка: сертификаты (navy, как первая) -->
	<?php
	// одна фраза по кругу; повторяется для ширины, ×2 — для бесшовной прокрутки
	$certMarquee = array_fill(0, 8, "ТОЛЬКО СЕРТИФИЦИРОВАННЫЕ ПРЕПАРАТЫ");
	?>
	<div class="marquee">
		<div class="marquee__track">
			<?php for ($i = 0; $i < 2; $i++): foreach ($certMarquee as $m): ?>
			<span class="marquee__item"><?= h($m) ?></span>
			<span class="marquee__sep" aria-hidden="true">&#8855;</span>
			<?php endforeach; endfor; ?>
		</div>
	</div>

	<!-- сертификаты: карусель сканов. клоны по краям — для бесшовной прокрутки без autoplay -->
	<section class="section" id="certificates">
		<div class="container">
			<h2 class="section__title">Наши сертификаты</h2>
			<div class="certs__carousel">
				<div class="certs__track js-certs-track">

					<!-- клоны последних 4 карточек — стоят перед реальными для прокрутки влево -->
					<?php foreach (array(3, 4, 5, 6) as $i): ?>
					<div class="cert-card" aria-hidden="true" tabindex="-1">
						<img class="cert-card__img" src="/source/img/certificate/cert_<?= $i ?>.webp" alt="" loading="lazy">
					</div>
					<?php endforeach; ?>

					<!-- реальные сертификаты, кликабельны — открывают лайтбокс -->
					<?php for ($i = 1; $i <= 6; $i++): ?>
					<button type="button" class="cert-card js-cert-open" data-cert="<?= $i ?>" aria-label="Открыть сертификат №<?= $i ?> увеличенно">
						<img class="cert-card__img" src="/source/img/certificate/cert_<?= $i ?>.webp" alt="Сертификат №<?= $i ?>" loading="lazy">
						<span class="cert-card__zoom"><svg aria-hidden="true"><use href="#i-zoom"></use></svg></span>
					</button>
					<?php endfor; ?>

					<!-- клоны первых 4 карточек — стоят после реальных для прокрутки вправо -->
					<?php foreach (array(1, 2, 3, 4) as $i): ?>
					<div class="cert-card" aria-hidden="true" tabindex="-1">
						<img class="cert-card__img" src="/source/img/certificate/cert_<?= $i ?>.webp" alt="" loading="lazy">
					</div>
					<?php endforeach; ?>

				</div>
				<div class="certs__nav">
					<button type="button" class="certs__btn" data-dir="prev" aria-label="Предыдущий сертификат">‹</button>
					<button type="button" class="certs__btn" data-dir="next" aria-label="Следующий сертификат">›</button>
				</div>
			</div>
		</div>
	</section>

	<!-- назначить специалиста / скидка ветеранам · фон с персонажем сквозной -->
	<section class="section section--specialist">
		<div class="container">
			<div class="veterans">

				<!-- левая зона: текст скидки + крупная «15%» -->
				<div class="veterans__text">
					<p class="veterans__lead">Ветеранам боевых действий, героям, ликвидаторам радиационных катастроф, ветеранам труда действует скидка</p>
					<!-- SVG-скидка в фирменном градиенте (без красного донора) -->
					<svg class="veterans__discount" viewBox="0 0 240 132" role="img" aria-label="Скидка 15 процентов">
						<defs>
							<linearGradient id="discount-grad" x1="0" y1="0" x2="1" y2="1">
								<stop offset="0" stop-color="#81b76a"/>
								<stop offset="0.55" stop-color="#5b918b"/>
								<stop offset="1" stop-color="#4880b0"/>
							</linearGradient>
						</defs>
						<text x="120" y="96" text-anchor="middle" font-family="Roboto, Arial, sans-serif" font-size="98" font-weight="800" fill="url(#discount-grad)">15%</text>
						<text x="120" y="126" text-anchor="middle" font-family="Roboto, Arial, sans-serif" font-size="22" font-weight="700" letter-spacing="8" fill="#5b918b">СКИДКА</text>
					</svg>
				</div>

				<!-- правая зона: форма (белая карточка, как у донора) -->
				<form class="veterans__form js-form" action="/source/php/order.php" method="post">
					<input type="hidden" name="source" value="Назначить специалиста">
					<input type="text" name="website" class="hp" tabindex="-1" autocomplete="off" aria-hidden="true">
					<div class="veterans__form-title">Назначить специалиста</div>
					<div class="veterans__form-sub">Заполните поля для связи с вами</div>
					<!-- мессенджеры цветными кружками (наш набор: tg / max / wa) -->
					<div class="veterans__messengers">
						<a class="msg-circle msg-circle--tg" href="<?= h($msg["telegram"]) ?>" target="_blank" rel="noopener" aria-label="Telegram"><svg><use href="#i-tg"></use></svg></a>
						<a class="msg-circle msg-circle--max" href="<?= h($msg["max"]) ?>" target="_blank" rel="noopener" aria-label="Max"><svg><use href="#i-max"></use></svg></a>
						<a class="msg-circle msg-circle--wa" href="<?= h($msg["whatsapp"]) ?>" target="_blank" rel="noopener" aria-label="WhatsApp"><svg><use href="#i-wa"></use></svg></a>
					</div>
					<input type="text" name="name" placeholder="Ваше имя" required>
					<input type="tel" name="phone" placeholder="+7 (___) ___-__-__" required>
					<button type="submit" class="btn btn--accent btn--block">Получить консультацию</button>
					<div class="form-status" role="status"></div>
					<div class="hero__form-note">Отправляя свои данные, вы соглашаетесь с <a href="/politika/">политикой конфиденциальности</a></div>
				</form>

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

	<!-- зона обслуживания: карта покрытия + текст с CTA -->
	<section class="section" id="coverage">
		<div class="container">
			<div class="coverage">
				<!-- карта районов выезда -->
				<img class="coverage__map" src="/source/img/coverage/map.webp" alt="Карта выезда: Москва и Московская область" width="857" height="896" loading="lazy">
				<!-- текст + кнопка (открывает поп-ап заявки) -->
				<div class="coverage__body">
					<p class="coverage__lead">Наши специалисты выезжают во все районы Москвы и Московской области:</p>
					<p class="coverage__note">обработка помещений — до 80 км от МКАД<br>обработка участков — до 80 км от МКАД</p>
					<a href="#" class="btn btn--primary js-order-open">Оставить заявку</a>
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

	<?php require __DIR__ . "/source/include/footer.html"; ?>

	<!-- поп-ап заявки (заменил секцию #order; открывается со всех кнопок «Заказать»/«Оставить заявку») -->
	<div class="modal" id="order-modal" aria-hidden="true">
		<div class="modal__overlay" data-modal-close></div>
		<div class="modal__dialog" role="dialog" aria-modal="true" aria-labelledby="order-modal-title">
			<button type="button" class="modal__close" data-modal-close aria-label="Закрыть">&times;</button>
			<h2 class="modal__title" id="order-modal-title">Оставить заявку на обработку</h2>
			<form class="hero__form js-form" action="/source/php/order.php" method="post">
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
	</div>

	<!-- лайтбокс сертификатов: увеличенный просмотр по клику на карточку -->
	<div class="lightbox" id="cert-lightbox" aria-hidden="true">
		<div class="lightbox__overlay" data-modal-close></div>
		<div class="lightbox__dialog">
			<button type="button" class="lightbox__close" data-modal-close aria-label="Закрыть">&times;</button>
			<img class="lightbox__img" src="" alt="">
		</div>
	</div>

	<!-- плавающая кнопка «Заказать звонок»: открывает поп-ап заявки -->
	<button type="button" class="fab fab--call js-order-open" aria-label="Заказать звонок">
		<svg aria-hidden="true"><use href="#i-phone"></use></svg>
	</button>

	<!-- кнопка «наверх»: появляется после первого экрана (логика в main.js) -->
	<button type="button" class="fab fab--top" aria-label="Наверх">
		<svg aria-hidden="true" viewBox="0 0 24 24"><path d="M12 19V6M6 12l6-6 6 6"/></svg>
	</button>

	<script src="/source/js/main.js"></script>
</body>
</html>