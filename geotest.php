<?php
// geotest.php — стенд для сравнения способов определения города.
// Никаких связок и фолбэков: каждый источник запускается сам по себе, чтобы видеть,
// кто что выдаёт по одному и тому же адресу и сколько это заняло. Ничего не сохраняет,
// из индексации закрыт (noindex). Браузерный метод работает ТОЛЬКО по HTTPS — значит, на проде.
require __DIR__ . "/source/php/bootstrap.php";
require __DIR__ . "/source/php/geo.php";

$geoData   = data_load("geo-cities");
$geoCities = isset($geoData["cities"]) ? $geoData["cities"] : array();
$geoCoords = isset($geoData["coords"]) ? $geoData["coords"] : array();
$dadataKey = isset($config["dadata_token"]) ? (string) $config["dadata_token"] : "";
$clientIp  = geo_client_ip();

// ---------------------------------------------------------------------------
// API стенда: страница дёргает эти ветки через fetch и сама рисует результат.
// Всё до единого вывода — иначе JSON испортится разметкой.
// ---------------------------------------------------------------------------
if (isset($_GET["api"])) {
	header("Content-Type: application/json; charset=utf-8");
	$ip  = !empty($_GET["ip"]) && filter_var($_GET["ip"], FILTER_VALIDATE_IP) ? $_GET["ip"] : $clientIp;
	$out = array();

	switch ($_GET["api"]) {
		// один движок по IP
		case "engine":
			$name = isset($_GET["name"]) ? $_GET["name"] : "";
			if ($ip === "") {
				$out = geo_result($name, array(), "IP клиента не определён");
				break;
			}
			switch ($name) {
				case "sxgeo":     $out = geo_sxgeo($ip); break;
				case "dadata":    $out = geo_dadata($ip, $dadataKey); break;
				case "freeipapi": $out = geo_freeipapi($ip); break;
				case "ipapi":     $out = geo_ipapi($ip); break;
				default:          $out = geo_result($name, array(), "Неизвестный движок");
			}
			$out["ip"] = $ip;
			break;

		// координаты браузера → город (обратная геокодировка)
		case "reverse":
			$lat = isset($_GET["lat"]) ? (float) $_GET["lat"] : 0;
			$lon = isset($_GET["lon"]) ? (float) $_GET["lon"] : 0;
			$via = isset($_GET["via"]) ? $_GET["via"] : "dadata";
			$out = $via === "osm" ? geo_reverse_osm($lat, $lon) : geo_reverse_dadata($lat, $lon, $dadataKey);
			break;

		default:
			$out = array("error" => "Неизвестный метод");
	}

	// к любому удачному ответу добавляем сверку с нашим списком городов
	if (!empty($out["ok"])) {
		$out["match"] = geo_match($geoCities, $geoCoords, $out["city"], $out["lat"], $out["lon"]);
	}

	echo json_encode($out, JSON_UNESCAPED_UNICODE);
	exit;
}

