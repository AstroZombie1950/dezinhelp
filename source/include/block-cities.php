<?php
// Населённые пункты выезда — он же ручной переключатель города.
// Каждый пункт ведёт на свой поддомен, текущий город подсвечен и ссылкой не является.
// Данные — data/cities.json, тот же реестр, по которому резолвится поддомен.
$cityReg   = city_registry();
$cityItems = city_list();
if (!empty($cityReg["visible"]) && $cityItems):
?>
<section class="section" id="cities">
	<div class="container">
		<h2 class="section__title"><?= h(isset($cityReg["title"]) ? $cityReg["title"] : "") ?></h2>
		<!-- список в колонки: раскладку держит CSS (column-count) -->
		<ul class="cities__list">
			<?php foreach ($cityItems as $c): ?>
				<?php if ($c["slug"] === $city["slug"]): ?>
			<li class="cities__city cities__city--home" aria-current="page"><?= h($c["name"]) ?></li>
				<?php else: ?>
			<li class="cities__city"><a class="cities__link" href="<?= h(city_url($c)) ?>" data-slug="<?= h($c["slug"]) ?>"><?= h($c["name"]) ?></a></li>
				<?php endif; ?>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
<?php endif; ?>
