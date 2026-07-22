<?php
// Админка МосКомДез. Одна точка входа: ?page=services|trash, без авторизации — логин.
require __DIR__ . "/php/lib.php";
require __DIR__ . "/php/auth.php";

$page = isset($_GET["page"]) ? (string) $_GET["page"] : "services";
if (!auth_logged_in()) { $page = "login"; }

// данные для страниц
$index    = json_read(data_path("services"));
$groups   = isset($index["groups"]) ? $index["groups"] : array();
$services = isset($index["services"]) ? $index["services"] : array();
$trash    = json_read(trash_meta_path());
$csrf     = auth_logged_in() ? auth_csrf_token() : "";

// название группы по id — для таблиц
$groupNames = array();
foreach ($groups as $g) { $groupNames[$g["id"]] = $g["title"]; }
?>
<!DOCTYPE html>
<html lang="ru">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="robots" content="noindex, nofollow">
	<title>Админка — МосКомДез</title>
	<link rel="icon" type="image/svg+xml" href="/favicon.svg">
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;600;700&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="/admin/source/admin.css">
</head>
<body data-csrf="<?= h($csrf) ?>">

<?php if ($page === "login"): ?>

	<!-- экран входа -->
	<div class="login">
		<form class="login__card" id="login-form" autocomplete="off">
			<div class="login__logo">МосКомДез</div>
			<div class="login__sub">Панель управления сайтом</div>
			<label class="field">
				<span class="field__label">Логин</span>
				<input type="text" name="user" required autofocus>
			</label>
			<label class="field">
				<span class="field__label">Пароль</span>
				<input type="password" name="pass" required>
			</label>
			<button type="submit" class="btn btn--primary btn--block">Войти</button>
		</form>
	</div>

