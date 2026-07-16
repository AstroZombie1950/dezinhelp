<?php
// Чтение данных из /data/*.json. Сюда пишет админка, отсюда читает вёрстка.
// Вёрстка не знает, откуда взялись данные — при смене хранилища правится только этот файл.

function data_load($name)
{
	$file = __DIR__ . "/../../data/" . basename($name) . ".json";
	if (!file_exists($file)) {
		return array();
	}
	$json = json_decode(file_get_contents($file), true);
	return is_array($json) ? $json : array();
}

// услуги одной группы меню, скрытые отсеиваются
function services_in_group($services, $groupId)
{
	$out = array();
	foreach ($services as $s) {
		if ($s["group"] === $groupId && !empty($s["visible"])) {
			$out[] = $s;
		}
	}
	return $out;
}

// одна услуга по слагу; скрытые не отдаём — для роутера это 404
function service_find($services, $slug)
{
	foreach ($services as $s) {
		if ($s["slug"] === $slug && !empty($s["visible"])) {
			return $s;
		}
	}
	return null;
}

// путь к файлу контента услуги; basename режет попытки выйти из папки
function service_file($slug)
{
	return __DIR__ . "/../../data/services/" . basename($slug) . ".json";
}

// контент страницы услуги: data/services/<slug>.json
function service_page($slug)
{
	$file = service_file($slug);
	if (!file_exists($file)) {
		return array();
	}
	$json = json_decode(file_get_contents($file), true);
	return is_array($json) ? $json : array();
}

// дата правки контента — lastmod для sitemap, обновляется админкой сама
function service_mtime($slug)
{
	$file = service_file($slug);
	return file_exists($file) ? filemtime($file) : time();
}

// услуги с флагом footer — короткий список ссылок в подвале
function services_footer($services)
{
	$out = array();
	foreach ($services as $s) {
		if (!empty($s["footer"]) && !empty($s["visible"])) {
			$out[] = $s;
		}
	}
	return $out;
}

// раскладка подразделов прайса по двум колонкам: подраздел уходит в ту, что короче.
// на текущих данных даёт вид донора (1 высокая слева + 2 справа) и не ломается,
// если в админке добавят/удалят подраздел
function prices_split($groups)
{
	$cols = array(array(), array());
	$rows = array(0, 0);
	foreach ($groups as $g) {
		$i = $rows[1] < $rows[0] ? 1 : 0;
		$cols[$i][] = $g;
		$rows[$i] += count($g["items"]) + 1; // +1 — строка-шапка подраздела
	}
	return $cols;
}