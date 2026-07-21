<?php
// Приёмник действий админки. Всё — POST с CSRF-токеном, ответы — JSON.
// Тексты ошибок пишутся для человека: их показывает красная плашка как есть.

require __DIR__ . "/lib.php";
require __DIR__ . "/auth.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") { fail("Нужен POST-запрос"); }

$action = isset($_POST["action"]) ? (string) $_POST["action"] : "";

// --- вход/выход доступны без авторизации ---
if ($action === "login") {
	$user = isset($_POST["user"]) ? trim($_POST["user"]) : "";
	$pass = isset($_POST["pass"]) ? (string) $_POST["pass"] : "";
	if (!auth_login($user, $pass)) { fail("Неверный логин или пароль"); }
	respond(true);
}

if (!auth_logged_in()) { fail("Сессия закончилась — войдите заново"); }
if (!auth_csrf_check(isset($_POST["csrf"]) ? $_POST["csrf"] : "")) {
	fail("Страница устарела — обновите её и повторите действие");
}

if ($action === "logout") {
	auth_logout();
	respond(true);
}

// дальше работаем со списком услуг
$servicesFile = data_path("services");
$index        = json_read($servicesFile);
$services     = isset($index["services"]) ? $index["services"] : array();
$groups       = isset($index["groups"]) ? $index["groups"] : array();

// услуга по слагу: возвращает индекс в массиве или -1
function svc_index($services, $slug)
{
	foreach ($services as $i => $s) {
		if ($s["slug"] === $slug) { return $i; }
	}
	return -1;
}

