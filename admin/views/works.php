<?php
// Примеры работ: data/works.json. Подключается при page=works.
// Ждёт $works и $services (для привязки к услуге кнопкой «Такая же проблема»).

// select привязки к услуге — рисуется и в карточках, и в шаблоне новой
function works_slug_select($services, $current)
{
	$out = "<select class=\"wcard__slug\">\n";
	$out .= "\t<option value=\"\">— без ссылки —</option>\n";
	foreach ($services as $s) {
		$sel = $s["slug"] === $current ? " selected" : "";
		$out .= "\t<option value=\"" . h($s["slug"]) . "\"" . $sel . ">" . h($s["name"]) . "</option>\n";
	}
	return $out . "</select>";
}

// один фото-слот (до/после) с кнопкой замены
function works_photo_slot($role, $label, $src)
{
	$empty = $src === "" ? " is-empty" : "";
	ob_start(); ?>
	<div class="wcard__photo js-wphoto" data-role="<?= h($role) ?>">
		<span class="wcard__photo-label"><?= h($label) ?></span>
		<img src="<?= h($src) ?>" alt="" class="wcard__img<?= $empty ?>">
		<input type="hidden" class="wcard__image" value="<?= h($src) ?>">
		<button type="button" class="btn btn--small js-wc-photo"><?= $src === "" ? "Загрузить" : "Заменить" ?></button>
		<input type="file" class="wcard__file" accept="image/jpeg,image/png,image/webp" hidden>
	</div>
	<?php return ob_get_clean();
}
?>
<div class="page-head">
	<h1 class="page-title">Примеры работ</h1>
	<a class="btn" href="/#works" target="_blank" rel="noopener">Открыть на сайте ↗</a>
</div>
<p class="page-hint">Карточки «до/после» на главной. Оба фото подгоняются под квадрат сами — можно загружать любое. На десктопе видно две карточки, на планшете и мобиле — одну.</p>

<form class="card js-tab-form" data-tab="works" data-action="works_save" id="works-form">
	<label class="switch">
		<input type="checkbox" id="works-visible" <?= !empty($works["visible"]) ? "checked" : "" ?>>
		<span>Показывать блок на главной</span>
	</label>

	<label class="field">
		<span class="field__label">Заголовок блока</span>
		<input type="text" name="title" value="<?= h(isset($works["title"]) ? $works["title"] : "") ?>">
	</label>

	<div id="works-cards">
		<?php foreach ((array) (isset($works["items"]) ? $works["items"] : array()) as $w): ?>
		<div class="wcard js-wcard">
			<div class="wcard__photos">
				<?= works_photo_slot("before", "До", isset($w["before"]) ? $w["before"] : "") ?>
				<?= works_photo_slot("after", "После", isset($w["after"]) ? $w["after"] : "") ?>
			</div>
			<div class="wcard__fields">
				<input type="text" class="wcard__text" value="<?= h(isset($w["text"]) ? $w["text"] : "") ?>" placeholder="Что сделали (текст под фото)">
				<div class="wcard__row">
					<input type="text" class="wcard__year" value="<?= h(isset($w["year"]) ? $w["year"] : "") ?>" placeholder="Год">
					<input type="text" class="wcard__location" value="<?= h(isset($w["location"]) ? $w["location"] : "") ?>" placeholder="Локация">
				</div>
				<?= works_slug_select($services, isset($w["slug"]) ? $w["slug"] : "") ?>
			</div>
			<span class="pgroup__tools wcard__tools">
				<button type="button" class="icon-btn js-wc-move" data-dir="up" title="Выше">↑</button>
				<button type="button" class="icon-btn js-wc-move" data-dir="down" title="Ниже">↓</button>
				<button type="button" class="icon-btn icon-btn--danger js-wc-del" title="Удалить пример">✕</button>
			</span>
		</div>
		<?php endforeach; ?>
	</div>

	<button type="button" class="btn" id="wc-add">+ Добавить пример</button>
	<div class="tab__actions"><button type="submit" class="btn btn--primary">Сохранить</button></div>
</form>

<template id="tpl-wcard">
	<div class="wcard js-wcard">
		<div class="wcard__photos">
			<?= works_photo_slot("before", "До", "") ?>
			<?= works_photo_slot("after", "После", "") ?>
		</div>
		<div class="wcard__fields">
			<input type="text" class="wcard__text" value="" placeholder="Что сделали (текст под фото)">
			<div class="wcard__row">
				<input type="text" class="wcard__year" value="" placeholder="Год">
				<input type="text" class="wcard__location" value="" placeholder="Локация">
			</div>
			<?= works_slug_select($services, "") ?>
		</div>
		<span class="pgroup__tools wcard__tools">
			<button type="button" class="icon-btn js-wc-move" data-dir="up" title="Выше">↑</button>
			<button type="button" class="icon-btn js-wc-move" data-dir="down" title="Ниже">↓</button>
			<button type="button" class="icon-btn icon-btn--danger js-wc-del" title="Удалить пример">✕</button>
		</span>
	</div>
</template>


<?php // пустой слаг: общий обработчик сохранения его требует, экшен works — игнорирует ?>
<input type="hidden" id="edit-slug" value="">