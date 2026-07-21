<?php
// Шаблон конфигурации. Скопируйте в config.php и впишите реальные данные.
// config.php исключён из git (см. .gitignore).

return array(
	// базовый адрес сайта без хвостового слеша — для canonical и sitemap
	"site_url" => "https://dezinhelp.ru",

	// телефоны (публичные — выводятся на странице)
	"phones" => array(
		array("display" => "+7 (000) 000-00-00", "tel" => "+70000000000"),
		array("display" => "+7 (000) 000-00-00", "tel" => "+70000000000"),
	),
	"email"   => "temp@mail.com",
	"address" => "г. Москва, ул. Красная Пресня, д. 32-34, 1Л/Н",
	"license" => "№ 77.00165.0.25 (ЕРУЛ № Л04-00111-70197)",

	// ссылки на мессенджеры (публичные)
	"messengers" => array(
		"telegram" => "#",
		"max"      => "#",
		"whatsapp" => "#",
	),

	// секреты — используются только сервером (order.php), в HTML не попадают
	"telegram_bot_token" => "",
	"telegram_chat_id"   => "",

	// учётка админки: хэш пароля делается командой
	// php -r "echo password_hash('пароль', PASSWORD_DEFAULT), PHP_EOL;"
	"admin_user"          => "",
	"admin_password_hash" => "",
);