switch ($action) {

// галочки «видима» и «в футере» — сохраняются сразу по клику
case "svc_toggle":
	$slug  = isset($_POST["slug"]) ? (string) $_POST["slug"] : "";
	$field = isset($_POST["field"]) ? (string) $_POST["field"] : "";
	$value = !empty($_POST["value"]);

	if (!in_array($field, array("visible", "footer"), true)) { fail("Неизвестное поле"); }
	$i = svc_index($services, $slug);
	if ($i < 0) { fail("Услуга не найдена — обновите страницу"); }

	$index["services"][$i][$field] = $value;
	json_write($servicesFile, $index);
	respond(true);

// стрелки ↑↓ — меняем местами с соседом той же группы меню
case "svc_move":
	$slug = isset($_POST["slug"]) ? (string) $_POST["slug"] : "";
	$dir  = (isset($_POST["dir"]) && $_POST["dir"] === "up") ? -1 : 1;

	$i = svc_index($services, $slug);
	if ($i < 0) { fail("Услуга не найдена — обновите страницу"); }

	// ближайший сосед той же группы в нужном направлении
	$j = -1;
	for ($k = $i + $dir; $k >= 0 && $k < count($services); $k += $dir) {
		if ($services[$k]["group"] === $services[$i]["group"]) { $j = $k; break; }
	}
	if ($j < 0) { respond(true, array("noop" => true)); } // уже крайняя — не ошибка

	$tmp = $index["services"][$i];
	$index["services"][$i] = $index["services"][$j];
	$index["services"][$j] = $tmp;
	json_write($servicesFile, $index);
	respond(true);

// новая услуга: строка в индексе + болванка контента; в меню попадает сразу
case "svc_add":
	$name  = isset($_POST["name"]) ? trim($_POST["name"]) : "";
	$slug  = isset($_POST["slug"]) ? strtolower(trim($_POST["slug"])) : "";
	$group = isset($_POST["group"]) ? (string) $_POST["group"] : "";

	if ($name === "") { fail("Заполните название услуги"); }
	if (!slug_valid($slug)) { fail("Адрес страницы: только строчные латинские буквы, цифры и дефис"); }
	if (svc_index($services, $slug) >= 0) { fail("Такой адрес уже занят другой услугой"); }
	if (file_exists(trash_path($slug))) { fail("Услуга с таким адресом лежит в корзине — восстановите её или выберите другой адрес"); }

	$groupOk = false;
	foreach ($groups as $g) { if ($g["id"] === $group) { $groupOk = true; break; } }
	if (!$groupOk) { fail("Выберите раздел меню"); }

	// в индекс — в конец своей группы, чтобы встала рядом с соседями по меню
	$row = array("name" => $name, "slug" => $slug, "group" => $group, "visible" => true, "footer" => false);
	$pos = count($services);
	for ($k = count($services) - 1; $k >= 0; $k--) {
		if ($services[$k]["group"] === $group) { $pos = $k + 1; break; }
	}
	array_splice($index["services"], $pos, 0, array($row));

	json_write($servicesFile, $index);
	json_write(service_path($slug), service_blank($name));
	respond(true, array("slug" => $slug));

// удаление: сперва dry-прогон — говорим, что заденет; по подтверждению переносим в корзину
case "svc_delete":
	$slug = isset($_POST["slug"]) ? (string) $_POST["slug"] : "";
	$dry  = !empty($_POST["dry"]);

	$i = svc_index($services, $slug);
	if ($i < 0) { fail("Услуга не найдена — обновите страницу"); }
	$svc = $services[$i];

	// где на услугу ссылаются — для предупреждения и для чистки
	$home = json_read(data_path("home-services"));
	$quiz = json_read(data_path("quiz"));

	$inSlider = false;
	foreach ((array) (isset($home["cards"]) ? $home["cards"] : array()) as $c) {
		if (isset($c["slug"]) && $c["slug"] === $slug) { $inSlider = true; break; }
	}
	$inQuiz = false;
	foreach ((array) (isset($quiz["steps"]) ? $quiz["steps"] : array()) as $st) {
		foreach ((array) (isset($st["options"]) ? $st["options"] : array()) as $o) {
			if (!empty($o["slugs"]) && in_array($slug, $o["slugs"], true)) { $inQuiz = true; break 2; }
		}
	}

	if ($dry) {
		respond(true, array("name" => $svc["name"], "slider" => $inSlider, "quiz" => $inQuiz));
	}

	// контент — в корзину (файла может не быть, это не ошибка)
	if (!is_dir(TRASH_DIR)) { @mkdir(TRASH_DIR, 0755, true); }
	if (file_exists(service_path($slug))) {
		backup_file(service_path($slug));
		if (!rename(service_path($slug), trash_path($slug))) { fail("Не получилось перенести файл в корзину"); }
	}

	// мета корзины: что и когда удалили, чтобы восстановить на то же место
	$meta = json_read(trash_meta_path());
	$meta[$slug] = array(
		"name"       => $svc["name"],
		"group"      => $svc["group"],
		"visible"    => !empty($svc["visible"]),
		"footer"     => !empty($svc["footer"]),
		"deleted_at" => date("Y-m-d H:i"),
	);
	json_write(trash_meta_path(), $meta);

	// из индекса
	array_splice($index["services"], $i, 1);
	json_write($servicesFile, $index);

	// чистим ссылку в слайдере главной: карточка остаётся, ссылка пустеет
	if ($inSlider) {
		foreach ($home["cards"] as $k => $c) {
			if (isset($c["slug"]) && $c["slug"] === $slug) { $home["cards"][$k]["slug"] = ""; }
		}
		json_write(data_path("home-services"), $home);
	}

	// чистим предвыбор в квизе
	if ($inQuiz) {
		foreach ($quiz["steps"] as $si => $st) {
			foreach ((array) (isset($st["options"]) ? $st["options"] : array()) as $oi => $o) {
				if (!empty($o["slugs"]) && in_array($slug, $o["slugs"], true)) {
					$quiz["steps"][$si]["options"][$oi]["slugs"] = array_values(array_diff($o["slugs"], array($slug)));
				}
			}
		}
		json_write(data_path("quiz"), $quiz);
	}

	respond(true);

// восстановление из корзины: услуга возвращается в меню, ссылки — нет (о чём предупреждаем)
case "svc_restore":
	$slug = isset($_POST["slug"]) ? (string) $_POST["slug"] : "";
	$meta = json_read(trash_meta_path());
	if (empty($meta[$slug])) { fail("Записи в корзине нет — обновите страницу"); }
	if (svc_index($services, $slug) >= 0) { fail("Услуга с таким адресом уже есть в списке"); }

	$m = $meta[$slug];

	// группа могла исчезнуть, пока услуга лежала в корзине — тогда в первую
	$groupId = $m["group"];
	$groupOk = false;
	foreach ($groups as $g) { if ($g["id"] === $groupId) { $groupOk = true; break; } }
	if (!$groupOk && $groups) { $groupId = $groups[0]["id"]; }

	$row = array(
		"name"    => $m["name"],
		"slug"    => $slug,
		"group"   => $groupId,
		"visible" => !empty($m["visible"]),
		"footer"  => !empty($m["footer"]),
	);
	$pos = count($services);
	for ($k = count($services) - 1; $k >= 0; $k--) {
		if ($services[$k]["group"] === $groupId) { $pos = $k + 1; break; }
	}
	array_splice($index["services"], $pos, 0, array($row));
	json_write($servicesFile, $index);

	// контент обратно из корзины
	if (file_exists(trash_path($slug))) {
		if (!rename(trash_path($slug), service_path($slug))) { fail("Не получилось вернуть файл из корзины"); }
	}

	unset($meta[$slug]);
	json_write(trash_meta_path(), $meta);
	respond(true);

// --- редактор страницы услуги: каждая вкладка сохраняется своим экшеном ---

// вкладка SEO: мета-теги и заголовок страницы
case "svc_save_seo":
	$slug = isset($_POST["slug"]) ? (string) $_POST["slug"] : "";
	if (svc_index($services, $slug) < 0) { fail("Услуга не найдена — обновите страницу"); }
	$pg = json_read(service_path($slug));
	if (!$pg) { fail("Файл страницы не найден"); }

	$h1 = isset($_POST["h1"]) ? trim($_POST["h1"]) : "";
	if ($h1 === "") { fail("Заполните заголовок страницы (H1)"); }

	$pg["seo"]["title"]       = isset($_POST["title"]) ? trim($_POST["title"]) : "";
	$pg["seo"]["description"] = isset($_POST["description"]) ? trim($_POST["description"]) : "";
	$pg["seo"]["keywords"]    = isset($_POST["keywords"]) ? trim($_POST["keywords"]) : "";
	$pg["h1"]        = $h1;
	$pg["hero_note"] = isset($_POST["hero_note"]) ? trim($_POST["hero_note"]) : "";

	json_write(service_path($slug), $pg);
	respond(true);

// вкладка «Тексты»: вводный и SEO-текст, оба через чистку тегов
case "svc_save_texts":
	$slug = isset($_POST["slug"]) ? (string) $_POST["slug"] : "";
	if (svc_index($services, $slug) < 0) { fail("Услуга не найдена — обновите страницу"); }
	$pg = json_read(service_path($slug));
	if (!$pg) { fail("Файл страницы не найден"); }

	$pg["intro_html"] = sanitize_html(isset($_POST["intro_html"]) ? $_POST["intro_html"] : "");
	$pg["seo_html"]   = sanitize_html(isset($_POST["seo_html"]) ? $_POST["seo_html"] : "");

	json_write(service_path($slug), $pg);
	// возвращаем вычищенный вариант — редактор покажет, что реально сохранилось
	respond(true, array("intro_html" => $pg["intro_html"], "seo_html" => $pg["seo_html"]));

// вкладка «Блоки»: 9 тумблеров секций страницы
case "svc_save_blocks":
	$slug = isset($_POST["slug"]) ? (string) $_POST["slug"] : "";
	if (svc_index($services, $slug) < 0) { fail("Услуга не найдена — обновите страницу"); }
	$pg = json_read(service_path($slug));
	if (!$pg) { fail("Файл страницы не найден"); }

	$known = array("gallery", "methods", "process", "certificates", "veterans", "coverage", "quiz", "services", "faq");
	$on = isset($_POST["blocks"]) ? (array) $_POST["blocks"] : array();
	foreach ($known as $b) {
		$pg["blocks"][$b] = in_array($b, $on, true);
	}

	json_write(service_path($slug), $pg);
	respond(true);

// вкладка «Цены»: клиент шлёт структуру одним JSON-полем, сервер проверяет её по форме
case "svc_save_prices":
	$slug = isset($_POST["slug"]) ? (string) $_POST["slug"] : "";
	if (svc_index($services, $slug) < 0) { fail("Услуга не найдена — обновите страницу"); }
	$pg = json_read(service_path($slug));
	if (!$pg) { fail("Файл страницы не найден"); }

	$in = json_decode(isset($_POST["prices_json"]) ? $_POST["prices_json"] : "", true);
	$pg["prices"] = prices_validate($in);
	json_write(service_path($slug), $pg);
	respond(true);

// прайс главной страницы: та же структура + свой заголовок блока
case "home_prices_save":
	$in = json_decode(isset($_POST["prices_json"]) ? $_POST["prices_json"] : "", true);
	$out = prices_validate($in);

	// порядок ключей файла сохраняем: visible, title, groups
	$data = array(
		"visible" => $out["visible"],
		"title"   => isset($_POST["title"]) ? trim($_POST["title"]) : "",
		"groups"  => $out["groups"],
	);
	json_write(data_path("prices"), $data);
	respond(true);

// вкладка FAQ: параллельные массивы вопрос/ответ, порядок — как в форме
case "svc_save_faq":
	$slug = isset($_POST["slug"]) ? (string) $_POST["slug"] : "";
	if (svc_index($services, $slug) < 0) { fail("Услуга не найдена — обновите страницу"); }
	$pg = json_read(service_path($slug));
	if (!$pg) { fail("Файл страницы не найден"); }

	$qs = isset($_POST["q"]) ? (array) $_POST["q"] : array();
	$as = isset($_POST["a"]) ? (array) $_POST["a"] : array();
	$faq = array();
	foreach ($qs as $i => $q) {
		$q = trim((string) $q);
		$a = trim((string) (isset($as[$i]) ? $as[$i] : ""));
		if ($q === "" && $a === "") { continue; } // пустая пара — просто лишняя карточка
		if ($q === "" || $a === "") { fail("Вопрос №" . ($i + 1) . " заполнен наполовину — допишите или удалите его"); }
		$faq[] = array("q" => $q, "a" => $a);
	}

	$pg["faq"] = $faq;
	json_write(service_path($slug), $pg);
	respond(true);

// слайдер главной: вся форма приходит одним JSON-полем
case "home_slider_save":
	$in = json_decode(isset($_POST["slider_json"]) ? $_POST["slider_json"] : "", true);
	if (!is_array($in)) { fail("Не получилось прочитать данные формы — обновите страницу"); }

	$cards = array();
	foreach ((array) (isset($in["cards"]) ? $in["cards"] : array()) as $n => $c) {
		$title = isset($c["title"]) ? trim($c["title"]) : "";
		if ($title === "") { fail("У карточки №" . ($n + 1) . " не заполнен заголовок"); }

		$image = isset($c["image"]) ? trim($c["image"]) : "";
		if ($image === "") { fail("У карточки «" . $title . "» нет фотографии — загрузите её"); }
		if (strpos($image, "/source/img/") !== 0) { fail("У карточки «" . $title . "» неправильный путь к фотографии"); }

		// привязка только к существующей услуге — битых ссылок на сайте не оставляем
		$cslug = isset($c["slug"]) ? trim($c["slug"]) : "";
		if ($cslug !== "" && svc_index($services, $cslug) < 0) {
			fail("Карточка «" . $title . "» привязана к услуге, которой уже нет — выберите другую");
		}

		// пункты цен: по строке на пункт, пустые строки выбрасываем
		$items = array();
		foreach ((array) (isset($c["items"]) ? $c["items"] : array()) as $line) {
			$line = trim((string) $line);
			if ($line !== "") { $items[] = $line; }
		}

		$cards[] = array(
			"image"    => $image,
			"title"    => $title,
			"subtitle" => isset($c["subtitle"]) ? trim($c["subtitle"]) : "",
			"items"    => $items,
			"from"     => isset($c["from"]) ? trim($c["from"]) : "",
			"slug"     => $cslug,
		);
	}

	$data = array(
		"visible" => !empty($in["visible"]),
		"title"   => isset($in["title"]) ? trim($in["title"]) : "",
		"cards"   => $cards,
	);
	json_write(data_path("home-services"), $data);
	respond(true);

// загрузка фото карточки: любой jpeg/png/webp кропается в квадрат 400×400 и жмётся в webp
case "home_slider_upload":
	if (empty($_FILES["file"]) || $_FILES["file"]["error"] === UPLOAD_ERR_INI_SIZE || $_FILES["file"]["error"] === UPLOAD_ERR_FORM_SIZE) {
		fail("Файл слишком большой — загрузите фото до 10 МБ");
	}
	if ($_FILES["file"]["error"] !== UPLOAD_ERR_OK || !is_uploaded_file($_FILES["file"]["tmp_name"])) {
		fail("Не получилось загрузить файл — попробуйте ещё раз");
	}
	if ($_FILES["file"]["size"] > 10 * 1024 * 1024) { fail("Файл слишком большой — загрузите фото до 10 МБ"); }

	$info = getimagesize($_FILES["file"]["tmp_name"]);
	if (!$info) { fail("Это не похоже на картинку — нужен файл JPG, PNG или WebP"); }

	switch ($info[2]) {
		case IMAGETYPE_JPEG: $src = imagecreatefromjpeg($_FILES["file"]["tmp_name"]); break;
		case IMAGETYPE_PNG:  $src = imagecreatefrompng($_FILES["file"]["tmp_name"]); break;
		case IMAGETYPE_WEBP: $src = imagecreatefromwebp($_FILES["file"]["tmp_name"]); break;
		default: fail("Такой формат не подходит — нужен файл JPG, PNG или WebP");
	}
	if (!$src) { fail("Не получилось прочитать картинку — попробуйте другой файл"); }

	// кроп «cover»: масштабируем по меньшей стороне и вырезаем центр
	$sw = imagesx($src); $sh = imagesy($src);
	$side = min($sw, $sh);
	$sx = (int) (($sw - $side) / 2);
	$sy = (int) (($sh - $side) / 2);

	$dst = imagecreatetruecolor(400, 400);
	// прозрачность png заливаем белым — карточки на сайте на светлом фоне
	$white = imagecolorallocate($dst, 255, 255, 255);
	imagefill($dst, 0, 0, $white);
	imagecopyresampled($dst, $src, 0, 0, $sx, $sy, 400, 400, $side, $side);
	imagedestroy($src);

	$name = "u" . date("Ymd-His") . "-" . substr(bin2hex(random_bytes(3)), 0, 4) . ".webp";
	$dir  = realpath(__DIR__ . "/../../source/img/our_services");
	if (!$dir) { fail("Папка для фотографий не найдена на сервере"); }
	if (!imagewebp($dst, $dir . "/" . $name, 82)) {
		imagedestroy($dst);
		fail("Не получилось сохранить фотографию на сервере");
	}
	imagedestroy($dst);

	respond(true, array("path" => "/source/img/our_services/" . $name));

// примеры работ: вся форма приходит одним JSON-полем
case "works_save":
	$in = json_decode(isset($_POST["works_json"]) ? $_POST["works_json"] : "", true);
	if (!is_array($in)) { fail("Не получилось прочитать данные формы — обновите страницу"); }

	$items = array();
	foreach ((array) (isset($in["items"]) ? $in["items"] : array()) as $n => $w) {
		$text = isset($w["text"]) ? trim($w["text"]) : "";
		if ($text === "") { fail("У примера №" . ($n + 1) . " не заполнен текст"); }

		// оба фото обязательны — сравнивать иначе нечего
		$before = isset($w["before"]) ? trim($w["before"]) : "";
		$after  = isset($w["after"]) ? trim($w["after"]) : "";
		if ($before === "" || $after === "") { fail("У примера «" . $text . "» должны быть оба фото — до и после"); }
		if (strpos($before, "/source/img/") !== 0 || strpos($after, "/source/img/") !== 0) {
			fail("У примера «" . $text . "» неправильный путь к фото");
		}

		// привязка только к существующей услуге — битых ссылок на сайте не оставляем
		$cslug = isset($w["slug"]) ? trim($w["slug"]) : "";
		if ($cslug !== "" && svc_index($services, $cslug) < 0) {
			fail("Пример «" . $text . "» привязан к услуге, которой уже нет — выберите другую");
		}

		$items[] = array(
			"text"     => $text,
			"year"     => isset($w["year"]) ? trim($w["year"]) : "",
			"location" => isset($w["location"]) ? trim($w["location"]) : "",
			"slug"     => $cslug,
			"before"   => $before,
			"after"    => $after,
		);
	}

	// порядок ключей файла сохраняем: visible, title, items
	$data = array(
		"visible" => !empty($in["visible"]),
		"title"   => isset($in["title"]) ? trim($in["title"]) : "",
		"items"   => $items,
	);
	json_write(data_path("works"), $data);
	respond(true);

// загрузка фото примера: любой jpeg/png/webp кропается в квадрат 640×640 и жмётся в webp
case "works_upload":
	if (empty($_FILES["file"]) || $_FILES["file"]["error"] === UPLOAD_ERR_INI_SIZE || $_FILES["file"]["error"] === UPLOAD_ERR_FORM_SIZE) {
		fail("Файл слишком большой — загрузите фото до 10 МБ");
	}
	if ($_FILES["file"]["error"] !== UPLOAD_ERR_OK || !is_uploaded_file($_FILES["file"]["tmp_name"])) {
		fail("Не получилось загрузить файл — попробуйте ещё раз");
	}
	if ($_FILES["file"]["size"] > 10 * 1024 * 1024) { fail("Файл слишком большой — загрузите фото до 10 МБ"); }

	$info = getimagesize($_FILES["file"]["tmp_name"]);
	if (!$info) { fail("Это не похоже на картинку — нужен файл JPG, PNG или WebP"); }

	switch ($info[2]) {
		case IMAGETYPE_JPEG: $src = imagecreatefromjpeg($_FILES["file"]["tmp_name"]); break;
		case IMAGETYPE_PNG:  $src = imagecreatefrompng($_FILES["file"]["tmp_name"]); break;
		case IMAGETYPE_WEBP: $src = imagecreatefromwebp($_FILES["file"]["tmp_name"]); break;
		default: fail("Такой формат не подходит — нужен файл JPG, PNG или WebP");
	}
	if (!$src) { fail("Не получилось прочитать картинку — попробуйте другой файл"); }

	// кроп «cover»: масштабируем по меньшей стороне и вырезаем центр
	$sw = imagesx($src); $sh = imagesy($src);
	$side = min($sw, $sh);
	$sx = (int) (($sw - $side) / 2);
	$sy = (int) (($sh - $side) / 2);

	$dst = imagecreatetruecolor(640, 640);
	$white = imagecolorallocate($dst, 255, 255, 255);
	imagefill($dst, 0, 0, $white);
	imagecopyresampled($dst, $src, 0, 0, $sx, $sy, 640, 640, $side, $side);
	imagedestroy($src);

	$name = "w" . date("Ymd-His") . "-" . substr(bin2hex(random_bytes(3)), 0, 4) . ".webp";
	$dir  = realpath(__DIR__ . "/../../source/img/works");
	if (!$dir) { fail("Папка для фотографий не найдена на сервере"); }
	if (!imagewebp($dst, $dir . "/" . $name, 82)) {
		imagedestroy($dst);
		fail("Не получилось сохранить фотографию на сервере");
	}
	imagedestroy($dst);

	respond(true, array("path" => "/source/img/works/" . $name));

// вкладка «Герой»: цена в плашке + пути картинок (герой и текст)
case "svc_save_hero":
	$slug = isset($_POST["slug"]) ? (string) $_POST["slug"] : "";
	if (svc_index($services, $slug) < 0) { fail("Услуга не найдена — обновите страницу"); }
	$pg = json_read(service_path($slug));
	if (!$pg) { fail("Файл страницы не найден"); }

	$pg["hero_price"] = isset($_POST["hero_price"]) ? trim($_POST["hero_price"]) : "";

	// пути принимаем только из нашей папки; пусто = сброс на дефолт
	$hi = isset($_POST["hero_image"]) ? trim($_POST["hero_image"]) : "";
	$ti = isset($_POST["text_image"]) ? trim($_POST["text_image"]) : "";
	$pg["hero_image"] = (strpos($hi, "/source/img/") === 0) ? $hi : "";
	$pg["text_image"] = (strpos($ti, "/source/img/") === 0) ? $ti : "";

	json_write(service_path($slug), $pg);
	respond(true);

// загрузка картинки услуги (герой / текст): даунскейл до 900px по ширине без кропа,
// прозрачность сохраняем (png без фона для текста), результат — webp
case "svc_image_upload":
	if (empty($_FILES["file"]) || $_FILES["file"]["error"] === UPLOAD_ERR_INI_SIZE || $_FILES["file"]["error"] === UPLOAD_ERR_FORM_SIZE) {
		fail("Файл слишком большой — загрузите фото до 10 МБ");
	}
	if ($_FILES["file"]["error"] !== UPLOAD_ERR_OK || !is_uploaded_file($_FILES["file"]["tmp_name"])) {
		fail("Не получилось загрузить файл — попробуйте ещё раз");
	}
	if ($_FILES["file"]["size"] > 10 * 1024 * 1024) { fail("Файл слишком большой — загрузите фото до 10 МБ"); }

	$info = getimagesize($_FILES["file"]["tmp_name"]);
	if (!$info) { fail("Это не похоже на картинку — нужен файл JPG, PNG или WebP"); }

	switch ($info[2]) {
		case IMAGETYPE_JPEG: $src = imagecreatefromjpeg($_FILES["file"]["tmp_name"]); break;
		case IMAGETYPE_PNG:  $src = imagecreatefrompng($_FILES["file"]["tmp_name"]); break;
		case IMAGETYPE_WEBP: $src = imagecreatefromwebp($_FILES["file"]["tmp_name"]); break;
		default: fail("Такой формат не подходит — нужен файл JPG, PNG или WebP");
	}
	if (!$src) { fail("Не получилось прочитать картинку — попробуйте другой файл"); }

	// даунскейл по ширине, пропорции сохраняем; апскейл не делаем
	$sw = imagesx($src); $sh = imagesy($src);
	$maxW = 900;
	if ($sw > $maxW) { $nw = $maxW; $nh = (int) round($sh * $maxW / $sw); }
	else { $nw = $sw; $nh = $sh; }

	$dst = imagecreatetruecolor($nw, $nh);
	// сохраняем прозрачность (для png без фона)
	imagealphablending($dst, false);
	imagesavealpha($dst, true);
	$transparent = imagecolorallocatealpha($dst, 0, 0, 0, 127);
	imagefilledrectangle($dst, 0, 0, $nw, $nh, $transparent);
	imagecopyresampled($dst, $src, 0, 0, 0, 0, $nw, $nh, $sw, $sh);
	imagedestroy($src);

	$dir = __DIR__ . "/../../source/img/services";
	if (!is_dir($dir)) { @mkdir($dir, 0755, true); }
	$dir = realpath($dir);
	if (!$dir) { fail("Папка для фотографий не найдена на сервере"); }

	$name = "s" . date("Ymd-His") . "-" . substr(bin2hex(random_bytes(3)), 0, 4) . ".webp";
	if (!imagewebp($dst, $dir . "/" . $name, 84)) {
		imagedestroy($dst);
		fail("Не получилось сохранить фотографию на сервере");
	}
	imagedestroy($dst);

	respond(true, array("path" => "/source/img/services/" . $name));

default:
	fail("Неизвестное действие");
}