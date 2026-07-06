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

// сбор полей
$name    = trim($_POST["name"] ?? "");
$phone   = trim($_POST["phone"] ?? "");
$comment = trim($_POST["comment"] ?? "");
$source  = trim($_POST["source"] ?? "Сайт");

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

// текст сообщения
$lines = array();
$lines[] = "🆕 Новая заявка: " . $source;
if ($name !== "")    { $lines[] = "👤 Имя: " . $name; }
$lines[] = "📞 Телефон: " . $phone;
if ($comment !== "") { $lines[] = "💬 Комментарий: " . $comment; }
$text = implode("\n", $lines);

// секреты
$token = $config["telegram_bot_token"] ?? "";
$chat  = $config["telegram_chat_id"] ?? "";
if ($token === "" || $chat === "") {
	http_response_code(500);
	echo json_encode(array("ok" => false, "error" => "config"));
	exit;
}

// отправка через Bot API
$url = "https://api.telegram.org/bot" . $token . "/sendMessage";
$payload = http_build_query(array(
	"chat_id" => $chat,
	"text"    => $text,
));

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200) {
	echo json_encode(array("ok" => true));
} else {
	http_response_code(502);
	echo json_encode(array("ok" => false, "error" => "telegram"));
}