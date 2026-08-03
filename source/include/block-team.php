<?php
// Команда: ряд круглых фото внахлёст. Данные — data/team.json.
$team = data_load("team");
if (!empty($team["visible"]) && !empty($team["people"])):
?>
<section class="section section--alt" id="team">
	<div class="container">
		<h2 class="section__title"><?= h($team["title"]) ?></h2>

		<!-- кружки заходят друг на друга; при наведении активный выходит вперёд -->
		<div class="team">
			<?php foreach ($team["people"] as $i => $person): ?>
			<div class="team__item">
				<div class="team__photo">
					<img src="<?= h($person["photo"]) ?>" alt="<?= h($person["name"] . ", " . $person["role"]) ?>" width="480" height="480" loading="lazy" decoding="async">
				</div>
				<div class="team__caption">
					<div class="team__name"><?= h($person["name"]) ?></div>
					<div class="team__role"><?= h($person["role"]) ?></div>
					<div class="team__exp"><?= h($person["experience"]) ?></div>
				</div>
			</div>
			<?php endforeach; ?>
		</div>

		<?php if (!empty($team["note"])): ?>
		<p class="team__note"><?= $team["note"] ?></p>
		<?php endif; ?>
	</div>
</section>
<?php endif; ?>
