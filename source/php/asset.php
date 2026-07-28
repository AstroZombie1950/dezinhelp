<?php
// Адрес статики: минифицированная версия, если она есть и не устарела, плюс
// метка времени файла. Хостинг отдаёт css/js с недельным кэшем — без метки
// посетитель неделю сидел бы на старой версии.
// Отдельный файл, потому что нужен и bootstrap.php, и самостоятельной 404.php.
function asset($path) {
	$root = dirname(__DIR__, 2);
	$min  = preg_replace('/\.(css|js)$/', '.min.$1', $path);
	if ($min !== $path && is_file($root . $min) && filemtime($root . $min) >= filemtime($root . $path)) {
		$path = $min;
	}
	$file = $root . $path;
	return is_file($file) ? $path . "?v=" . filemtime($file) : $path;
}
