<!-- поп-ап заявки (открывается со всех кнопок «Заказать»/«Оставить заявку») + плавающие кнопки.
     Вынесено из index.php: футер зовёт модалку через .js-order-open, значит она нужна на каждой странице -->
<div class="modal" id="order-modal" aria-hidden="true">
	<div class="modal__overlay" data-modal-close></div>
	<div class="modal__dialog" role="dialog" aria-modal="true" aria-labelledby="order-modal-title">
		<button type="button" class="modal__close" data-modal-close aria-label="Закрыть">&times;</button>
		<h2 class="modal__title" id="order-modal-title">Оставить заявку на обработку {CITY_IN}</h2>
		<form class="hero__form js-form" action="/source/php/order.php" method="post">
			<input type="hidden" name="source" value="Заявка на обработку">
			<input type="text" name="website" class="hp" tabindex="-1" autocomplete="off" aria-hidden="true">
			<input type="text" name="name" placeholder="Ваше имя" autocomplete="name" aria-label="Ваше имя" required>
			<input type="tel" name="phone" placeholder="+7 (___) ___-__-__" autocomplete="tel" aria-label="Номер телефона" required>
			<input type="text" name="comment" placeholder="Комментарий (необязательно)" aria-label="Комментарий">
			<button type="submit" class="btn btn--primary btn--block">Отправить заявку</button>
			<div class="form-status" role="status"></div>
			<div class="hero__form-note">Отправляя свои данные, вы соглашаетесь с <a href="/politika/">политикой конфиденциальности</a></div>
		</form>
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

<!-- поп-ап «ваш город»: показывается после определения города (main.js).
     data-slug — открытый сейчас город, data-base — домен для куки на все поддомены -->
<div class="geo-ask" id="geo-ask" hidden data-slug="<?= h($city["slug"]) ?>" data-base="<?= h(city_base_domain()) ?>">
	<button type="button" class="geo-ask__close" data-geo-close aria-label="Закрыть">&times;</button>
	<div class="geo-ask__text">Нужна обработка {CITY_IN}?</div>
	<div class="geo-ask__actions">
		<button type="button" class="btn btn--primary btn--sm js-geo-yes">Да</button>
		<button type="button" class="btn btn--outline btn--sm js-geo-no">Нет</button>
	</div>
</div>

<!-- выбор города: тот же реестр, что и в списке на главной; открывается кнопкой «Нет» -->
<div class="city-picker" id="city-picker" aria-hidden="true">
	<div class="city-picker__overlay" data-picker-close></div>
	<div class="city-picker__dialog" role="dialog" aria-modal="true" aria-labelledby="city-picker-title">
		<button type="button" class="city-picker__close" data-picker-close aria-label="Закрыть">&times;</button>
		<h2 class="city-picker__title" id="city-picker-title">Выберите ваш город</h2>
		<div class="city-picker__note">Покажем цены и контакты для вашего населённого пункта</div>
		<input type="text" class="city-picker__search js-city-search" placeholder="Начните вводить название" aria-label="Поиск города" autocomplete="off">
		<ul class="city-picker__list js-city-list">
			<?php foreach (city_list() as $c): ?>
				<?php if ($c["slug"] === $city["slug"]): ?>
			<li class="city-picker__item" data-name="<?= h($c["name"]) ?>"><span class="city-picker__current" aria-current="page"><?= h($c["name"]) ?></span></li>
				<?php else: ?>
			<li class="city-picker__item" data-name="<?= h($c["name"]) ?>"><a class="city-picker__link js-city-choose" href="<?= h(city_url($c)) ?>" data-slug="<?= h($c["slug"]) ?>"><?= h($c["name"]) ?></a></li>
				<?php endif; ?>
			<?php endforeach; ?>
		</ul>
		<div class="city-picker__empty js-city-empty" hidden>Город не найден</div>
	</div>
</div>
