<?php
// Обработчик форм заявок: валидация + отправка в Telegram

header("Content-Type: application/json; charset=utf-8");

// конфиг: рабочий config.php, иначе шаблон
$configFile = __DIR__ . "/config.php";
$config = file_exists($configFile) ? require $configFile : require __DIR__ . "/config.sample.php";

// только POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
	http_response_code(405);
	echo json_encode(array("ok" => false, "error" => "method"));
	exit;
}

// honeypot: боты заполняют скрытое поле — молча "успех"
if (!empty($_POST["website"])) {
	echo json_encode(array("ok" => true));
	exit;
}

// город берём с хоста, а не из формы: подделать нельзя и вёрстку править не нужно
require __DIR__ . "/city.php";
city_config($config);
$orderCity = city_resolve();
$cityName  = isset($orderCity["city"]["name"]) ? $orderCity["city"]["name"] : "";

// обрезка по символам без mbstring: длинные поля не должны раздувать сообщение
// (Telegram не принимает больше 4096 символов — заявка не дошла бы вовсе)
function order_cut($s, $max)
{
	if (preg_match('/^.{0,' . $max . '}/us', $s, $m)) { return $m[0]; }
	return substr($s, 0, $max);
}

// Последняя страховка: заявка не ушла ни в CRM, ни в Telegram — кладём её на диск.
// Иначе она исчезнет вместе с посетителем, а он второй раз писать не станет.
// Файл не чистим по размеру, только перекладываем в .old: это единственная копия
function order_save_failed($text)
{
	$file = __DIR__ . "/../../data/.orders-failed.log";
	if (file_exists($file) && filesize($file) > 1048576) {
		@rename($file, $file . ".old");
	}
	@file_put_contents($file, date("d.m.Y H:i:s") . "\n" . $text . "\n\n----------\n\n", FILE_APPEND | LOCK_EX);
}

// сбор полей
$name    = order_cut(trim($_POST["name"] ?? ""), 100);
$phone   = order_cut(trim($_POST["phone"] ?? ""), 32);
$comment = order_cut(trim($_POST["comment"] ?? ""), 500);
$source  = order_cut(trim($_POST["source"] ?? "Сайт"), 100);

// рекламные метки первого захода и страница отправки — их подкладывает main.js.
// В amoCRM под каждую заведено своё поле, поэтому храним по ключам, а не строкой
$track = array();
foreach (array("utm_source", "utm_medium", "utm_campaign", "utm_content", "utm_term",
	"gclid", "yclid", "fbclid", "_ym_uid", "referrer", "landing", "page") as $k) {
	$v = order_cut(trim($_POST[$k] ?? ""), 500);
	if ($v !== "") { $track[$k] = $v; }
}

// имя обязательно, кроме форм с флагом name_optional (герой — как у донора)
if (empty($_POST["name_optional"]) && array_key_exists("name", $_POST) && $name === "") {
	http_response_code(422);
	echo json_encode(array("ok" => false, "error" => "name"));
	exit;
}

// валидация телефона: ровно 11 цифр
$digits = preg_replace("/\D+/", "", $phone);
if (strlen($digits) !== 11) {
	http_response_code(422);
	echo json_encode(array("ok" => false, "error" => "phone"));
	exit;
}

// лимит частоты: не больше 5 заявок с одного IP за 10 минут — иначе скриптом
// можно залить чат Telegram. IP — REMOTE_ADDR: заголовки типа X-Forwarded-For
// подделываются, для лимита они не годятся
function order_rate_ok($ip, $limit = 5, $window = 600)
{
	if ($ip === "") { return true; }
	$file = __DIR__ . "/../../data/.ratelimit.json";
	$fh = @fopen($file, "c+");
	if (!$fh) { return true; } // файл недоступен — заявки важнее лимита
	flock($fh, LOCK_EX);
	$all = json_decode((string) stream_get_contents($fh), true);
	if (!is_array($all)) { $all = array(); }

	// старые отметки выбрасываем по всем адресам — файл не растёт
	$from = time() - $window;
	foreach ($all as $k => $times) {
		$all[$k] = array_values(array_filter((array) $times, function ($t) use ($from) { return $t > $from; }));
		if (!$all[$k]) { unset($all[$k]); }
	}

	$ok = count(isset($all[$ip]) ? $all[$ip] : array()) < $limit;
	if ($ok) { $all[$ip][] = time(); }

	ftruncate($fh, 0);
	rewind($fh);
	fwrite($fh, json_encode($all));
	flock($fh, LOCK_UN);
	fclose($fh);
	return $ok;
}

