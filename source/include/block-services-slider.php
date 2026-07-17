<?php
// Слайдер услуг. Ждёт $srvData (title + cards) и, необязательно,
// $srvTitle — свой заголовок, $srvExclude — слаг, который выкинуть (страница сама на себя не ссылается).
$srvCards = array();
foreach ($srvData["cards"] as $c) {
	if (!empty($srvExclude) && !empty($c["slug"]) && $c["slug"] === $srvExclude) { continue; }
	$srvCards[] = $c;
}
if (!empty($srvData["visible"]) && $srvCards):
	// клонов с каждой стороны — по максимуму видимых карточек (4 на десктопе)
	$srvTotal  = count($srvCards);
	$srvClone  = min(4, $srvTotal);
	$srvBefore = array_slice($srvCards, max(0, $srvTotal - $srvClone)); // последние — перед реальными, для прокрутки влево
	$srvAfter  = array_slice($srvCards, 0, $srvClone);                  // первые — после реальных, для прокрутки вправо
?>
<h2 class="section__title"><?= h(!empty($srvTitle) ? $srvTitle : $srvData["title"]) ?></h2>
<div class="srv__carousel">
	<div class="srv__track js-srv-track" data-real="<?= $srvTotal ?>" data-clone="<?= $srvClone ?>">

		<?php foreach ($srvBefore as $card): ?>
		<?php $cardAria = true; require __DIR__ . "/service-card.php"; ?>
		<?php endforeach; ?>

		<?php foreach ($srvCards as $card): ?>
		<?php $cardAria = false; require __DIR__ . "/service-card.php"; ?>
		<?php endforeach; ?>

		<?php foreach ($srvAfter as $card): ?>
		<?php $cardAria = true; require __DIR__ . "/service-card.php"; ?>
		<?php endforeach; ?>

	</div>
	<div class="srv__nav">
		<button type="button" class="srv__btn" data-dir="prev" aria-label="Предыдущая услуга">&lsaquo;</button>
		<button type="button" class="srv__btn" data-dir="next" aria-label="Следующая услуга">&rsaquo;</button>
	</div>
</div>
<?php endif; ?>