<?php else: ?>

	<!-- шапка админки -->
	<header class="topbar">
		<div class="topbar__brand">МосКомДез <span>· админка</span></div>
		<?php // блоки главной собраны в одну выпадашку — открыта, когда открыт любой из них
		$homePages = array("prices", "slider", "works");
		$homeOpen  = in_array($page, $homePages, true);
		?>
		<nav class="topbar__nav">
			<a href="/admin/" class="<?= $page === "services" ? "is-active" : "" ?>">Услуги</a>

			<div class="topbar__drop<?= $homeOpen ? " is-current" : "" ?>">
				<button type="button" class="topbar__drop-btn js-drop">Главная <span class="topbar__caret">▾</span></button>
				<div class="topbar__drop-menu">
					<a href="/admin/?page=prices" class="<?= $page === "prices" ? "is-active" : "" ?>">Прайс главной</a>
					<a href="/admin/?page=slider" class="<?= $page === "slider" ? "is-active" : "" ?>">Слайдер главной</a>
					<a href="/admin/?page=works" class="<?= $page === "works" ? "is-active" : "" ?>">Примеры работ</a>
				</div>
			</div>

			<a href="/admin/?page=settings" class="<?= $page === "settings" ? "is-active" : "" ?>">Настройки</a>
			<a href="/admin/?page=trash" class="<?= $page === "trash" ? "is-active" : "" ?>">Корзина<?= $trash ? " (" . count($trash) . ")" : "" ?></a>
			<a href="/" target="_blank" rel="noopener">Открыть сайт ↗</a>
		</nav>
		<button type="button" class="topbar__logout" id="logout">Выйти</button>
	</header>

	<main class="wrap">

	<?php if ($page === "service"): ?>

		<?php
		// редактор страницы услуги: услуга из индекса + её файл контента
		$editSlug = isset($_GET["slug"]) ? (string) $_GET["slug"] : "";
		$editSvc  = null;
		foreach ($services as $s) { if ($s["slug"] === $editSlug) { $editSvc = $s; break; } }
		$editPage = $editSvc ? json_read(service_path($editSlug)) : array();
		?>
		<?php if (!$editSvc || !$editPage): ?>
		<h1 class="page-title">Услуга не найдена</h1>
		<div class="empty">Такой услуги нет в списке. Возможно, её удалили. <a href="/admin/">Вернуться к списку</a></div>
		<?php else: ?>
		<?php require __DIR__ . "/views/service.php"; ?>
		<?php endif; ?>

	<?php elseif ($page === "prices"): ?>

		<?php $homePrices = json_read(data_path("prices")); ?>
		<?php require __DIR__ . "/views/home-prices.php"; ?>

	<?php elseif ($page === "slider"): ?>

		<?php $homeSlider = json_read(data_path("home-services")); ?>
		<?php require __DIR__ . "/views/home-slider.php"; ?>

	<?php elseif ($page === "works"): ?>

		<?php $works = json_read(data_path("works")); ?>
		<?php require __DIR__ . "/views/works.php"; ?>

	<?php elseif ($page === "settings"): ?>

		<?php
		// публичные данные сайта + действующий логин (хэш пароля в форму не отдаём)
		$site      = json_read(data_path("site"));
		$authCreds = auth_creds();
		?>
		<?php require __DIR__ . "/views/settings.php"; ?>

	<?php elseif ($page === "trash"): ?>

		<h1 class="page-title">Корзина</h1>
		<p class="page-hint">Удалённые услуги лежат здесь и не видны на сайте. Восстановление вернёт услугу в меню.</p>

		<?php if (!$trash): ?>
		<div class="empty">Корзина пуста</div>
		<?php else: ?>
		<table class="table">
			<thead>
				<tr><th>Услуга</th><th>Раздел меню</th><th>Удалена</th><th></th></tr>
			</thead>
			<tbody>
				<?php foreach ($trash as $slug => $m): ?>
				<tr>
					<td><?= h($m["name"]) ?><div class="table__slug">/<?= h($slug) ?>/</div></td>
					<td><?= h(isset($groupNames[$m["group"]]) ? $groupNames[$m["group"]] : "—") ?></td>
					<td><?= h($m["deleted_at"]) ?></td>
					<td class="table__actions">
						<button type="button" class="btn btn--small js-restore" data-slug="<?= h($slug) ?>" data-name="<?= h($m["name"]) ?>">Восстановить</button>
					</td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<?php endif; ?>

	<?php else: ?>

		<div class="page-head">
			<h1 class="page-title">Услуги</h1>
			<button type="button" class="btn btn--primary" id="svc-add-open">+ Добавить услугу</button>
		</div>
		<p class="page-hint">Галочки сохраняются сразу. Стрелки меняют порядок в выпадающем меню сайта.</p>

		<?php foreach ($groups as $g): ?>
		<h2 class="group-title"><?= h($g["title"]) ?></h2>
		<table class="table">
			<thead>
				<tr><th>Услуга</th><th class="table__c">Видима</th><th class="table__c">В футере</th><th class="table__c">Цены</th><th></th></tr>
			</thead>
			<tbody>
				<?php foreach ($services as $s): if ($s["group"] !== $g["id"]) { continue; } ?>
				<?php
					// бейдж «цены: нет» — быстро видно, где прайс ещё пустой
					$pg = json_read(service_path($s["slug"]));
					$hasPrices = !empty($pg["prices"]["groups"]);
				?>
				<tr data-slug="<?= h($s["slug"]) ?>">
					<td>
						<?= h($s["name"]) ?>
						<div class="table__slug"><a href="/<?= h($s["slug"]) ?>/" target="_blank" rel="noopener">/<?= h($s["slug"]) ?>/</a></div>
					</td>
					<td class="table__c"><input type="checkbox" class="js-toggle" data-field="visible" <?= !empty($s["visible"]) ? "checked" : "" ?>></td>
					<td class="table__c"><input type="checkbox" class="js-toggle" data-field="footer" <?= !empty($s["footer"]) ? "checked" : "" ?>></td>
					<td class="table__c"><?= $hasPrices ? "<span class=\"badge badge--ok\">есть</span>" : "<span class=\"badge\">нет</span>" ?></td>
					<td class="table__actions">
						<button type="button" class="icon-btn js-move" data-dir="up" title="Выше в меню">↑</button>
						<button type="button" class="icon-btn js-move" data-dir="down" title="Ниже в меню">↓</button>
						<a class="btn btn--small" href="/admin/?page=service&amp;slug=<?= h($s["slug"]) ?>">Редактировать</a>
						<button type="button" class="icon-btn icon-btn--danger js-delete" data-name="<?= h($s["name"]) ?>" title="В корзину">✕</button>
					</td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<?php endforeach; ?>

		<!-- форма добавления услуги -->
		<div class="modal" id="svc-add" aria-hidden="true">
			<div class="modal__overlay" data-modal-close></div>
			<form class="modal__dialog" id="svc-add-form" autocomplete="off">
				<div class="modal__title">Новая услуга</div>
				<label class="field">
					<span class="field__label">Название</span>
					<input type="text" name="name" placeholder="Например: Обработка от муравьёв" required>
				</label>
				<label class="field">
					<span class="field__label">Адрес страницы</span>
					<input type="text" name="slug" placeholder="obrabotka-ot-muravyov" required>
					<span class="field__hint">Заполняется сам из названия. Только латиница, цифры и дефис.</span>
				</label>
				<label class="field">
					<span class="field__label">Раздел меню</span>
					<select name="group">
						<?php foreach ($groups as $g): ?>
						<option value="<?= h($g["id"]) ?>"><?= h($g["title"]) ?></option>
						<?php endforeach; ?>
					</select>
				</label>
				<div class="modal__actions">
					<button type="button" class="btn" data-modal-close>Отмена</button>
					<button type="submit" class="btn btn--primary">Добавить</button>
				</div>
			</form>
		</div>

	<?php endif; ?>

	</main>

	<!-- модалка подтверждения: заголовок и текст подставляет JS -->
	<div class="modal" id="confirm" aria-hidden="true">
		<div class="modal__overlay" data-modal-close></div>
		<div class="modal__dialog">
			<div class="modal__title" id="confirm-title"></div>
			<div class="modal__text" id="confirm-text"></div>
			<div class="modal__actions">
				<button type="button" class="btn" data-modal-close>Отмена</button>
				<button type="button" class="btn btn--danger" id="confirm-yes"></button>
			</div>
		</div>
	</div>

<?php endif; ?>

	<!-- плашки «Сохранено» / ошибки -->
	<div class="toasts" id="toasts"></div>

	<script src="/admin/source/admin.js"></script>
</body>
</html>