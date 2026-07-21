<?php
// Блок «цифры покажут наглядно»: заголовок + лид + 4 счётчика (анимация от нуля).
// Данные — data/stats.json. Счётчики крутит main.js по data-count при попадании в вьюпорт.
$stats = data_load("stats");
if (!empty($stats["visible"]) && !empty($stats["items"])):
	// бренд в заголовке — фирменной градиент-маркой (тот же класс, что в герое).
	// режем по границам подстроки — байтово-безопасно для UTF-8, без mbstring
	$stTitle = isset($stats["title"]) ? $stats["title"] : "";
	$stMark  = isset($stats["title_mark"]) ? $stats["title_mark"] : "";
	$stPos   = ($stMark !== "") ? strpos($stTitle, $stMark) : false;
?>
<section class="section stats" id="stats">
	<div class="container">
		<h2 class="section__title stats__title">
			<?php if ($stPos !== false): ?>
			<?= h(substr($stTitle, 0, $stPos)) ?><span class="hero__title-mark"><?= h($stMark) ?></span><?= h(substr($stTitle, $stPos + strlen($stMark))) ?>
			<?php else: ?>
			<?= h($stTitle) ?>
			<?php endif; ?>
		</h2>

		<?php if (!empty($stats["lead"])): ?>
		<p class="stats__lead"><?= h($stats["lead"]) ?></p>
		<?php endif; ?>

		<!-- сетка счётчиков -->
		<div class="stats__grid">
			<?php foreach ($stats["items"] as $item): ?>
			<div class="stats__item">
				<span class="stats__num" data-count="<?= h($item["value"]) ?>">0</span>
				<span class="stats__cap"><?= h($item["caption"]) ?></span>
			</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
<?php endif; ?>