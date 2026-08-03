<?php
// Отзывы: карусель карточек. Данные — data/reviews.json.
// Вместо текста отзыва в карточке стоит ряд скринов: клик открывает полный размер
// в общем лайтбоксе. Пока заказчик не прислал скрины, shots пустой — рисуются заглушки,
// текст отзыва при этом остаётся в json и в разметку не идёт.
$reviews = data_load("reviews");
if (!empty($reviews["visible"]) && !empty($reviews["items"])):
	$stubCount = isset($reviews["shots_placeholder"]) ? (int) $reviews["shots_placeholder"] : 3;
?>
	<!-- отзывы: карусель -->
	<section class="section section--alt" id="reviews">
		<div class="container">
			<h2 class="section__title"><?= h($reviews["title"]) ?></h2>
			<div class="reviews__ratings">
				<div class="reviews__rating"><strong><?= h($reviews["rating"]) ?></strong><?= h($reviews["rating_caption"]) ?></div>
			</div>
			<div class="reviews__carousel">
				<div class="reviews__track">
					<?php foreach ($reviews["items"] as $r): ?>
					<?php
					$stars = max(0, min(5, (int) $r["stars"]));
					$shots = !empty($r["shots"]) ? $r["shots"] : array();
					?>
					<div class="review-card">
						<div class="review-card__head">
							<div class="review-card__avatar" style="background:<?= h($r["color"]) ?>"><?= h($r["letter"]) ?></div>
							<div><div class="review-card__name"><?= h($r["name"]) ?></div><div class="review-card__date"><?= h($r["date"]) ?></div></div>
						</div>
						<div class="review-card__stars" aria-label="Оценка: <?= $stars ?> из 5"><?= str_repeat("★", $stars) . str_repeat("☆", 5 - $stars) ?></div>

						<div class="review-card__shots">
							<?php if ($shots): ?>
							<?php foreach ($shots as $n => $shot): ?>
							<button type="button" class="review-card__shot js-lightbox"
								data-lightbox-src="<?= h($shot) ?>"
								data-lightbox-alt="<?= h("Отзыв клиента " . $r["name"]) ?>"
								aria-label="<?= h("Открыть скриншот отзыва: " . $r["name"] . ", " . ($n + 1) . " из " . count($shots)) ?>">
								<img src="<?= h($shot) ?>" alt="<?= h("Скриншот отзыва: " . $r["name"]) ?>" loading="lazy" decoding="async">
							</button>
							<?php endforeach; ?>
							<?php else: ?>
							<?php for ($i = 0; $i < $stubCount; $i++): ?>
							<!-- заглушка до получения скринов от заказчика -->
							<div class="review-card__shot review-card__shot--stub" aria-hidden="true">
								<svg viewBox="0 0 32 32">
									<rect x="4" y="6" width="24" height="20" rx="2.5" fill="none" stroke="currentColor" stroke-width="2"/>
									<circle cx="12" cy="13" r="2.2" fill="none" stroke="currentColor" stroke-width="1.8"/>
									<path d="M7 23l6-6 4 4 3-3 5 5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
								</svg>
							</div>
							<?php endfor; ?>
							<?php endif; ?>
						</div>
					</div>
					<?php endforeach; ?>
				</div>
				<div class="reviews__nav">
					<button type="button" class="reviews__btn" data-dir="prev" aria-label="Предыдущий отзыв">‹</button>
					<button type="button" class="reviews__btn" data-dir="next" aria-label="Следующий отзыв">›</button>
				</div>
			</div>
		</div>
	</section>
<?php endif; ?>
