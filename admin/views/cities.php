<?php
// Реестр городов: поддомены и формы названия. Ждёт $citiesData (data/cities.json).
$cRows = isset($citiesData["cities"]) ? $citiesData["cities"] : array();
if (!$cRows) {
	$cRows = array(array("slug" => "", "name" => "", "in" => "", "genitive" => "", "to" => "",
		"prepositional" => "", "main" => true, "enabled" => true, "index" => true));
}
$cVal = function ($row, $key) { return isset($row[$key]) ? $row[$key] : ""; };
?>
<div class="page-head">
	<h1 class="page-title">Города</h1>
</div>
<p class="page-hint">Один город — один поддомен вида <b>subdomen.site.ru</b>. Формы названия подставляются в заголовки, тексты, FAQ, подписи картинок и формы вместо <b>{CITY}</b>, <b>{CITY_IN}</b>, <b>{CITY_GENITIVE}</b>, <b>{CITY_TO}</b>. Поддомена, которого нет в этом списке, на сайте не существует — он отдаёт 404.</p>

<form class="card js-tab-form" data-tab="cities" data-action="cities_save" id="cities-form">

	<label class="switch switch--row">
		<input type="checkbox" id="cities-visible" <?= !empty($citiesData["visible"]) ? "checked" : "" ?>>
		<span>Показывать список городов на главной <em>он же переключатель города</em></span>
	</label>

	<label class="field">
		<span class="field__label">Заголовок списка</span>
		<input type="text" id="cities-title" value="<?= h(isset($citiesData["title"]) ? $citiesData["title"] : "") ?>">
	</label>

	<table class="table table--cities">
		<thead>
			<tr>
				<th>Город</th>
				<th>Поддомен</th>
				<th>Где</th>
				<th>Чего</th>
				<th>Куда</th>
				<th>Где (без предлога)</th>
				<th class="table__c">Работает</th>
				<th class="table__c">В поиске</th>
				<th></th>
			</tr>
		</thead>
		<tbody id="cities-list">
			<?php foreach ($cRows as $c): $isMain = !empty($c["main"]); ?>
			<tr class="js-city-row<?= $isMain ? " is-main" : "" ?>" data-main="<?= $isMain ? "1" : "" ?>">
				<td>
					<input type="text" data-f="name" value="<?= h($cVal($c, "name")) ?>" placeholder="Балашиха">
					<?php if ($isMain): ?><div class="table__slug">основной домен</div><?php endif; ?>
				</td>
				<td><input type="text" data-f="slug" value="<?= h($cVal($c, "slug")) ?>" placeholder="balashikha"></td>
				<td><input type="text" data-f="in" value="<?= h($cVal($c, "in")) ?>" placeholder="в Балашихе"></td>
				<td><input type="text" data-f="genitive" value="<?= h($cVal($c, "genitive")) ?>" placeholder="Балашихи"></td>
				<td><input type="text" data-f="to" value="<?= h($cVal($c, "to")) ?>" placeholder="в Балашиху"></td>
				<td><input type="text" data-f="prepositional" value="<?= h($cVal($c, "prepositional")) ?>" placeholder="Балашихе"></td>
				<td class="table__c"><input type="checkbox" data-f="enabled" <?= !empty($c["enabled"]) ? "checked" : "" ?><?= $isMain ? " disabled title=\"Основной город выключить нельзя\"" : "" ?>></td>
				<td class="table__c"><input type="checkbox" data-f="index" <?= !empty($c["index"]) ? "checked" : "" ?>></td>
				<td class="table__actions"><?php if (!$isMain): ?><button type="button" class="icon-btn icon-btn--danger js-city-del" title="Удалить город">✕</button><?php endif; ?></td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>

	<p class="field__hint">«Работает» — город открывается на своём поддомене; выключите, и поддомен станет отдавать 404. «В поиске» — страницы города видны поисковикам; выключено — на них стоит noindex. Город, помеченный как основной, живёт на главном домене: его нельзя выключить или удалить.</p>

	<div class="row-actions">
		<button type="button" class="btn btn--small" id="city-add">+ Добавить город</button>
	</div>

	<div class="tab__actions"><button type="submit" class="btn btn--primary">Сохранить</button></div>
</form>

<?php // пустой слаг: общий обработчик сохранения его требует, экшен городов игнорирует ?>
<input type="hidden" id="edit-slug" value="">
