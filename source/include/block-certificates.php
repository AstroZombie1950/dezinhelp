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
