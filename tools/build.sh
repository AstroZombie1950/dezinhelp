#!/bin/sh
# Сборка минифицированной статики перед выкладкой на хостинг.
#
#   sh tools/build.sh
#
# Делает source/css/main.min.css и source/js/main.min.js. Страницы подставляют
# их сами (asset() в source/php/bootstrap.php) — но только если минифай свежее
# исходника. Забыли пересобрать после правки — отдастся исходный файл, то есть
# сайт останется рабочим, просто чуть тяжелее. Оба min-файла лежат в гите,
# заливать на сервер их нужно вместе с исходниками.
#
# CSS собирает свой php-скрипт (без зависимостей), JS — terser через npx,
# поэтому для JS нужен node. Нет node — main.min.js просто не обновится.

set -e
cd "$(dirname "$0")/.."

php tools/minify-css.php

if command -v npx >/dev/null 2>&1; then
	npx -y terser@5 source/js/main.js -c -m -o source/js/main.min.js
	echo "main.min.js: $(wc -c < source/js/main.js) → $(wc -c < source/js/main.min.js) байт"
else
	echo "npx не найден — main.min.js не пересобран (сайт отдаст main.js)"
fi
