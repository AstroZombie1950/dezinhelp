<?php
// Общие хелперы админки: пути, чтение/запись JSON, ответы.
// Запись всегда через json_write(): валидация → бэкап → атомарный rename.

// админка живёт только на основном домене: на городских поддоменах её нет,
// иначе это 79 лишних точек входа в логин
require_once __DIR__ . "/../../source/php/city.php";
if (city_resolve()["status"] !== "main") {
	require __DIR__ . "/../../404.php";
	exit;
}

define("DATA_DIR",   realpath(__DIR__ . "/../../data"));
define("TRASH_DIR",  DATA_DIR . "/.trash");
define("BACKUP_DIR", DATA_DIR . "/.backup");

// сколько бэкапов держим на каждый файл
define("BACKUP_KEEP", 10);

// экранирование вывода в HTML (та же h(), что на сайте, но админка живёт отдельно)
function h($s) { return htmlspecialchars((string) $s, ENT_QUOTES, "UTF-8"); }

// JSON-ответ экшена и выход; ошибки — человеческим текстом, их показывает плашка
function respond($ok, $data = array())
{
	header("Content-Type: application/json; charset=utf-8");
	echo json_encode(array_merge(array("ok" => $ok), $data), JSON_UNESCAPED_UNICODE);
	exit;
}

function fail($message) { respond(false, array("error" => $message)); }

// чтение JSON; $path — абсолютный путь
function json_read($path)
{
	if (!file_exists($path)) { return array(); }
	$json = json_decode(file_get_contents($path), true);
	return is_array($json) ? $json : array();
}

// запись JSON: табы вместо пробелов (правило проекта), бэкап старой версии,
// атомарная замена — оборванный запрос не оставит битый файл
function json_write($path, $data)
{
	$json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
	if ($json === false) { fail("Не получилось собрать JSON: " . json_last_error_msg()); }

	// json_encode умеет только 4 пробела — меняем каждый уровень на таб
	$json = preg_replace_callback("/^(?: {4})+/m", function ($m) {
		return str_repeat("\t", strlen($m[0]) / 4);
	}, $json);
	$json .= "\n";

	// контрольная распаковка: пишем только то, что читается обратно
	if (json_decode($json, true) === null) { fail("Файл не прошёл проверку, запись отменена"); }

	backup_file($path);

	$tmp = $path . "." . uniqid("tmp", true);
	if (file_put_contents($tmp, $json) === false) { fail("Не получилось записать файл на диск"); }
	if (!rename($tmp, $path)) {
		@unlink($tmp);
		fail("Не получилось заменить файл на диске");
	}
	return true;
}

// копия файла в data/.backup перед перезаписью; старые копии подчищаем
function backup_file($path)
{
	if (!file_exists($path)) { return; }
	if (!is_dir(BACKUP_DIR)) { @mkdir(BACKUP_DIR, 0755, true); }

	$base = basename($path);
	@copy($path, BACKUP_DIR . "/" . $base . "." . date("Ymd-His"));

	// держим только последние BACKUP_KEEP копий этого файла
	$copies = glob(BACKUP_DIR . "/" . $base . ".*");
	if ($copies && count($copies) > BACKUP_KEEP) {
		sort($copies); // имена с датой сортируются хронологически
		foreach (array_slice($copies, 0, count($copies) - BACKUP_KEEP) as $old) {
			@unlink($old);
		}
	}
}

// файлы данных, с которыми работает админка
function data_path($name)    { return DATA_DIR . "/" . basename($name) . ".json"; }
function service_path($slug) { return DATA_DIR . "/services/" . basename($slug) . ".json"; }
function trash_path($slug)   { return TRASH_DIR . "/" . basename($slug) . ".json"; }
function trash_meta_path()   { return TRASH_DIR . "/meta.json"; }

// слаг: строго то, что пропускает роутер в .htaccess
function slug_valid($slug) { return (bool) preg_match("/^[a-z0-9-]+$/", $slug); }

// проверка структуры прайса из формы: используется и услугами, и прайсом главной.
// пустые строки выбрасываются молча, подраздел без названия — ошибка
function prices_validate($in)
{
	if (!is_array($in)) { fail("Не получилось прочитать данные формы — обновите страницу"); }

	$out = array("visible" => !empty($in["visible"]), "groups" => array());
	foreach ((array) (isset($in["groups"]) ? $in["groups"] : array()) as $g) {
		$title = isset($g["title"]) ? trim($g["title"]) : "";
		if ($title === "") { fail("У каждого подраздела должно быть название"); }
		$items = array();
		foreach ((array) (isset($g["items"]) ? $g["items"] : array()) as $it) {
			$n = isset($it["name"]) ? trim($it["name"]) : "";
			$p = isset($it["price"]) ? trim($it["price"]) : "";
			if ($n === "" && $p === "") { continue; }
			if ($n === "") { fail("В подразделе «" . $title . "» есть строка без названия услуги"); }
			$items[] = array("name" => $n, "price" => $p);
		}
		$out["groups"][] = array(
			"visible" => !empty($g["visible"]),
			"title"   => $title,
			"items"   => $items,
		);
	}
	return $out;
}

