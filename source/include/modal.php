<!-- поп-ап заявки (открывается со всех кнопок «Заказать»/«Оставить заявку») + плавающие кнопки.
     Вынесено из index.php: футер зовёт модалку через .js-order-open, значит она нужна на каждой странице -->
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

<!-- плавающая кнопка «Заказать звонок»: открывает поп-ап заявки -->
<button type="button" class="fab fab--call js-order-open" aria-label="Заказать звонок">
	<svg aria-hidden="true"><use href="#i-phone"></use></svg>
</button>

<!-- кнопка «наверх»: появляется после первого экрана (логика в main.js) -->
<button type="button" class="fab fab--top" aria-label="Наверх">
	<svg aria-hidden="true" viewBox="0 0 24 24"><path d="M12 19V6M6 12l6-6 6 6"/></svg>
</button>
