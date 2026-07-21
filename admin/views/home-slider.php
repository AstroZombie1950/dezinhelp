<?php
// Слайдер услуг на главной: data/home-services.json. Подключается при page=slider.
// Ждёт $homeSlider и $services (для привязки карточек к услугам).

// select привязки к услуге — рисуется и в карточках, и в шаблоне новой
function slider_slug_select($services, $current)
{
	$out = "<select class=\"scard__slug\">\n";
	$out .= "\t<option value=\"\">— без ссылки —</option>\n";
	foreach ($services as $s) {
		$sel = $s["slug"] === $current ? " selected" : "";
		$out .= "\t<option value=\"" . h($s["slug"]) . "\"" . $sel . ">" . h($s["name"]) . "</option>\n";
	}
	return $out . "</select>";
}
?>
<div class="page-head">
	<h1 class="page-title">Слайдер главной</h1>
	<a class="btn" href="/#services" target="_blank" rel="noopener">Открыть на сайте ↗</a>
</div>
<p class="page-hint">Карточки услуг с фотографиями на главной странице. Фото подгоняется под квадрат 400×400 само — можно загружать любое.</p>

<form class="card js-tab-form" data-tab="slider" data-action="home_slider_save" id="slider-form">
	<label class="switch">
		<input type="checkbox" id="slider-visible" <?= !empty($homeSlider["visible"]) ? "checked" : "" ?>>
		<span>Показывать слайдер на главной</span>
	</label>

	<label class="field">
		<span class="field__label">Заголовок блока</span>
		<input type="text" name="title" value="<?= h(isset($homeSlider["title"]) ? $homeSlider["title"] : "") ?>">
	</label>

	<div id="slider-cards">
		<?php foreach ((array) (isset($homeSlider["cards"]) ? $homeSlider["cards"] : array()) as $c): ?>
		<div class="scard js-scard">
			<div class="scard__photo">
				<img src="<?= h($c["image"]) ?>" alt="" class="scard__img">
				<input type="hidden" class="scard__image" value="<?= h($c["image"]) ?>">
				<button type="button" class="btn btn--small js-sc-photo">Заменить фото</button>
				<input type="file" class="scard__file" accept="image/jpeg,image/png,image/webp" hidden>
			</div>
			<div class="scard__fields">
				<div class="scard__row">
					<input type="text" class="scard__title" value="<?= h($c["title"]) ?>" placeholder="Заголовок (крупный)">
					<input type="text" class="scard__subtitle" value="<?= h($c["subtitle"]) ?>" placeholder="Подзаголовок">
				</div>
				<textarea class="scard__items" rows="5" placeholder="Каждая строка — отдельный пункт цены"><?= h(implode("\n", $c["items"])) ?></textarea>
				<div class="scard__row">
					<input type="text" class="scard__from" value="<?= h($c["from"]) ?>" placeholder="Плашка «ОТ 2300 ₽»">
					<?= slider_slug_select($services, $c["slug"]) ?>
				</div>
			</div>
			<span class="pgroup__tools scard__tools">
				<button type="button" class="icon-btn js-sc-move" data-dir="up" title="Выше">↑</button>
				<button type="button" class="icon-btn js-sc-move" data-dir="down" title="Ниже">↓</button>
				<button type="button" class="icon-btn icon-btn--danger js-sc-del" title="Удалить карточку">✕</button>
			</span>
		</div>
		<?php endforeach; ?>
	</div>

	<button type="button" class="btn" id="sc-add">+ Добавить карточку</button>
	<div class="tab__actions"><button type="submit" class="btn btn--primary">Сохранить</button></div>
</form>

<template id="tpl-scard">
	<div class="scard js-scard">
		<div class="scard__photo">
			<img src="" alt="" class="scard__img is-empty">
			<input type="hidden" class="scard__image" value="">
			<button type="button" class="btn btn--small js-sc-photo">Загрузить фото</button>
			<input type="file" class="scard__file" accept="image/jpeg,image/png,image/webp" hidden>
		</div>
		<div class="scard__fields">
			<div class="scard__row">
				<input type="text" class="scard__title" value="" placeholder="Заголовок (крупный)">
				<input type="text" class="scard__subtitle" value="" placeholder="Подзаголовок">
			</div>
			<textarea class="scard__items" rows="5" placeholder="Каждая строка — отдельный пункт цены"></textarea>
			<div class="scard__row">
				<input type="text" class="scard__from" value="" placeholder="Плашка «ОТ 2300 ₽»">
				<?= slider_slug_select($services, "") ?>
			</div>
		</div>
		<span class="pgroup__tools scard__tools">
			<button type="button" class="icon-btn js-sc-move" data-dir="up" title="Выше">↑</button>
			<button type="button" class="icon-btn js-sc-move" data-dir="down" title="Ниже">↓</button>
			<button type="button" class="icon-btn icon-btn--danger js-sc-del" title="Удалить карточку">✕</button>
		</span>
	</div>
</template>

<input type="hidden" id="edit-slug" value="">