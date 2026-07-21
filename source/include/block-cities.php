<?php
// Населённые пункты выезда: заголовок + список городов в колонки.
// Данные — data/geo-cities.json. Город из highlight подсвечивается акцентом.
$geo = data_load("geo-cities");
if (!empty($geo["visible"]) && !empty($geo["cities"])):
	$geoHighlight = isset($geo["highlight"]) ? $geo["highlight"] : "";
?>
<section class="section" id="cities">
	<div class="container">
		<h2 class="section__title"><?= h($geo["title"]) ?></h2>
		<!-- список в колонки: раскладку держит CSS (column-count) -->
		<ul class="cities__list">
			<?php foreach ($geo["cities"] as $city): ?>
			<li class="cities__city<?= $city === $geoHighlight ? " cities__city--home" : "" ?>"><?= h($city) ?></li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
<?php endif; ?>