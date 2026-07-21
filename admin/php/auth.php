<?php
// Авторизация админки: одна учётка из config.php, PHP-сессия, CSRF-токен.

// конфиг сайта: логин и хэш пароля лежат там же, где секреты Telegram
$configFile = __DIR__ . "/../../source/php/config.php";
$adminConfig = file_exists($configFile)
	? require $configFile
	: require __DIR__ . "/../../source/php/config.sample.php";

// сессия со своим именем и кука только для админки
session_name("dezadmin");
session_set_cookie_params(array(
	"path"     => "/admin/",
	"httponly" => true,
	"samesite" => "Lax",
));
session_start();

function auth_logged_in()
{
	return !empty($_SESSION["admin"]);
}

// проверка пары логин/пароль из config.php
function auth_login($user, $pass)
{
	global $adminConfig;
	$u = isset($adminConfig["admin_user"]) ? $adminConfig["admin_user"] : "";
	$h = isset($adminConfig["admin_password_hash"]) ? $adminConfig["admin_password_hash"] : "";
	if ($u === "" || $h === "") { return false; }

	// hash_equals против перебора по времени сравнения логина
	if (!hash_equals($u, (string) $user) || !password_verify((string) $pass, $h)) {
		sleep(1); // тормозим перебор
		return false;
	}

	session_regenerate_id(true);
	$_SESSION["admin"] = true;
	$_SESSION["csrf"]  = bin2hex(random_bytes(16));
	return true;
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