if (!order_rate_ok(isset($_SERVER["REMOTE_ADDR"]) ? $_SERVER["REMOTE_ADDR"] : "")) {
	http_response_code(429);
	echo json_encode(array("ok" => false, "error" => "rate"));
	exit;
}

// согласие в квизе проверяет и сервер, а не только браузер.
// признак квиза — поле «способ связи»: в других формах его нет
if (isset($_POST["contact_method"]) && empty($_POST["consent"])) {
	http_response_code(422);
	echo json_encode(array("ok" => false, "error" => "consent"));
	exit;
}

// ответы квиза: с клиента приходят только индексы, тексты берём из data/quiz.json.
// иначе в Telegram можно было бы прислать произвольную строку
$quizAnswers = array();
$quizPicked  = array(); // те же ответы по индексам шагов — из них CRM заполняет поля
$quizContact = "";
if (isset($_POST["q0"]) || isset($_POST["contact_method"])) {
	require_once __DIR__ . "/data.php";
	$quiz = data_load("quiz");

	if (!empty($quiz["steps"])) {
		foreach ($quiz["steps"] as $i => $step) {
			if (!isset($_POST["q" . $i])) { continue; }
			$picked = (array) $_POST["q" . $i];
			$texts = array();
			foreach ($picked as $idx) {
				// только целые индексы: нечисловое иначе приводится к 0 и молча
				// подставляет первый вариант
				if (is_numeric($idx) && isset($step["options"][(int) $idx])) {
					$texts[] = $step["options"][(int) $idx]["text"];
				}
			}
			if ($texts) {
				$quizAnswers[] = " • " . $step["title"] . ": " . implode(", ", $texts);
				$quizPicked[$i] = $texts;
			}
		}
	}

	// способ связи: сверяем id со списком, чужое не пропускаем
	if (!empty($quiz["final"]["contacts"]) && isset($_POST["contact_method"])) {
		foreach ($quiz["final"]["contacts"] as $c) {
			if ($c["id"] === $_POST["contact_method"]) {
				$quizContact = $c["text"];
				break;
			}
		}
	}
}

// текст сообщения
$lines = array();
$lines[] = "🆕 Новая заявка: " . $source;
if ($cityName !== "") { $lines[] = "🏙 Город: " . $cityName; }
if ($name !== "")    { $lines[] = "👤 Имя: " . $name; }
$lines[] = "📞 Телефон: " . $phone;
if ($quizContact !== "") { $lines[] = "📲 Способ связи: " . $quizContact; }
if ($comment !== "") { $lines[] = "💬 Комментарий: " . $comment; }
if ($quizAnswers) {
	$lines[] = "📋 Ответы:";
	$lines[] = implode("\n", $quizAnswers);
}
if (isset($track["page"])) { $lines[] = "🔗 Страница: " . $track["page"]; }
if (isset($track["utm_source"])) {
	$ad = array($track["utm_source"]);
	foreach (array("utm_medium", "utm_campaign", "utm_term") as $k) {
		if (isset($track[$k])) { $ad[] = $track[$k]; }
	}
	$lines[] = "📊 Реклама: " . implode(" / ", $ad);
}
$text = implode("\n", $lines);

// --- amoCRM ---
// шлём до Telegram, но результат не решающий: заявка считается принятой, если
// сработал хотя бы один канал. Ошибки CRM уходят в data/.amo.log
require __DIR__ . "/amo.php";
$amoOk = false;
if (amo_enabled($config)) {
	$amo = amo_send($config, array(
		"name"    => $name,
		"phone"   => $phone,
		"city"    => $cityName,
		"source"  => $source,
		"comment" => $comment,
		"service" => order_cut(trim($_POST["service"] ?? ""), 60),
		"quiz"    => $quizPicked,
		"track"   => $track,
		"note"    => $text,
		"ip"      => isset($_SERVER["REMOTE_ADDR"]) ? $_SERVER["REMOTE_ADDR"] : "",
	));
	$amoOk = !empty($amo["ok"]);
}

// Telegram оставлен вторым каналом по просьбе заказчика: amoCRM у них уже подводила,
// а сообщение в чат приходит даже когда CRM недоступна
require __DIR__ . "/telegram.php";
$tgOk = tg_send($config, $text);

if ($tgOk || $amoOk) {
	echo json_encode(array("ok" => true));
} else {
	order_save_failed($text);
	http_response_code(502);
	echo json_encode(array("ok" => false, "error" => "telegram"));
}