<?php
// Методы обработки, вариант страницы услуги: две карточки и иллюстрация между ними (как у донора).
// Данные — data/methods.json → service.
$methods = data_load("methods");
$m = isset($methods["service"]) ? $methods["service"] : array();
if (!empty($m["visible"]) && !empty($m["cards"])):
?>
<section class="section" id="methods">
	<div class="container">
		<h2 class="section__title"><?= h($m["title"]) ?></h2>
		<div class="methods-srv">
			<?php foreach ($m["cards"] as $n => $card): ?>
			<div class="methods-srv__card">
				<div class="methods-srv__title"><?= h($card["title"]) ?></div>
				<div class="methods-srv__text"><?= h($card["text"]) ?></div>
			</div>

			<?php // иллюстрация встаёт между первой и второй карточкой; на мобиле уезжает вниз ?>
			<?php if ($n === 0 && !empty($m["image"])): ?>
			<div class="methods-srv__figure">
				<img src="<?= h($m["image"]) ?>" alt="<?= h(isset($m["image_alt"]) ? $m["image_alt"] : "") ?>" width="692" height="960" loading="lazy">
			</div>
			<?php endif; ?>
			<?php endforeach; ?>
		</div>
	</div>
</section>
<?php endif; ?>
