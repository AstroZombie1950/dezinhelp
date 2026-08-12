<?php
// Лицензия и аттестация сотрудников: номер лицензии, скан удостоверения, список гарантий.
// Данные — data/license.json, номер лицензии — общий $license из bootstrap.php.
// Пока заказчик не прислал скан, image пустой — на его месте рисуется заглушка.
$lic = data_load("license");
if (!empty($lic["visible"])):
	// марку в заголовке подсвечиваем градиентом — тем же приёмом, что в герое и в цифрах
	$licTitle = isset($lic["title"]) ? $lic["title"] : "";
	$licMark  = isset($lic["title_mark"]) ? $lic["title_mark"] : "";
	$licPos   = ($licMark !== "") ? strpos($licTitle, $licMark) : false;
	$licImg   = isset($lic["image"]) ? trim($lic["image"]) : "";
	// размеры скана — из данных: без них картинка догружается и двигает вёрстку.
	// Меняете скан — поправьте и их, иначе браузер зарезервирует чужие пропорции
	$licW     = isset($lic["image_width"]) ? (int) $lic["image_width"] : 0;
	$licH     = isset($lic["image_height"]) ? (int) $lic["image_height"] : 0;
?>
<section class="section section--alt license" id="license">
	<div class="container">

		<?php if ($license !== ""): ?>
		<div class="license__eyebrow"><?= h($license) ?></div>
		<?php endif; ?>

		<h2 class="section__title license__title">
			<?php if ($licPos !== false): ?>
			<?= h(substr($licTitle, 0, $licPos)) ?><span class="hero__title-mark"><?= h($licMark) ?></span><?= h(substr($licTitle, $licPos + strlen($licMark))) ?>
			<?php else: ?>
			<?= h($licTitle) ?>
			<?php endif; ?>
		</h2>

		<figure class="license__doc">
			<?php if ($licImg !== ""): ?>
			<img class="license__img" src="<?= h($licImg) ?>" alt="<?= h($lic["image_alt"]) ?>"<?= $licW && $licH ? ' width="' . $licW . '" height="' . $licH . '"' : "" ?> loading="lazy" decoding="async">
			<?php else: ?>
			<!-- заглушка до получения скана от заказчика -->
			<div class="license__stub">
				<svg class="license__stub-icon" viewBox="0 0 48 48" aria-hidden="true">
					<rect x="6" y="9" width="36" height="30" rx="3" fill="none" stroke="currentColor" stroke-width="2.5"/>
					<line x1="24" y1="9" x2="24" y2="39" stroke="currentColor" stroke-width="2.5"/>
					<circle cx="15" cy="20" r="3.5" fill="none" stroke="currentColor" stroke-width="2"/>
					<path d="M10 30c1.6-3 8.4-3 10 0" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
					<line x1="29" y1="18" x2="38" y2="18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
					<line x1="29" y1="24" x2="38" y2="24" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
					<line x1="29" y1="30" x2="34" y2="30" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
				</svg>
				<span class="license__stub-text"><?= h($lic["image_caption"]) ?></span>
			</div>
			<?php endif; ?>
		</figure>

		<?php if (!empty($lic["items"])): ?>
		<div class="license__trust">
			<h3 class="license__subtitle"><span><?= h($lic["subtitle"]) ?></span></h3>
			<ul class="license__list">
				<?php foreach ($lic["items"] as $item): ?>
				<li class="license__item"><?= $item ?></li>
				<?php endforeach; ?>
			</ul>
		</div>
		<?php endif; ?>

	</div>
</section>
<?php endif; ?>
