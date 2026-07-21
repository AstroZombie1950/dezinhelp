<?php
// geo-test.php — полигон для замера точности геолокации.
// Ни к чему не привязан, ничего не отправляет и не сохраняет: заходишь на /geotest.php
// и видишь, что выдал каждый метод. Только для тестов, из индексации закрыт (noindex).
// Точный (браузерный) метод работает ТОЛЬКО по HTTPS — значит на проде.

// экранирование вывода
function e($s) { return htmlspecialchars((string) $s, ENT_QUOTES, "UTF-8"); }

// маленький http-GET: curl, если есть, иначе file_get_contents. Возврат: строка или null
function http_get($url, $timeout)
{
	if (function_exists("curl_init")) {
		$ch = curl_init($url);
		curl_setopt_array($ch, array(
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_TIMEOUT        => $timeout,
			CURLOPT_USERAGENT      => "dezinhelp-geo-test",
		));
		$res = curl_exec($ch);
		curl_close($ch);
		return $res === false ? null : $res;
	}
	$ctx = stream_context_create(array("http" => array("timeout" => $timeout, "user_agent" => "dezinhelp-geo-test")));
	$res = @file_get_contents($url, false, $ctx);
	return $res === false ? null : $res;
}

// --- IP клиента ---
// За прокси хостинга реальный IP может лежать в X-Forwarded-For / X-Real-IP, а не в REMOTE_ADDR.
// Собираем все кандидаты — на проде сразу видно, какой заголовок отдаёт настоящий адрес.
$ipHeaders = array("REMOTE_ADDR", "HTTP_X_FORWARDED_FOR", "HTTP_X_REAL_IP", "HTTP_CF_CONNECTING_IP");
$ipSeen = array();
foreach ($ipHeaders as $key) {
	$ipSeen[$key] = isset($_SERVER[$key]) ? $_SERVER[$key] : "";
}
// выбираем первый валидный публичный IP по приоритету
$clientIp = "";
foreach (array("HTTP_X_FORWARDED_FOR", "HTTP_X_REAL_IP", "HTTP_CF_CONNECTING_IP", "REMOTE_ADDR") as $key) {
	if (!empty($_SERVER[$key])) {
		$candidate = trim(explode(",", $_SERVER[$key])[0]); // XFF бывает списком — берём первый
		if (filter_var($candidate, FILTER_VALIDATE_IP)) { $clientIp = $candidate; break; }
	}
}

