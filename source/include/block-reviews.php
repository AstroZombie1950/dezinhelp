<?php
// Отзывы: карусель карточек. Данные — data/reviews.json.
// Карточка с is_shot показывает скриншот переписки (клик открывает полный размер
// в общем лайтбоксе), остальные — текст отзыва. Порядок карточек задаёт заказчик.
$reviews = data_load("reviews");
if (!empty($reviews["visible"]) && !empty($reviews["items"])):
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
					// карточка показывает либо скриншот переписки, либо текст отзыва
					$isShot = !empty($r["is_shot"]);
					// сдвиг превью: если сверху скрина не сам отзыв, а переписка до него
					$shotPos = !empty($r["shot_position"]) ? $r["shot_position"] : "";
					?>
					<div class="review-card">
						<div class="review-card__head">
							<div class="review-card__avatar" style="background:<?= h($r["color"]) ?>"><?= h($r["letter"]) ?></div>
							<div><div class="review-card__name"><?= h($r["name"]) ?></div><div class="review-card__date"><?= h($r["date"]) ?></div></div>
						</div>
						<div class="review-card__stars" aria-label="Оценка: <?= $stars ?> из 5"><?= str_repeat("★", $stars) . str_repeat("☆", 5 - $stars) ?></div>

						<?php if ($isShot): ?>
						<div class="review-card__shots">
							<?php if ($shots): ?>
							<?php foreach ($shots as $n => $shot): ?>
							<button type="button" class="review-card__shot review-card__shot--img js-lightbox"
								data-lightbox-src="<?= h($shot) ?>"
								data-lightbox-alt="<?= h("Отзыв клиента " . $r["name"]) ?>"
								aria-label="<?= h("Открыть скриншот отзыва: " . $r["name"]) ?>"
								<?= $shotPos !== "" ? 'style="--shot-pos: ' . h($shotPos) . '"' : "" ?>>
								<img src="<?= h($shot) ?>" alt="<?= h("Скриншот отзыва: " . $r["name"]) ?>" loading="lazy" decoding="async">
								<span class="review-card__zoom"><svg aria-hidden="true"><use href="#i-zoom"></use></svg>увеличить</span>
							</button>
							<?php endforeach; ?>
							<?php else: ?>
							<!-- заглушка: отзыв помечен как скриншотный, но файл ещё не прислан -->
							<div class="review-card__shot review-card__shot--stub" aria-hidden="true">
								<svg viewBox="0 0 32 32">
									<rect x="4" y="6" width="24" height="20" rx="2.5" fill="none" stroke="currentColor" stroke-width="2"/>
									<circle cx="12" cy="13" r="2.2" fill="none" stroke="currentColor" stroke-width="1.8"/>
									<path d="M7 23l6-6 4 4 3-3 5 5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
								</svg>
								Скриншот отзыва
							</div>
							<?php endif; ?>
						</div>
						<?php else: ?>
						<div class="review-card__text"><?= h($r["text"]) ?></div>
						<?php endif; ?>
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
