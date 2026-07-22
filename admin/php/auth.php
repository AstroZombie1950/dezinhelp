<?php
// Авторизация админки: одна учётка, PHP-сессия, CSRF-токен.
// Учётка берётся из data/auth.json (её пишет админка), а если файла нет — из config.php.

// конфиг сайта: логин и хэш пароля лежат там же, где секреты Telegram
$configFile = __DIR__ . "/../../source/php/config.php";
$adminConfig = file_exists($configFile)
	? require $configFile
	: require __DIR__ . "/../../source/php/config.sample.php";

// учётка, изменённая через панель; лежит в закрытой от веба /data
define("AUTH_FILE", __DIR__ . "/../../data/auth.json");

// сессия со своим именем и кука только для админки
session_name("dezadmin");
session_set_cookie_params(array(
	"path"     => "/admin/",
	"httponly" => true,
	"samesite" => "Lax",
));
session_start();

// действующая пара логин/хэш: файл админки важнее конфига
function auth_creds()
{
	global $adminConfig;
	$user = isset($adminConfig["admin_user"]) ? (string) $adminConfig["admin_user"] : "";
	$hash = isset($adminConfig["admin_password_hash"]) ? (string) $adminConfig["admin_password_hash"] : "";

	if (file_exists(AUTH_FILE)) {
		$json = json_decode(file_get_contents(AUTH_FILE), true);
		// битый или неполный файл игнорируем — вход останется по конфигу
		if (is_array($json) && !empty($json["user"]) && !empty($json["password_hash"])) {
			$user = (string) $json["user"];
			$hash = (string) $json["password_hash"];
		}
	}
	return array("user" => $user, "hash" => $hash);
}

function auth_logged_in()
{
	return !empty($_SESSION["admin"]);
}

// проверка пары логин/пароль
function auth_login($user, $pass)
{
	$c = auth_creds();
	if ($c["user"] === "" || $c["hash"] === "") { return false; }

	// hash_equals против перебора по времени сравнения логина
	if (!hash_equals($c["user"], (string) $user) || !password_verify((string) $pass, $c["hash"])) {
		sleep(1); // тормозим перебор
		return false;
	}

	session_regenerate_id(true);
	$_SESSION["admin"] = true;
	$_SESSION["csrf"]  = bin2hex(random_bytes(16));
	return true;
}

// подтверждение действующего пароля — нужно при смене доступа
function auth_check_password($pass)
{
	$c = auth_creds();
	if ($c["hash"] === "") { return false; }
	return password_verify((string) $pass, $c["hash"]);
}

function auth_logout()
{
	$_SESSION = array();
	session_destroy();
}

function auth_csrf_token()
{
	if (empty($_SESSION["csrf"])) { $_SESSION["csrf"] = bin2hex(random_bytes(16)); }
	return $_SESSION["csrf"];
}

// все пишущие экшены обязаны присылать токен
function auth_csrf_check($token)
{
	return !empty($_SESSION["csrf"]) && hash_equals($_SESSION["csrf"], (string) $token);
}