// --- Метод А: геолокация по IP через ip-api.com (бесплатно, без ключа) ---
// Запрос делаем с сервера, поэтому http-эндпоинт бесплатного тарифа не упирается в mixed-content.
$ipGeo = null;
if ($clientIp !== "") {
	$fields = "status,message,country,regionName,city,district,zip,lat,lon,timezone,isp,org,mobile,proxy,hosting,query";
	$url = "http://ip-api.com/json/" . urlencode($clientIp) . "?lang=ru&fields=" . $fields;
	$raw = http_get($url, 4);
	if ($raw !== null) {
		$decoded = json_decode($raw, true);
		if (is_array($decoded)) { $ipGeo = $decoded; }
	}
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="robots" content="noindex, nofollow">
	<title>Гео-тест — замер точности геолокации</title>
	<style>
		/* минимальное оформление, без зависимостей */
		:root { --line: #e1e8ef; --muted: #5c6b7a; --navy: #102037; --blue: #4880b0; --green: #2f9e44; --bg: #f5f8fa; }
		* { box-sizing: border-box; }
		body { margin: 0; padding: 24px; font-family: -apple-system, "Roboto", Arial, sans-serif; color: var(--navy); background: var(--bg); line-height: 1.5; }
		.wrap { max-width: 860px; margin: 0 auto; }
		h1 { font-size: 22px; margin: 0 0 4px; }
		.sub { color: var(--muted); margin: 0 0 24px; }
		.card { background: #fff; border: 1px solid var(--line); border-radius: 10px; padding: 20px; margin-bottom: 20px; }
		.card h2 { font-size: 17px; margin: 0 0 4px; }
		/* блок-пояснение: методика/плюсы-минусы/требования */
		.desc { font-size: 13px; color: var(--muted); margin: 0 0 14px; }
		.desc b { color: var(--navy); }
		/* таблица «ключ — значение» */
		.kv { width: 100%; border-collapse: collapse; font-size: 14px; }
		.kv th { text-align: left; width: 190px; vertical-align: top; padding: 5px 12px 5px 0; color: var(--muted); font-weight: 400; }
		.kv td { padding: 5px 0; vertical-align: top; }
		.val { font-family: "SF Mono", Consolas, monospace; }
		.muted { color: var(--muted); }
		.ok { color: var(--green); }
		.err { color: #c0392b; }
		button { font: inherit; cursor: pointer; background: var(--blue); color: #fff; border: 0; border-radius: 8px; padding: 10px 18px; }
		button:disabled { opacity: .5; cursor: default; }
		a { color: var(--blue); }
		.maps a { margin-right: 12px; white-space: nowrap; }
		.hint { font-size: 13px; color: var(--muted); margin-top: 10px; }
	</style>
</head>
<body>
<div class="wrap">

	<h1>Гео-тест</h1>
	<p class="sub">Полигон для замера точности. Ничего не отправляется и не сохраняется — только показ на экране.</p>

	<!-- ============ Метод А: по IP (сервер) ============ -->
	<div class="card">
		<h2>А. По IP-адресу (серверный, ip-api.com)</h2>
		<p class="desc">
			<b>Методика:</b> сервер по IP посетителя спрашивает базу ip-api.com.
			<b>Плюсы:</b> тихо, без попапов и разрешений, работает всегда.
			<b>Минусы:</b> грубо (уровень города), в РФ часто врёт — мобильные операторы и NAT провайдеров сажают всех в одну точку.
			<b>Требования:</b> исходящий интернет с хостинга. Бесплатный тариф — до 45 запросов/мин. Для точности по РФ позже можно заменить на Sypex&nbsp;Geo (локальная база) или DaData (ключ).
		</p>

		<!-- какой заголовок отдал IP — важно проверить на проде за прокси -->
		<table class="kv">
			<tr><th>REMOTE_ADDR</th><td class="val"><?= $ipSeen["REMOTE_ADDR"] !== "" ? e($ipSeen["REMOTE_ADDR"]) : "<span class=\"muted\">—</span>" ?></td></tr>
			<tr><th>X-Forwarded-For</th><td class="val"><?= $ipSeen["HTTP_X_FORWARDED_FOR"] !== "" ? e($ipSeen["HTTP_X_FORWARDED_FOR"]) : "<span class=\"muted\">—</span>" ?></td></tr>
			<tr><th>X-Real-IP</th><td class="val"><?= $ipSeen["HTTP_X_REAL_IP"] !== "" ? e($ipSeen["HTTP_X_REAL_IP"]) : "<span class=\"muted\">—</span>" ?></td></tr>
			<tr><th>Выбранный IP</th><td class="val"><?= $clientIp !== "" ? e($clientIp) : "<span class=\"err\">не определён</span>" ?></td></tr>
		</table>

		<hr style="border:0;border-top:1px solid var(--line);margin:14px 0">

		<?php if ($ipGeo === null): ?>
			<p class="err">Не удалось получить данные от ip-api.com (нет исходящего интернета, приватный IP или лимит). На проде должно отработать.</p>
		<?php elseif (isset($ipGeo["status"]) && $ipGeo["status"] !== "success"): ?>
			<p class="err">ip-api вернул ошибку: <?= e(isset($ipGeo["message"]) ? $ipGeo["message"] : "unknown") ?></p>
		<?php else: ?>
			<table class="kv">
				<tr><th>Город</th><td class="val"><?= e($ipGeo["city"] ?? "—") ?><?= !empty($ipGeo["district"]) ? " (" . e($ipGeo["district"]) . ")" : "" ?></td></tr>
				<tr><th>Регион</th><td class="val"><?= e($ipGeo["regionName"] ?? "—") ?>, <?= e($ipGeo["country"] ?? "—") ?></td></tr>
				<tr><th>Индекс</th><td class="val"><?= e($ipGeo["zip"] ?? "—") ?></td></tr>
				<tr><th>Координаты</th><td class="val"><?= e($ipGeo["lat"] ?? "—") ?>, <?= e($ipGeo["lon"] ?? "—") ?></td></tr>
				<tr><th>Провайдер</th><td class="val"><?= e($ipGeo["isp"] ?? "—") ?></td></tr>
				<tr><th>Флаги</th><td class="val muted">mobile: <?= !empty($ipGeo["mobile"]) ? "да" : "нет" ?> · proxy: <?= !empty($ipGeo["proxy"]) ? "да" : "нет" ?> · hosting: <?= !empty($ipGeo["hosting"]) ? "да" : "нет" ?></td></tr>
				<?php if (isset($ipGeo["lat"], $ipGeo["lon"])): ?>
				<tr><th>На карте</th><td class="maps">
					<a href="https://yandex.ru/maps/?pt=<?= e($ipGeo["lon"]) ?>,<?= e($ipGeo["lat"]) ?>&z=13&l=map" target="_blank" rel="noopener">Яндекс</a>
					<a href="https://www.google.com/maps?q=<?= e($ipGeo["lat"]) ?>,<?= e($ipGeo["lon"]) ?>" target="_blank" rel="noopener">Google</a>
				</td></tr>
				<?php endif; ?>
			</table>
		<?php endif; ?>
	</div>

	<!-- ============ Метод Б: браузерный Geolocation API (точный) ============ -->
	<div class="card">
		<h2>Б. Браузерная геолокация (точный, GPS/Wi-Fi)</h2>
		<p class="desc">
			<b>Методика:</b> сам браузер по GPS (телефон) или Wi-Fi/сети (десктоп) отдаёт координаты и радиус погрешности.
			<b>Плюсы:</b> самый точный — на телефоне метры-десятки метров.
			<b>Минусы:</b> спрашивает разрешение (попап), пользователь может отказать.
			<b>Требования:</b> <b>только HTTPS</b> (по http браузер молча откажет) — то есть на проде. Плюс координаты можно расшифровать в адрес (обратная геокодировка через OpenStreetMap, бесплатно, без ключа).
		</p>

		<button id="geo-btn" type="button">Определить точно</button>
		<p class="hint" id="geo-hint">Нажмите кнопку — браузер спросит разрешение на доступ к геопозиции.</p>

		<table class="kv" id="geo-out" style="display:none;margin-top:12px">
			<tr><th>Координаты</th><td class="val" id="g-coords">—</td></tr>
			<tr><th>Погрешность</th><td class="val" id="g-acc">—</td></tr>
			<tr><th>Адрес (OSM)</th><td class="val" id="g-addr">—</td></tr>
			<tr><th>На карте</th><td class="maps" id="g-maps">—</td></tr>
		</table>
	</div>

	<!-- ============ Метод В: подсказки без спроса ============ -->
	<div class="card">
		<h2>В. Косвенные подсказки (часовой пояс, язык)</h2>
		<p class="desc">
			<b>Методика:</b> берём из браузера часовой пояс и язык.
			<b>Плюсы:</b> мгновенно, без спроса и без запросов наружу.
			<b>Минусы:</b> очень грубо — уровень «страна / Москва-не Москва», как перекрёстная проверка.
			<b>Требования:</b> никаких.
		</p>
		<table class="kv">
			<tr><th>Часовой пояс</th><td class="val" id="tz">—</td></tr>
			<tr><th>Язык браузера</th><td class="val" id="lang">—</td></tr>
		</table>
	</div>

</div>

<script>
	// ===== Метод В: подсказки (сразу при загрузке) =====
	try { document.getElementById("tz").textContent = Intl.DateTimeFormat().resolvedOptions().timeZone || "—"; } catch (e) {}
	document.getElementById("lang").textContent = (navigator.languages && navigator.languages.join(", ")) || navigator.language || "—";

	// ===== Метод Б: браузерная геолокация по кнопке =====
	var btn = document.getElementById("geo-btn");
	var hint = document.getElementById("geo-hint");
	var out = document.getElementById("geo-out");

	btn.addEventListener("click", function () {
		if (!navigator.geolocation) { hint.innerHTML = "<span style='color:#c0392b'>Браузер не поддерживает Geolocation API.</span>"; return; }
		btn.disabled = true;
		hint.textContent = "Запрашиваю геопозицию…";

		navigator.geolocation.getCurrentPosition(onOk, onErr, {
			enableHighAccuracy: true, // просим максимально точно (включает GPS на телефоне)
			timeout: 15000,
			maximumAge: 0             // без кэша — свежая точка
		});
	});

	function onOk(pos) {
		var c = pos.coords;
		var lat = c.latitude, lon = c.longitude, acc = Math.round(c.accuracy);
		btn.disabled = false;
		hint.textContent = "";
		out.style.display = "";

		document.getElementById("g-coords").textContent = lat.toFixed(6) + ", " + lon.toFixed(6);

		// человекочитаемая оценка точности
		var quality = acc <= 50 ? "отлично" : acc <= 500 ? "город/район" : "грубо";
		document.getElementById("g-acc").textContent = "± " + acc + " м (" + quality + ")";

		document.getElementById("g-maps").innerHTML =
			'<a target="_blank" rel="noopener" href="https://yandex.ru/maps/?pt=' + lon + ',' + lat + '&z=17&l=map">Яндекс</a>' +
			'<a target="_blank" rel="noopener" href="https://www.google.com/maps?q=' + lat + ',' + lon + '">Google</a>' +
			'<a target="_blank" rel="noopener" href="https://www.openstreetmap.org/?mlat=' + lat + '&mlon=' + lon + '#map=17/' + lat + '/' + lon + '">OSM</a>';

		// расшифровка координат в адрес — обратная геокодировка OpenStreetMap (Nominatim)
		var addr = document.getElementById("g-addr");
		addr.textContent = "запрашиваю…";
		fetch("https://nominatim.openstreetmap.org/reverse?format=json&accept-language=ru&zoom=18&lat=" + lat + "&lon=" + lon)
			.then(function (r) { return r.json(); })
			.then(function (d) { addr.textContent = d && d.display_name ? d.display_name : "адрес не найден"; })
			.catch(function () { addr.textContent = "не удалось получить адрес"; });
	}

	function onErr(err) {
		btn.disabled = false;
		var msg = err.code === 1 ? "Отказано в доступе (или страница не по HTTPS)." :
		          err.code === 2 ? "Позиция недоступна." :
		          err.code === 3 ? "Истекло время ожидания." : "Ошибка геолокации.";
		hint.innerHTML = "<span style='color:#c0392b'>" + msg + "</span>";
	}
</script>
</body>
</html>