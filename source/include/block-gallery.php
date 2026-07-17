<?php
// Объекты санобработки: слайдер изображений. Данные — data/service-page.json → gallery.
// Логика как у слайдера услуг: клоны по краям, только целые карточки, без подглядывания и точек.
$gal = isset($servicePage["gallery"]) ? $servicePage["gallery"] : array();
if (!empty($gal["visible"]) && !empty($gal["images"])):
	$gTotal  = count($gal["images"]);
	$gClone  = min(3, $gTotal);
	$gBefore = array_slice($gal["images"], max(0, $gTotal - $gClone));
	$gAfter  = array_slice($gal["images"], 0, $gClone);
?>
<section class="section section--alt" id="gallery">
	<div class="container">
		<h2 class="section__title"><?= h($gal["title"]) ?></h2>
		<div class="gal__carousel">
			<div class="gal__track js-gal-track" data-real="<?= $gTotal ?>" data-clone="<?= $gClone ?>">

				<?php foreach ($gBefore as $img): ?>
				<div class="gal__item" aria-hidden="true"><img src="<?= h($img["src"]) ?>" alt="" width="1200" height="800" loading="lazy"></div>
				<?php endforeach; ?>

				<?php foreach ($gal["images"] as $img): ?>
				<div class="gal__item"><img src="<?= h($img["src"]) ?>" alt="<?= h($img["alt"]) ?>" width="1200" height="800" loading="lazy"></div>
				<?php endforeach; ?>

				<?php foreach ($gAfter as $img): ?>
				<div class="gal__item" aria-hidden="true"><img src="<?= h($img["src"]) ?>" alt="" width="1200" height="800" loading="lazy"></div>
				<?php endforeach; ?>

			</div>
			<div class="gal__nav">
				<button type="button" class="gal__btn" data-dir="prev" aria-label="Предыдущее фото">&lsaquo;</button>
				<button type="button" class="gal__btn" data-dir="next" aria-label="Следующее фото">&rsaquo;</button>
			</div>
		</div>
	</div>
</section>
<?php endif; ?>
