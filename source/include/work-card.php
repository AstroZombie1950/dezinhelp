<?php
// Карточка примера работ в слайдере на главной. Ждёт $work (элемент data/works.json)
// и $workClone (true — это клон: прячем от скринридеров и вырезаем из таба).
$wYear = isset($work["year"]) ? trim($work["year"]) : "";
$wLoc  = isset($work["location"]) ? trim($work["location"]) : "";
// «год, локация» одной строкой; если чего-то нет — лишней запятой не будет
$wMeta = $wYear . ($wYear !== "" && $wLoc !== "" ? ", " : "") . $wLoc;
?>
<div class="work-card"<?= $workClone ? ' aria-hidden="true"' : '' ?>>

	<!-- до | стрелка | после -->
	<div class="work-card__media">
		<figure class="work-card__photo">
			<img src="<?= h($work["before"]) ?>" alt="<?= $workClone ? "" : "До: " . h($work["text"]) ?>" width="640" height="640" loading="lazy">
			<figcaption class="work-card__badge">До</figcaption>
		</figure>

		<!-- стрелка-переход: кружок фирменного градиента с белым шевроном -->
		<span class="work-card__arrow" aria-hidden="true">
			<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M9 6l6 6-6 6"/></svg>
		</span>

		<figure class="work-card__photo">
			<img src="<?= h($work["after"]) ?>" alt="<?= $workClone ? "" : "После: " . h($work["text"]) ?>" width="640" height="640" loading="lazy">
			<figcaption class="work-card__badge work-card__badge--after">После</figcaption>
		</figure>
	</div>

	<!-- текст -->
	<div class="work-card__body">
		<div class="work-card__text"><?= h($work["text"]) ?></div>

		<!-- низ: кнопка на услугу из примера + год/локация в углу -->
		<div class="work-card__foot">
			<?php if (!empty($work["slug"])): ?>
			<a href="/<?= h($work["slug"]) ?>/" class="btn btn--primary btn--sm"<?= $workClone ? ' tabindex="-1"' : '' ?>>Такая же проблема</a>
			<?php endif; ?>
			<?php if ($wMeta !== ""): ?>
			<span class="work-card__meta"><?= h($wMeta) ?></span>
			<?php endif; ?>
		</div>
	</div>

</div>
