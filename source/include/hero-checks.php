<?php
// Чек-лист героя: свой у каждой страницы. Список кладёт вызывающий в $heroChecks —
// услуга берёт его из data/services/<slug>.json, главная из data/site.json.
// В заголовке [квадратные скобки] превращаются в маркер-подсветку.
$checks = (isset($heroChecks) && is_array($heroChecks)) ? $heroChecks : array();

// контент ещё не заполнили — показываем общий набор, чтобы блок не пустовал
if (!$checks) {
	$checks = array(
		array("title" => "Устраним проблему [за 1 час на 100%.]", "sub" => "Если не поможет — приедем бесплатно еще раз!"),
		array("title" => "Гарантия [до 2-х лет.]",                "sub" => "Прописана в договоре."),
		array("title" => "[Лицензия] СЭС",                        "sub" => "Сотрудники предъявят документы по приезду."),
		array("title" => "[НАШЛИ ДЕШЕВЛЕ?]",                      "sub" => "Мы сделаем цену еще ниже!"),
	);
}
?>
<ul class="hero__checks">
	<?php foreach ($checks as $c): ?>
	<?php if (empty($c["title"])) { continue; } ?>
	<li class="hero__check">
		<span class="hero__check-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="4"/><path d="M8 12.5l2.5 2.5 5-5.5"/></svg></span>
		<div class="hero__check-body">
			<div class="hero__check-title"><?= hero_check_title($c["title"]) ?></div>
			<?php if (!empty($c["sub"])): ?>
			<div class="hero__check-sub"><?= h($c["sub"]) ?></div>
			<?php endif; ?>
		</div>
	</li>
	<?php endforeach; ?>
</ul>
