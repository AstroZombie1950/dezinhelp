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
