<?php
// Бегущая строка. Ждёт $marqueeItems — массив фраз.
// Список дублируется ×2: вторая копия догоняет первую, шов не виден.
?>
<div class="marquee">
	<div class="marquee__track">
		<?php for ($i = 0; $i < 2; $i++): foreach ($marqueeItems as $m): ?>
		<span class="marquee__item"><?= h($m) ?></span>
		<span class="marquee__sep" aria-hidden="true">&#8855;</span>
		<?php endforeach; endfor; ?>
	</div>
</div>
