<?php
// Герой главной. Подключается из admin/index.php при page=hero.
// Ждёт: $site (data/site.json).
?>
<div class="page-head">
	<h1 class="page-title">Герой главной</h1>
	<a class="btn" href="/" target="_blank" rel="noopener">Открыть страницу ↗</a>
</div>
<p class="page-hint">Чек-лист первого экрана главной. У каждой услуги свой такой же список — он правится в карточке услуги, на вкладке «Герой».</p>

<form class="tab card is-active js-tab-form" data-tab="hero" data-action="home_save_hero">
	<?php $hcItems = isset($site["hero_checks"]) ? $site["hero_checks"] : array(); ?>
	<?php require __DIR__ . "/hero-checks-fields.php"; ?>
	<div class="tab__actions"><button type="submit" class="btn btn--primary">Сохранить</button></div>
</form>

<?php // пустой слаг: общий обработчик сохранения его требует, экшен главной — игнорирует ?>
<input type="hidden" id="edit-slug" value="">
