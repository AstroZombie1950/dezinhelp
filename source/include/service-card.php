<?php
// Карточка услуги в слайдере на главной. Ждёт $card (элемент data/home-services.json)
// и $cardAria (true — это клон, прячем от скринридеров и вырезаем из таба).
?>
<div class="srv-card"<?= $cardAria ? ' aria-hidden="true"' : '' ?>>
	<div class="srv-card__img" style="background-image:url(<?= h($card["image"]) ?>)"></div>
	<div class="srv-card__body">
		<div class="srv-card__title"><?= h($card["title"]) ?><span><?= h($card["subtitle"]) ?></span></div>
		<ul class="srv-card__list">
			<?php foreach ($card["items"] as $item): ?>
			<li><?= h($item) ?></li>
			<?php endforeach; ?>
		</ul>
		<div class="srv-card__from"><?= h($card["from"]) ?></div>
		<div class="srv-card__actions">
			<?php if (!empty($card["slug"])): ?>
			<a href="/<?= h($card["slug"]) ?>/" class="btn btn--primary btn--sm"<?= $cardAria ? ' tabindex="-1"' : '' ?>>Подробнее</a>
			<?php endif; ?>
			<button type="button" class="btn btn--outline btn--sm js-order-open"<?= $cardAria ? ' tabindex="-1"' : '' ?>>Заказать</button>
		</div>
	</div>
</div>