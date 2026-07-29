<?php
// Поля чек-листа героя: ровно четыре пункта. Общие для услуги и главной.
// Ждёт: $hcItems (массив пунктов из JSON, может быть пустым).
$hcItems = isset($hcItems) && is_array($hcItems) ? $hcItems : array();
?>
<div class="field__label field--sep">Чек-лист в герое</div>
<p class="field__hint field__hint--top">Четыре пункта с галочками рядом с формой заявки. Текст в [квадратных скобках] покажется зелёным маркером. Пустой заголовок — пункт не выводится.</p>

<?php for ($i = 0; $i < 4; $i++): ?>
<?php $hc = isset($hcItems[$i]) ? $hcItems[$i] : array(); ?>
<div class="fitem">
	<label class="field">
		<span class="field__label">Пункт <?= $i + 1 ?> — заголовок</span>
		<input type="text" name="check_title[]" value="<?= h(isset($hc["title"]) ? $hc["title"] : "") ?>" placeholder="Гарантия [до 2-х лет.]">
	</label>
	<label class="field">
		<span class="field__label">Подпись под заголовком</span>
		<input type="text" name="check_sub[]" value="<?= h(isset($hc["sub"]) ? $hc["sub"] : "") ?>" placeholder="Прописана в договоре.">
	</label>
</div>
<?php endfor; ?>
