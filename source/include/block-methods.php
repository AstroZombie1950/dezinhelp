<?php
// Методы обработки, вариант главной: сетка карточек. Данные — data/methods.json → home.
$methods = data_load("methods");
$m = isset($methods["home"]) ? $methods["home"] : array();
if (!empty($m["visible"]) && !empty($m["cards"])):
?>
<section class="section" id="methods">
	<div class="container">
		<h2 class="section__title"><?= h($m["title"]) ?></h2>
		<div class="methods">
			<?php foreach ($m["cards"] as $card): ?>
			<div class="method-card">
				<div class="method-card__title"><?= h($card["title"]) ?></div>
				<div class="method-card__text"><?= h($card["text"]) ?></div>
			</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
<?php endif; ?>
