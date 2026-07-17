<?php
// FAQ. Ждёт $faqItems (массив q/a) и $faqTitle.
// На главной данные из data/faq.json, на странице услуги — из файла услуги.
if (!empty($faqItems)):
?>
<section class="section" id="faq">
	<div class="container">
		<h2 class="section__title"><?= h($faqTitle) ?></h2>
		<div class="faq__list">
			<?php foreach ($faqItems as $item): ?>
			<div class="faq-item">
				<button type="button" class="faq-item__question"><?= h($item["q"]) ?><span class="faq-item__plus"></span></button>
				<div class="faq-item__answer"><div class="faq-item__answer-inner"><?= $item["a"] ?></div></div>
			</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
<?php endif; ?>
