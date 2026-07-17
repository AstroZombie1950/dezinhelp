<?php
// Квиз-заявка. Ждёт $quizData (data/quiz.json), опц. $quizSlug — слаг текущей услуги
// для предвыбора варианта, опц. $quizSource — подпись заявки в Telegram.
// Все шаги в разметке сразу, показ/скрытие и прогресс — на JS (см. main.js).
$qSteps = isset($quizData["steps"]) ? $quizData["steps"] : array();
if (!empty($quizData["visible"]) && $qSteps):
	$qFinal  = $quizData["final"];
	$qTotal  = count($qSteps) + 1;                       // шаги + финальный экран
	$qSlug   = isset($quizSlug) ? $quizSlug : "";
	$qSource = isset($quizSource) ? $quizSource : $quizData["source"];

	// прогресс от 0% до 100% равными долями: пока ничего не выбрано — ноль,
	// на финале полоска закрыта. переживает добавление шага в админке
	$qPercent = function ($i) use ($qTotal) {
		return $qTotal < 2 ? 100 : (int) round(100 * $i / ($qTotal - 1));
	};
?>
<section class="section" id="quiz">
	<div class="container">
		<?php if (!empty($quizData["title"])): ?>
		<h2 class="section__title"><?= h($quizData["title"]) ?></h2>
		<?php endif; ?>

		<form class="quiz js-quiz js-form" action="/source/php/order.php" method="post">
			<input type="hidden" name="source" value="<?= h($qSource) ?>">
			<input type="hidden" name="name_optional" value="1">
			<input type="text" name="website" class="hp" tabindex="-1" autocomplete="off" aria-hidden="true">

			<div class="quiz__body">
				<?php foreach ($qSteps as $i => $step): ?>
				<?php $qMulti = isset($step["type"]) && $step["type"] === "checkbox"; ?>
				<div class="quiz__step<?= $i === 0 ? " is-active" : "" ?>" data-percent="<?= $qPercent($i) ?>">
					<div class="quiz__question"><?= h($step["title"]) ?></div>
					<div class="quiz__options">
						<?php foreach ($step["options"] as $j => $opt): ?>
						<?php
						// вариант, к которому привязана текущая услуга, отмечаем заранее:
						// человек уже прочитал H1, повторно тыкать очевидное не заставляем
						$qChecked = $qSlug !== "" && !empty($opt["slugs"]) && in_array($qSlug, $opt["slugs"], true);
						?>
						<label class="quiz__option">
							<input type="<?= $qMulti ? "checkbox" : "radio" ?>" name="q<?= $i ?><?= $qMulti ? "[]" : "" ?>" value="<?= $j ?>"<?= $qChecked ? " checked" : "" ?>>
							<span class="quiz__mark<?= $qMulti ? " quiz__mark--box" : "" ?>"></span>
							<span class="quiz__option-text"><?= h($opt["text"]) ?></span>
						</label>
						<?php endforeach; ?>
					</div>
				</div>
				<?php endforeach; ?>

				<?php // финальный экран: слева заголовок, справа телефон, способ связи и согласие ?>
				<div class="quiz__step quiz__step--final" data-percent="<?= $qPercent($qTotal - 1) ?>">
					<div class="quiz__final">
						<div class="quiz__final-title"><?= h($qFinal["title"]) ?></div>

						<div class="quiz__final-form">
							<div class="quiz__field-label"><?= h($qFinal["phone_label"]) ?></div>
							<input type="tel" name="phone" placeholder="+7 (___) ___-__-__" autocomplete="tel">

							<div class="quiz__field-label"><?= h($qFinal["contacts_label"]) ?></div>
							<div class="quiz__contacts">
								<?php foreach ($qFinal["contacts"] as $k => $c): ?>
								<label class="quiz__contact" title="<?= h($c["text"]) ?>">
									<input type="radio" name="contact_method" value="<?= h($c["id"]) ?>"<?= $k === 0 ? " checked" : "" ?>>
									<span class="quiz__contact-icon"><svg aria-hidden="true"><use href="#<?= h($c["icon"]) ?>"></use></svg></span>
									<span class="hp"><?= h($c["text"]) ?></span>
								</label>
								<?php endforeach; ?>
							</div>

							<button type="submit" class="btn btn--primary quiz__submit"><?= h($qFinal["button"]) ?></button>

							<label class="quiz__consent">
								<input type="checkbox" name="consent" value="1">
								<span class="quiz__mark quiz__mark--box"></span>
								<span class="quiz__consent-text"><?= $qFinal["consent_html"] ?></span>
							</label>

							<div class="form-status" role="status"></div>
						</div>
					</div>
				</div>
			</div>

			<?php // футер общий на все экраны: прогресс слева, навигация справа ?>
			<div class="quiz__footer">
				<div class="quiz__progress">
					<div class="quiz__progress-text">
						<span class="js-quiz-label"><?= h($quizData["progress_label"]) ?></span>
						<strong class="js-quiz-percent"><?= $qPercent(0) ?>%</strong>
					</div>
					<div class="quiz__bar"><span class="quiz__bar-fill js-quiz-fill" style="width: <?= $qPercent(0) ?>%"></span></div>
				</div>

				<div class="quiz__nav">
					<button type="button" class="quiz__back js-quiz-back" aria-label="Назад"><svg aria-hidden="true"><use href="#i-arr-left"></use></svg></button>
					<button type="button" class="btn btn--primary quiz__next js-quiz-next" disabled><?= h($quizData["next_button"]) ?><svg aria-hidden="true"><use href="#i-arr-right"></use></svg></button>
				</div>
			</div>

			<?php // подпись экрана-финала — JS подставляет её вместо «Квиз пройден на:» ?>
			<span class="hp js-quiz-final-label"><?= h($quizData["final_label"]) ?></span>
		</form>
	</div>
</section>
<?php endif; ?>