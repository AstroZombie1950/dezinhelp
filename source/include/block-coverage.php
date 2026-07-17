<?php
// Зона выезда: карта + текст с CTA. Данные — data/coverage.json.
// Заголовок один и тот же на главной и на страницах услуг.
$cov = data_load("coverage");
if (!empty($cov["visible"])):
?>
<section class="section" id="coverage">
	<div class="container">
		<h2 class="section__title"><?= h($cov["title"]) ?></h2>
		<div class="coverage">
			<!-- карта районов выезда -->
			<img class="coverage__map" src="<?= h($cov["map"]) ?>" alt="<?= h($cov["map_alt"]) ?>" width="775" height="710" loading="lazy">
			<!-- текст + кнопка (открывает поп-ап заявки) -->
			<div class="coverage__body">
				<p class="coverage__lead"><?= h($cov["lead"]) ?></p>
				<p class="coverage__note"><?= $cov["note"] ?></p>
				<a href="#" class="btn btn--primary js-order-open"><?= h($cov["button"]) ?></a>
			</div>
		</div>
	</div>
</section>
<?php endif; ?>