// чистка HTML текстов услуги: белый список тегов, атрибуты срезаем.
// копипаст из Word приносит span/style/классы — здесь всё это отваливается,
// поэтому сломать вёрстку или пронести скрипт из админки нельзя
function sanitize_html($html)
{
	// script/style — вместе с содержимым, иначе strip_tags оставит их текст в абзаце
	$html = preg_replace("#<(script|style)\b[^>]*>.*?</\\1>#is", "", (string) $html);
	$html = strip_tags($html, "<p><h2><h3><strong><em><b><i><ul><ol><li><br><a>");

	// у ссылок оставляем только href с безопасной схемой
	$html = preg_replace_callback("/<a\b[^>]*>/i", function ($m) {
		if (preg_match("/href\s*=\s*(?:\"([^\"]*)\"|'([^']*)')/i", $m[0], $h)) {
			$url = trim(isset($h[2]) && $h[2] !== "" ? $h[2] : $h[1]);
			if (preg_match("#^(https?://|mailto:|tel:|/)#i", $url)) {
				return "<a href=\"" . htmlspecialchars($url, ENT_QUOTES, "UTF-8") . "\">";
			}
		}
		return "<a>";
	}, $html);

	// у остальных тегов атрибуты не нужны вовсе
	$html = preg_replace("/<(p|h2|h3|strong|em|b|i|ul|ol|li|br)\b[^>]*>/i", "<\$1>", $html);

	return trim($html);
}

// Приём картинки из формы ($_FILES["file"]) с сохранением в webp. Общий для всех
// загрузок админки; ошибки уходят через fail() с текстом для плашки.
// $square > 0 — кроп «cover» в квадрат этой стороны на белом фоне (карточки);
// $square = 0 — даунскейл до 900px по ширине, прозрачность сохраняется (png без фона).
// Возвращает путь файла от корня сайта.
function upload_image_webp($subdir, $prefix, $square, $quality)
{
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

	$sw = imagesx($src); $sh = imagesy($src);
	if ($square > 0) {
		// кроп «cover»: масштабируем по меньшей стороне и вырезаем центр;
		// прозрачность png заливаем белым — карточки на сайте на светлом фоне
		$side = min($sw, $sh);
		$sx = (int) (($sw - $side) / 2);
		$sy = (int) (($sh - $side) / 2);

		$dst = imagecreatetruecolor($square, $square);
		$white = imagecolorallocate($dst, 255, 255, 255);
		imagefill($dst, 0, 0, $white);
		imagecopyresampled($dst, $src, 0, 0, $sx, $sy, $square, $square, $side, $side);
	} else {
		// даунскейл по ширине, пропорции сохраняем; апскейл не делаем
		$maxW = 900;
		if ($sw > $maxW) { $nw = $maxW; $nh = (int) round($sh * $maxW / $sw); }
		else { $nw = $sw; $nh = $sh; }

		$dst = imagecreatetruecolor($nw, $nh);
		imagealphablending($dst, false);
		imagesavealpha($dst, true);
		$transparent = imagecolorallocatealpha($dst, 0, 0, 0, 127);
		imagefilledrectangle($dst, 0, 0, $nw, $nh, $transparent);
		imagecopyresampled($dst, $src, 0, 0, 0, 0, $nw, $nh, $sw, $sh);
	}
	imagedestroy($src);

	$dir = __DIR__ . "/../../source/img/" . $subdir;
	if (!is_dir($dir)) { @mkdir($dir, 0755, true); }
	$dir = realpath($dir);
	if (!$dir) { fail("Папка для фотографий не найдена на сервере"); }

	$name = $prefix . date("Ymd-His") . "-" . substr(bin2hex(random_bytes(3)), 0, 4) . ".webp";
	if (!imagewebp($dst, $dir . "/" . $name, $quality)) {
		imagedestroy($dst);
		fail("Не получилось сохранить фотографию на сервере");
	}
	imagedestroy($dst);

	return "/source/img/" . $subdir . "/" . $name;
}

// болванка контента новой услуги: страница откроется сразу, тексты дозаполнят
function service_blank($name)
{
	return array(
		"seo" => array(
			"title"       => $name . " — МосКомДез",
			"description" => "",
			"keywords"    => "",
		),
		"h1"         => $name,
		"hero_note"  => "",
		"intro_html" => "",
		"seo_html"   => "",
		"prices"     => array("visible" => true, "groups" => array()),
		"blocks"     => array(
			"gallery"      => true,
			"methods"      => true,
			"process"      => true,
			"certificates" => true,
			"veterans"     => true,
			"coverage"     => true,
			"quiz"         => true,
			"services"     => true,
			"faq"          => true,
		),
		"faq" => array(),
	);
}