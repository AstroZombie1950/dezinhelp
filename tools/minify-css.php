<?php
// Минификация css: комментарии, лишние пробелы и последняя `;` в блоке.
// Ничего не переписывает в самих правилах — только съедает пустоту, поэтому
// результат безопасно отдавать вместо исходника.
//
// Запуск:  php tools/minify-css.php
// Кладёт  source/css/main.min.css  рядом с исходником. Страницы подхватят его
// сами (asset() в bootstrap.php), но только пока он свежее main.css —
// забыли пересобрать, значит отдастся исходный файл, а не устаревший минифай.

$src = dirname(__DIR__) . "/source/css/main.css";
$dst = dirname(__DIR__) . "/source/css/main.min.css";

$css = file_get_contents($src);
if ($css === false) {
	fwrite(STDERR, "не читается $src\n");
	exit(1);
}

$before = strlen($css);

// комментарии /* ... */ целиком
$css = preg_replace('!/\*.*?\*/!s', "", $css);
// любые серии пробелов и переводов строк — в один пробел
$css = preg_replace('/\s+/', " ", $css);
// пробелы вокруг структурных символов не нужны
$css = preg_replace('/\s*([{};,])\s*/', '$1', $css);
// последняя `;` перед закрывающей скобкой
$css = str_replace(";}", "}", $css);
$css = trim($css);

file_put_contents($dst, $css);

printf("%s: %d → %d байт (-%d%%)\n", basename($dst), $before, strlen($css), round((1 - strlen($css) / $before) * 100));