// протокол: без HTTPS браузерная геолокация молча не работает
$isHttps = (!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off")
	|| (isset($_SERVER["HTTP_X_FORWARDED_PROTO"]) && $_SERVER["HTTP_X_FORWARDED_PROTO"] === "https");
?>
<!DOCTYPE html>
<html lang="ru">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="robots" content="noindex, nofollow">
	<title>Гео-стенд — способы определения города по отдельности</title>
	<style>
		/* оформление своё, main.css не подключаем: стенд живёт отдельно от сайта */
		:root { --line:#e1e8ef; --muted:#5c6b7a; --navy:#102037; --blue:#4880b0; --green:#2f9e44; --red:#c0392b; --bg:#f5f8fa; }
		* { box-sizing:border-box; }
		body { margin:0; padding:24px; font-family:-apple-system,"Roboto",Arial,sans-serif; color:var(--navy); background:var(--bg); line-height:1.5; }
		.wrap { max-width:1180px; margin:0 auto; }
		h1 { font-size:22px; margin:0 0 4px; }
		.sub { color:var(--muted); margin:0 0 20px; }
		h2.section { font-size:18px; margin:0 0 12px; }
		button { font:inherit; cursor:pointer; background:var(--blue); color:#fff; border:0; border-radius:8px; padding:9px 16px; }
		button:disabled { opacity:.5; cursor:default; }
		button.ghost { background:#fff; color:var(--blue); border:1px solid var(--blue); }
		input[type=text] { font:inherit; padding:8px 10px; border:1px solid var(--line); border-radius:8px; }
		.val { font-family:"SF Mono",Consolas,monospace; }
		.muted { color:var(--muted); } .ok { color:var(--green); } .err { color:var(--red); }
		.warn { background:#fff6e5; border:1px solid #f0d9a8; border-radius:10px; padding:14px 18px; margin-bottom:20px; font-size:14px; }

		/* панель управления над карточками */
		.bar { display:flex; gap:10px; align-items:center; flex-wrap:wrap; margin-bottom:18px; }

		/* карточки способов */
		.tag { display:inline-block; font-size:11px; padding:2px 7px; border-radius:20px; background:#eef3f7; color:var(--muted); white-space:nowrap; }
		.tag--no { background:#fdecea; color:var(--red); }
		.tag--local { background:#e8f5ec; color:var(--green); }
		.methods { display:grid; grid-template-columns:1fr; gap:16px; margin-bottom:20px; }
		.method { background:#fff; border:1px solid var(--line); border-radius:10px; padding:18px; display:flex; flex-direction:column; }
		.method--wide { grid-column:1 / -1; }
		.method__head { display:flex; align-items:center; gap:10px; margin-bottom:8px; }
		.method__name { font-size:16px; font-weight:700; }
		.method__desc { font-size:13px; color:var(--muted); margin:0 0 14px; }
		.method__desc b { color:var(--navy); font-weight:600; }
		.method__btn { align-self:flex-start; margin-top:auto; }
		.method__out { margin-top:14px; font-size:13px; border-top:1px solid var(--line); padding-top:12px; display:none; }
		.r-city { font-size:20px; font-weight:700; line-height:1.2; }
		.r-line { margin-top:4px; }
		.r-line .muted { color:var(--muted); }

		/* блок обратной геокодировки внутри браузерной карточки */
		.geocoders { display:grid; grid-template-columns:repeat(2,1fr); gap:14px; margin-top:12px; }
		.geocoder { background:var(--bg); border-radius:8px; padding:12px 14px; }
		.geocoder__name { font-size:12px; text-transform:uppercase; letter-spacing:.4px; color:var(--muted); margin-bottom:6px; }

		/* карты: точка по возвращённым координатам, Яндекс + Google рядом */
		.maps { display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-top:14px; }
		.map__label { font-size:12px; text-transform:uppercase; letter-spacing:.4px; color:var(--muted); margin-bottom:6px; }
		.map iframe { display:block; width:100%; height:230px; border:0; border-radius:8px; background:#eef3f7; }

		/* косвенные подсказки */
		table.grid { width:100%; border-collapse:collapse; font-size:14px; }
		.grid td { padding:9px 10px 9px 0; border-bottom:1px solid var(--line); vertical-align:top; }
		.grid tr:last-child td { border-bottom:0; }
		.card { background:#fff; border:1px solid var(--line); border-radius:10px; padding:20px; }
		.card h2 { font-size:17px; margin:0 0 10px; }
		.desc { font-size:13px; color:var(--muted); margin:0 0 14px; }

		@media (max-width:760px) { .methods, .geocoders, .maps { grid-template-columns:1fr; } body { padding:14px; } }
	</style>
</head>
<body>
<div class="wrap">

	<h1>Гео-стенд</h1>
	<p class="sub">Каждый способ определения города — сам по себе, без связок и фолбэков. Видно, кто что выдаёт по одному адресу и сколько это заняло. Ничего не отправляется и не сохраняется.</p>

	<?php if (!$isHttps): ?>
	<div class="warn">
		<b>Страница открыта не по HTTPS.</b> Браузерная геолокация в этом режиме отказывает молча — карточка «Browser Geolocation API»
		отработает как отказ пользователя. Полная картина будет после установки SSL. IP-способы работают и без HTTPS.
	</div>
	<?php endif; ?>

	<!-- ================= способы по IP ================= -->
	<h2 class="section">По IP-адресу</h2>
	<div class="bar">
		<button id="run-all">Прогнать все по IP</button>
		<input type="text" id="ip-input" placeholder="IP для проверки (пусто — ваш)" style="width:260px" value="">
		<span class="muted" style="font-size:13px">ваш адрес: <span class="val"><?= $clientIp !== "" ? h($clientIp) : "не определён" ?></span></span>
	</div>

	<div class="methods">

		<div class="method" data-engine="sxgeo">
			<div class="method__head">
				<div class="method__name">Sypex Geo</div>
				<span class="tag tag--local">локальная база</span>
			</div>
			<p class="method__desc">
				Сторонний движок, но с заранее скачанной базой <span class="val">SxGeoCity.dat</span> (37 МБ) прямо на сервере —
				сети не требует, отвечает за ~2 мс. <b>Коммерческое использование разрешено.</b> Точность — до города.
			</p>
			<button class="method__btn">Проверить</button>
			<div class="method__out"></div>
		</div>

		<div class="method" data-engine="dadata">
			<div class="method__head">
				<div class="method__name">DaData</div>
				<span class="tag">по ключу · 10k/сутки</span>
			</div>
			<p class="method__desc">
				Сторонний сервис по токену через API, только адреса РФ, 10 000 запросов в сутки бесплатно. Отдаёт коды ФИАС/КЛАДР —
				по ним наш список городов матчится однозначно. <b>Нужен ключ</b> <span class="val">dadata_token</span> в config.php.
			</p>
			<button class="method__btn">Проверить</button>
			<div class="method__out"></div>
		</div>

		<div class="method" data-engine="freeipapi">
			<div class="method__head">
				<div class="method__name">freeipapi</div>
				<span class="tag">без ключа · 60/мин</span>
			</div>
			<p class="method__desc">
				Сторонний API без ключа, по HTTPS, 60 запросов в минуту. <b>Коммерческое использование разрешено.</b>
				Данные общемировые — по России грубее, чем у DaData. Держим для сравнения точности.
			</p>
			<button class="method__btn">Проверить</button>
			<div class="method__out"></div>
		</div>

		<div class="method" data-engine="ipapi">
			<div class="method__head">
				<div class="method__name">ip-api</div>
				<span class="tag tag--no">в прод нельзя</span>
			</div>
			<p class="method__desc">
				Только для сравнения: на бесплатном тарифе <b>коммерческое использование запрещено</b> правилами сервиса, плюс работает
				только по http. В боевую логику не идёт ни при каком результате — держим как эталон точности.
			</p>
			<button class="method__btn">Проверить</button>
			<div class="method__out"></div>
		</div>

	</div>

	<!-- ================= браузерная геолокация ================= -->
	<h2 class="section">По геопозиции браузера</h2>
	<div class="methods">
		<div class="method method--wide" id="browser">
			<div class="method__head">
				<div class="method__name">Browser Geolocation API</div>
				<span class="tag">по кнопке · только HTTPS</span>
			</div>
			<p class="method__desc">
				Точные координаты из GPS/Wi-Fi — браузер спрашивает разрешение по кнопке, погрешность до десятков метров.
				<b>Работает только по HTTPS</b> (на проде). Координаты расшифровываем в город двумя обратными геокодерами
				сразу — <b>DaData</b> (тот же ключ и лимит) и <b>OpenStreetMap</b> (без ключа, некоммерческий по лицензии) — показываем оба.
			</p>
			<button class="method__btn" id="browser-btn">Определить по геопозиции</button>
			<div class="method__out" id="browser-out"></div>
		</div>
	</div>

	<!-- ================= косвенные подсказки ================= -->
	<div class="card">
		<h2>Косвенные подсказки</h2>
		<p class="desc">
			Часовой пояс и язык браузера. Мгновенно, без спроса и без запросов наружу, но точность — уровня «страна и часовой пояс».
			Годится как перекрёстная проверка: если пояс не московский, а IP говорит «Москва», скорее всего человек за VPN.
		</p>
		<table class="grid">
			<tr><td style="width:170px" class="muted">Часовой пояс</td><td class="val" id="tz">—</td></tr>
			<tr><td class="muted">Язык браузера</td><td class="val" id="lang">—</td></tr>
			<tr><td class="muted">Разрешение на гео</td><td class="val" id="perm">—</td></tr>
		</table>
	</div>

</div>

<script>
	// ===== вспомогательное =====
	function api(params) {
		var q = new URLSearchParams(params).toString();
		return fetch("?" + q, { headers: { "Accept": "application/json" } }).then(function (r) { return r.json(); });
	}

	// поле «проверить произвольный IP» действует на все IP-способы
	function customIp() {
		var v = document.getElementById("ip-input").value.trim();
		return v ? { ip: v } : {};
	}

	function matchText(m) {
		if (!m) return "—";
		if (m.exact) return "<span class='ok'>да, " + m.exact + "</span>";
		if (m.nearest && m.far) return "<span class='err'>вне зоны</span> (" + m.nearest + ", " + m.km + " км)";
		if (m.nearest) return "нет; ближайший — " + m.nearest + ", " + m.km + " км";
		return "нет";
	}

	// точку по возвращённым координатам — на Яндекс и Google, бесключевыми эмбедами.
	// Яндекс: ll/pt в порядке «долгота,широта»; Google: q в порядке «широта,долгота».
	function mapsHtml(lat, lon) {
		if (lat === null || lon === null) return "<div class='r-line muted'>координат нет — карту не строим</div>";
		var y = "https://yandex.ru/map-widget/v1/?ll=" + lon + "," + lat + "&z=11&pt=" + lon + "," + lat + ",pm2rdm";
		var g = "https://maps.google.com/maps?q=" + lat + "," + lon + "&z=11&output=embed";
		return "<div class='maps'>"
			+ "<div class='map'><div class='map__label'>Яндекс.Карты</div><iframe loading='lazy' src='" + y + "'></iframe></div>"
			+ "<div class='map'><div class='map__label'>Google Maps</div><iframe loading='lazy' src='" + g + "'></iframe></div>"
			+ "</div>";
	}

	// единый вид результата: сырые координаты и точка на карте отдельно от имени города.
	// «по версии сервиса» — что отдал источник; «наш список» — что мы из этого слепили сами.
	function resultHtml(d) {
		if (!d || !d.ok) return "<span class='err'>" + ((d && d.error) || "город не определён") + "</span>";
		var coords = d.lat !== null ? d.lat + ", " + d.lon : "—";
		return "<div class='r-line'>Координаты от сервиса: <span class='val'><b>" + coords + "</b></span></div>"
			+ "<div class='r-line'>Город <b>по версии сервиса</b>: <b>" + (d.city || "—") + "</b>"
				+ (d.region ? " <span class='muted'>(" + d.region + (d.country ? ", " + d.country : "") + ")</span>" : "") + "</div>"
			+ "<div class='r-line'>Наш город <b>(считаем сами)</b>: " + matchText(d.match) + "</div>"
			+ (d.ms ? "<div class='r-line muted'>Время сервера: " + d.ms + " мс</div>" : "")
			+ mapsHtml(d.lat, d.lon);
	}

	// ===== IP-способы =====
	function runIpEngine(name) {
		var card = document.querySelector("[data-engine='" + name + "']");
		var out  = card.querySelector(".method__out");
		out.style.display = "block";
		out.innerHTML = "<span class='muted'>запрашиваю…</span>";
		return api(Object.assign({ api: "engine", name: name }, customIp())).then(function (d) {
			out.innerHTML = resultHtml(d);
			return d;
		});
	}

	document.querySelectorAll(".method[data-engine] .method__btn").forEach(function (btn) {
		btn.addEventListener("click", function () {
			var name = btn.closest(".method").dataset.engine;
			btn.disabled = true;
			runIpEngine(name).then(function () { btn.disabled = false; });
		});
	});

	document.getElementById("run-all").addEventListener("click", function () {
		var self = this;
		self.disabled = true;
		Promise.all(["sxgeo", "dadata", "freeipapi", "ipapi"].map(runIpEngine)).then(function () { self.disabled = false; });
	});

	// ===== браузерная геолокация =====
	function reverseCard(via, name, coords) {
		return api({ api: "reverse", via: via, lat: coords.latitude, lon: coords.longitude }).then(function (d) {
			var body = d.ok
				? "<div class='r-city' style='font-size:16px'>" + d.city + "</div>"
					+ "<div class='muted'>" + (d.region || "") + "</div>"
					+ (d.address ? "<div class='r-line muted' style='font-size:12px'>" + d.address + "</div>" : "")
					+ "<div class='r-line'>Наш список: " + matchText(d.match) + "</div>"
				: "<span class='err'>" + (d.error || "не удалось") + "</span>";
			return "<div class='geocoder'><div class='geocoder__name'>" + name + "</div>" + body + "</div>";
		});
	}

	document.getElementById("browser-btn").addEventListener("click", function () {
		var btn = this;
		var out = document.getElementById("browser-out");
		out.style.display = "block";
		if (!navigator.geolocation) { out.innerHTML = "<span class='err'>браузер не умеет Geolocation API</span>"; return; }
		btn.disabled = true;
		out.innerHTML = "<span class='muted'>жду разрешения и координат…</span>";
		navigator.geolocation.getCurrentPosition(
			function (pos) {
				var c = pos.coords;
				out.innerHTML = "<div class='r-line'>Координаты браузера: <span class='val'><b>" + c.latitude.toFixed(5) + ", " + c.longitude.toFixed(5) + "</b></span>"
					+ " <span class='muted'>(±" + Math.round(c.accuracy) + " м)</span></div>"
					+ mapsHtml(c.latitude, c.longitude)
					+ "<div class='r-line muted' style='margin-top:12px'>расшифровываю координаты в город двумя геокодерами…</div>"
					+ "<div class='geocoders'></div>";
				Promise.all([
					reverseCard("dadata", "DaData", c),
					reverseCard("osm", "OpenStreetMap", c)
				]).then(function (cards) {
					out.querySelector(".geocoders").innerHTML = cards.join("");
					btn.disabled = false;
				});
			},
			function (err) {
				out.innerHTML = "<span class='err'>" + (err.code === 1 ? "отказано в доступе (или страница не по HTTPS)"
					: err.code === 2 ? "позиция недоступна" : "истекло время ожидания") + "</span>";
				btn.disabled = false;
			},
			{ enableHighAccuracy: true, timeout: 12000, maximumAge: 0 }
		);
	});

	// ===== косвенные подсказки =====
	try { document.getElementById("tz").textContent = Intl.DateTimeFormat().resolvedOptions().timeZone || "—"; } catch (e) {}
	document.getElementById("lang").textContent = (navigator.languages && navigator.languages.join(", ")) || navigator.language || "—";
	if (navigator.permissions) {
		navigator.permissions.query({ name: "geolocation" }).then(function (st) {
			document.getElementById("perm").textContent = st.state === "granted" ? "выдано"
				: st.state === "denied" ? "запрещено" : "ещё не спрашивали";
		});
	} else {
		document.getElementById("perm").textContent = "Permissions API недоступен";
	}
</script>
</body>
</html>
