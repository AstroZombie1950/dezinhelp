<?php
// Редактор подразделов прайса: общий для страницы услуги и прайса главной.
// Ждёт $pgGroups — массив groups[{visible, title, items[{name, price}]}].
// Форма-родитель должна иметь id="prices-form", тумблер — id="prices-visible".
?>
<div id="prices-groups">
	<?php foreach ((array) $pgGroups as $g): ?>
	<div class="pgroup js-pgroup">
		<div class="pgroup__head">
			<input type="text" class="pgroup__title" value="<?= h($g["title"]) ?>" placeholder="Название подраздела">
			<label class="switch switch--small" title="Показывать подраздел">
				<input type="checkbox" class="pgroup__visible" <?= !isset($g["visible"]) || $g["visible"] ? "checked" : "" ?>>
				<span>виден</span>
			</label>
			<span class="pgroup__tools">
				<button type="button" class="icon-btn js-pg-move" data-dir="up" title="Выше">↑</button>
				<button type="button" class="icon-btn js-pg-move" data-dir="down" title="Ниже">↓</button>
				<button type="button" class="icon-btn icon-btn--danger js-pg-del" title="Удалить подраздел">✕</button>
			</span>
		</div>
		<div class="pgroup__items">
			<?php foreach ((array) (isset($g["items"]) ? $g["items"] : array()) as $it): ?>
			<div class="prow js-prow">
				<input type="text" class="prow__name" value="<?= h($it["name"]) ?>" placeholder="Услуга">
				<input type="text" class="prow__price" value="<?= h($it["price"]) ?>" placeholder="от 2300 ₽">
				<button type="button" class="icon-btn js-pr-move" data-dir="up" title="Выше">↑</button>
				<button type="button" class="icon-btn js-pr-move" data-dir="down" title="Ниже">↓</button>
				<button type="button" class="icon-btn icon-btn--danger js-pr-del" title="Удалить строку">✕</button>
			</div>
			<?php endforeach; ?>
		</div>
		<button type="button" class="btn btn--small js-pr-add">+ Строка</button>
	</div>
	<?php endforeach; ?>
</div>

<button type="button" class="btn" id="pg-add">+ Добавить подраздел</button>

<!-- шаблоны для новых подразделов и строк — клонируются JS-ом -->
<template id="tpl-pgroup">
	<div class="pgroup js-pgroup">
		<div class="pgroup__head">
			<input type="text" class="pgroup__title" value="" placeholder="Название подраздела">
			<label class="switch switch--small" title="Показывать подраздел">
				<input type="checkbox" class="pgroup__visible" checked>
				<span>виден</span>
			</label>
			<span class="pgroup__tools">
				<button type="button" class="icon-btn js-pg-move" data-dir="up" title="Выше">↑</button>
				<button type="button" class="icon-btn js-pg-move" data-dir="down" title="Ниже">↓</button>
				<button type="button" class="icon-btn icon-btn--danger js-pg-del" title="Удалить подраздел">✕</button>
			</span>
		</div>
		<div class="pgroup__items"></div>
		<button type="button" class="btn btn--small js-pr-add">+ Строка</button>
	</div>
</template>
<template id="tpl-prow">
	<div class="prow js-prow">
		<input type="text" class="prow__name" value="" placeholder="Услуга">
		<input type="text" class="prow__price" value="" placeholder="от 2300 ₽">
		<button type="button" class="icon-btn js-pr-move" data-dir="up" title="Выше">↑</button>
		<button type="button" class="icon-btn js-pr-move" data-dir="down" title="Ниже">↓</button>
		<button type="button" class="icon-btn icon-btn--danger js-pr-del" title="Удалить строку">✕</button>
	</div>
</template>