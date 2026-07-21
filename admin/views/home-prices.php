<?php
// Прайс главной страницы: data/prices.json. Подключается из admin/index.php при page=prices.
// Ждёт $homePrices. Переиспользует форму цен из редактора услуги — id и JS общие.
?>
<div class="page-head">
	<h1 class="page-title">Прайс главной</h1>
	<a class="btn" href="/#prices" target="_blank" rel="noopener">Открыть на сайте ↗</a>
</div>
<p class="page-hint">Таблица цен на главной странице — та, что без картинок, под слайдером услуг. Колонки на сайте раскладываются сами.</p>

<form class="card js-tab-form" data-tab="prices" data-action="home_prices_save" id="prices-form">
	<label class="switch">
		<input type="checkbox" id="prices-visible" <?= !empty($homePrices["visible"]) ? "checked" : "" ?>>
		<span>Показывать блок на главной</span>
	</label>

	<label class="field">
		<span class="field__label">Заголовок блока</span>
		<input type="text" name="title" value="<?= h(isset($homePrices["title"]) ? $homePrices["title"] : "") ?>">
	</label>

	<?php $pgGroups = isset($homePrices["groups"]) ? $homePrices["groups"] : array(); ?>
	<?php require __DIR__ . "/prices-groups.php"; ?>

	<div class="tab__actions"><button type="submit" class="btn btn--primary">Сохранить</button></div>
</form>

<?php // пустой слаг: общий обработчик сохранения его требует, экшен главной — игнорирует ?>
<input type="hidden" id="edit-slug" value="">