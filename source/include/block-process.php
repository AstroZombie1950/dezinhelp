<?php
// Порядок обработки. Данные — data/process.json, правится через админку.
$process = data_load("process");
if (!empty($process["visible"]) && $process["steps"]):
?>
<section class="section" id="process">
	<div class="container">
		<h2 class="section__title"><?= h($process["title"]) ?></h2>
		<div class="steps">
			<?php foreach ($process["steps"] as $n => $step): ?>
			<div class="step">
				<div class="step__num"><?= $n + 1 ?></div>
				<div class="step__title"><?= h($step["title"]) ?></div>
				<div class="step__text"><?= h($step["text"]) ?></div>
			</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
<?php endif; ?>
