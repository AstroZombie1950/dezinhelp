<?php
// Прайс. Ждёт $pricesData (visible + groups[visible, title, items]).
// $pricesLayout: "cols" — две колонки (главная), "full" — на всю ширину (страница услуги).
// $pricesButton — текст кнопки под таблицами; пусто = формы нет.
$pGroups = array();
foreach ($pricesData["groups"] as $g) {
	if (isset($g["visible"]) && !$g["visible"]) { continue; }   // подраздел скрыт через админку
	if (empty($g["items"])) { continue; }
	$pGroups[] = $g;
}
if (!empty($pricesData["visible"]) && $pGroups):
	$pFull = (isset($pricesLayout) && $pricesLayout === "full");
	$pCols = $pFull ? array($pGroups) : prices_split($pGroups);
?>
<div class="price-table-wrap">
	<?php if (!empty($pricesData["title"])): ?>
	<h3 class="price-table__title"><?= h($pricesData["title"]) ?></h3>
	<?php endif; ?>

	<div class="price-table<?= $pFull ? " price-table--full" : "" ?>">
		<?php foreach ($pCols as $col): ?>
		<?php if (!$col) { continue; } ?>
		<div class="price-table__col">
			<?php foreach ($col as $group): ?>
			<div class="price-table__card">
				<div class="price-table__row price-table__row--head">
					<span class="price-table__name"><?= h($group["title"]) ?></span>
				</div>
				<?php foreach ($group["items"] as $item): ?>
				<div class="price-table__row">
					<span class="price-table__name"><?= h($item["name"]) ?></span>
					<span class="price-table__value"><?= h($item["price"]) ?></span>
				</div>
				<?php endforeach; ?>
			</div>
			<?php endforeach; ?>
		</div>
		<?php endforeach; ?>
	</div>

	<?php // форма под таблицами — одна на всю секцию, как у донора ?>
	<?php if (!empty($pricesButton)): ?>
	<form class="price-form js-form" action="/source/php/order.php" method="post">
		<input type="hidden" name="source" value="Цены — заказ обработки">
		<input type="text" name="website" class="hp" tabindex="-1" autocomplete="off" aria-hidden="true">
		<input type="tel" name="phone" placeholder="+7 (___) ___-__-__" autocomplete="tel" required>
		<button type="submit" class="btn btn--primary"><?= h($pricesButton) ?></button>
		<div class="form-status" role="status"></div>
	</form>
	<?php endif; ?>
</div>
<?php endif; ?>
