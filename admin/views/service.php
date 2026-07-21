<?php
// Редактор страницы услуги. Подключается из admin/index.php при page=service.
// Ждёт: $editSvc (строка индекса), $editPage (контент data/services/<slug>.json).
$eSlug   = $editSvc["slug"];
$eSeo    = isset($editPage["seo"]) ? $editPage["seo"] : array();
$eBlocks = isset($editPage["blocks"]) ? $editPage["blocks"] : array();
$ePrices = isset($editPage["prices"]) ? $editPage["prices"] : array("visible" => true, "groups" => array());
$eFaq    = isset($editPage["faq"]) ? $editPage["faq"] : array();

// подписи тумблеров — человеческие, по порядку блоков на странице
$blockLabels = array(
	"gallery"      => array("Объекты санобработки", "галерея фотографий объектов"),
	"process"      => array("Порядок обработки", "три шага: заявка — обработка — результат"),
	"methods"      => array("Методы обработки", "две карточки с иллюстрацией"),
	"certificates" => array("Свидетельства", "карусель сертификатов"),
	"veterans"     => array("Скидка ветеранам", "баннер с формой записи"),
	"coverage"     => array("Зона выезда", "карта Москвы и области"),
	"quiz"         => array("Форма-опрос (квиз)", "подбор услуги за 4 вопроса"),
	"services"     => array("Виды услуг", "слайдер карточек других услуг"),
	"faq"          => array("Частые вопросы", "вопросы и ответы этой услуги"),
);
?>
<div class="page-head">
	<h1 class="page-title"><?= h($editSvc["name"]) ?></h1>
	<a class="btn" href="/<?= h($eSlug) ?>/" target="_blank" rel="noopener">Открыть страницу ↗</a>
</div>
<p class="page-hint">Каждая вкладка сохраняется своей кнопкой. Несохранённые правки помечаются точкой на кнопке.</p>

<!-- переключатель вкладок -->
<div class="tabs" id="tabs">
	<button type="button" class="tabs__btn is-active" data-tab="seo">SEO</button>
	<button type="button" class="tabs__btn" data-tab="texts">Тексты</button>
	<button type="button" class="tabs__btn" data-tab="prices">Цены</button>
	<button type="button" class="tabs__btn" data-tab="blocks">Блоки</button>
	<button type="button" class="tabs__btn" data-tab="faq">Вопросы</button>
</div>

<!-- ===== SEO ===== -->
<form class="tab card is-active js-tab-form" data-tab="seo" data-action="svc_save_seo">
	<label class="field">
		<span class="field__label">Заголовок страницы (H1)</span>
		<input type="text" name="h1" value="<?= h($editPage["h1"]) ?>">
		<span class="field__hint">Большой заголовок в шапке страницы.</span>
	</label>
	<label class="field">
		<span class="field__label">Сноска под заголовком</span>
		<input type="text" name="hero_note" value="<?= h($editPage["hero_note"]) ?>">
		<span class="field__hint">Мелкий текст со звёздочкой. Пусто — не показывается.</span>
	</label>
	<label class="field">
		<span class="field__label">Title (заголовок вкладки браузера)</span>
		<input type="text" name="title" value="<?= h(isset($eSeo["title"]) ? $eSeo["title"] : "") ?>">
	</label>
	<label class="field">
		<span class="field__label">Description (описание для поисковиков)</span>
		<textarea name="description" rows="3"><?= h(isset($eSeo["description"]) ? $eSeo["description"] : "") ?></textarea>
	</label>
	<label class="field">
		<span class="field__label">Keywords (ключевые слова через запятую)</span>
		<textarea name="keywords" rows="2"><?= h(isset($eSeo["keywords"]) ? $eSeo["keywords"] : "") ?></textarea>
	</label>
	<div class="tab__actions"><button type="submit" class="btn btn--primary">Сохранить</button></div>
</form>

