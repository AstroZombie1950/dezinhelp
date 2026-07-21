<?php
// Примеры работ: слайдер карточек «до/после». Данные — data/works.json.
// Механика та же, что у слайдера услуг и галереи: клоны по краям, только целые
// карточки, без подглядывания и точек. На десктопе видно 2, ниже — 1.
$works = data_load("works");
$wItems = array();
foreach ((array) (isset($works["items"]) ? $works["items"] : array()) as $w) {
	// без пары фото карточку не показываем — сравнивать нечего
	if (empty($w["before"]) || empty($w["after"])) { continue; }
	$wItems[] = $w;
}
if (!empty($works["visible"]) && $wItems):
	$wTotal  = count($wItems);
	$wClone  = min(2, $wTotal);                             // клонов с каждой стороны — по числу видимых на десктопе
	$wBefore = array_slice($wItems, max(0, $wTotal - $wClone)); // последние — перед реальными, для прокрутки влево
	$wAfter  = array_slice($wItems, 0, $wClone);               // первые — после реальных, для прокрутки вправо
?>
<section class="section section--alt" id="works">
	<div class="container">
		<h2 class="section__title"><?= h(!empty($works["title"]) ? $works["title"] : "Примеры наших работ") ?></h2>

		<div class="works__carousel">
			<div class="works__track js-works-track" data-real="<?= $wTotal ?>" data-clone="<?= $wClone ?>">

				<?php foreach ($wBefore as $work): ?>
				<?php $workClone = true; require __DIR__ . "/work-card.php"; ?>
				<?php endforeach; ?>

				<?php foreach ($wItems as $work): ?>
				<?php $workClone = false; require __DIR__ . "/work-card.php"; ?>
				<?php endforeach; ?>

				<?php foreach ($wAfter as $work): ?>
				<?php $workClone = true; require __DIR__ . "/work-card.php"; ?>
				<?php endforeach; ?>

			</div>
			<div class="works__nav">
				<button type="button" class="works__btn" data-dir="prev" aria-label="Предыдущий пример">&lsaquo;</button>
				<button type="button" class="works__btn" data-dir="next" aria-label="Следующий пример">&rsaquo;</button>
			</div>
		</div>
	</div>
</section>
<?php endif; ?>
