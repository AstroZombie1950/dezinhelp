<?php
// Отправка сообщения в Telegram. Используется и приёмником заявок (order.php),
// и обработчиком вебхуков amoCRM (amo-hook.php).

function tg_send($config, $text)
{
	$token = $config["telegram_bot_token"] ?? "";
	$chat  = $config["telegram_chat_id"] ?? "";
	if ($token === "" || $chat === "" || $text === "") { return false; }

	$ch = curl_init("https://api.telegram.org/bot" . $token . "/sendMessage");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array(
		"chat_id"                  => $chat,
		"text"                     => $text,
		"disable_web_page_preview" => "true",
	)));
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
	curl_setopt($ch, CURLOPT_TIMEOUT, 10);
	curl_exec($ch);
	// curl_close не зовём: с PHP 8.5 он deprecated, а предупреждение ломает JSON-ответ
	return curl_getinfo($ch, CURLINFO_HTTP_CODE) === 200;
}