<!-- ===== Тексты ===== -->
<form class="tab card js-tab-form" data-tab="texts" data-action="svc_save_texts">
	<?php // textarea с js-editor оборачивается в визуальный редактор скриптом ?>
	<div class="field">
		<span class="field__label">Вводный текст</span>
		<textarea name="intro_html" rows="12" class="code js-editor"><?= h($editPage["intro_html"]) ?></textarea>
		<span class="field__hint">Первый абзац виден всегда, остальное прячется под кнопку «Читать полностью». В режиме HTML разрешены теги: p, h2, h3, strong, em, ul, ol, li, a — лишнее уберётся при сохранении.</span>
	</div>
	<div class="field">
		<span class="field__label">SEO-текст</span>
		<textarea name="seo_html" rows="14" class="code js-editor"><?= h($editPage["seo_html"]) ?></textarea>
		<span class="field__hint">Показывается под кнопкой «Читать полностью», после вводного.</span>
	</div>
	<div class="tab__actions"><button type="submit" class="btn btn--primary">Сохранить</button></div>
</form>

<!-- ===== Цены ===== -->
<form class="tab card js-tab-form" data-tab="prices" data-action="svc_save_prices" id="prices-form">
	<label class="switch">
		<input type="checkbox" id="prices-visible" <?= !empty($ePrices["visible"]) ? "checked" : "" ?>>
		<span>Показывать блок цен на странице</span>
	</label>

	<?php $pgGroups = isset($ePrices["groups"]) ? $ePrices["groups"] : array(); ?>
	<?php require __DIR__ . "/prices-groups.php"; ?>

	<div class="tab__actions"><button type="submit" class="btn btn--primary">Сохранить</button></div>
</form>

<!-- ===== Блоки ===== -->
<form class="tab card js-tab-form" data-tab="blocks" data-action="svc_save_blocks">
	<p class="page-hint" style="margin-top: 0;">Выключенный блок пропадает со страницы целиком. Порядок блоков — как на сайте.</p>
	<?php foreach ($blockLabels as $key => $label): ?>
	<label class="switch switch--row">
		<input type="checkbox" name="blocks[]" value="<?= h($key) ?>" <?= !empty($eBlocks[$key]) ? "checked" : "" ?>>
		<span><b><?= h($label[0]) ?></b> <em><?= h($label[1]) ?></em></span>
	</label>
	<?php endforeach; ?>
	<div class="tab__actions"><button type="submit" class="btn btn--primary">Сохранить</button></div>
</form>

<!-- ===== Вопросы (FAQ) ===== -->
<form class="tab card js-tab-form" data-tab="faq" data-action="svc_save_faq" id="faq-form">
	<div id="faq-items">
		<?php foreach ($eFaq as $f): ?>
		<div class="fitem js-fitem">
			<div class="fitem__head">
				<input type="text" class="fitem__q" value="<?= h($f["q"]) ?>" placeholder="Вопрос">
				<span class="pgroup__tools">
					<button type="button" class="icon-btn js-fq-move" data-dir="up" title="Выше">↑</button>
					<button type="button" class="icon-btn js-fq-move" data-dir="down" title="Ниже">↓</button>
					<button type="button" class="icon-btn icon-btn--danger js-fq-del" title="Удалить вопрос">✕</button>
				</span>
			</div>
			<textarea class="fitem__a" rows="3" placeholder="Ответ"><?= h($f["a"]) ?></textarea>
		</div>
		<?php endforeach; ?>
	</div>
	<button type="button" class="btn" id="fq-add">+ Добавить вопрос</button>
	<div class="tab__actions"><button type="submit" class="btn btn--primary">Сохранить</button></div>
</form>

<template id="tpl-fitem">
	<div class="fitem js-fitem">
		<div class="fitem__head">
			<input type="text" class="fitem__q" value="" placeholder="Вопрос">
			<span class="pgroup__tools">
				<button type="button" class="icon-btn js-fq-move" data-dir="up" title="Выше">↑</button>
				<button type="button" class="icon-btn js-fq-move" data-dir="down" title="Ниже">↓</button>
				<button type="button" class="icon-btn icon-btn--danger js-fq-del" title="Удалить вопрос">✕</button>
			</span>
		</div>
		<textarea class="fitem__a" rows="3" placeholder="Ответ"></textarea>
	</div>
</template>

<input type="hidden" id="edit-slug" value="<?= h($eSlug) ?>">