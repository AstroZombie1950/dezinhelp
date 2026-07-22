<?php
// Настройки сайта. Подключается из admin/index.php при page=settings.
// Ждёт: $site (data/site.json), $authCreds (действующая учётка), $adminConfig (config.php).

// пока site.json не сохраняли, показываем то, что реально работает на сайте — значения конфига
$sVal = function ($key) use ($site, $adminConfig) {
	if (isset($site[$key]) && $site[$key] !== "") { return $site[$key]; }
	if (isset($adminConfig[$key]) && !is_array($adminConfig[$key])) { return $adminConfig[$key]; }
	return "";
};

// строки подвала живут только в site.json — в конфиг за ними не ходим
$fVal = function ($key) use ($site) { return isset($site[$key]) ? $site[$key] : ""; };

$sPhones = !empty($site["phones"])
	? $site["phones"]
	: (isset($adminConfig["phones"]) ? $adminConfig["phones"] : array());
if (!$sPhones) { $sPhones = array(array("display" => "", "tel" => "")); }

$sMsgs = !empty($site["messengers"])
	? $site["messengers"]
	: (isset($adminConfig["messengers"]) ? $adminConfig["messengers"] : array());
$mVal = function ($key) use ($sMsgs) { return isset($sMsgs[$key]) ? $sMsgs[$key] : ""; };
?>
<div class="page-head">
	<h1 class="page-title">Настройки</h1>
</div>
<p class="page-hint">Контакты правятся здесь и сразу подставляются во все блоки сайта: шапку, подвал, формы. Каждая вкладка сохраняется своей кнопкой.</p>

<!-- переключатель вкладок -->
<div class="tabs" id="tabs">
	<button type="button" class="tabs__btn is-active" data-tab="site">Данные сайта</button>
	<button type="button" class="tabs__btn" data-tab="auth">Доступ</button>
</div>

<!-- ===== Данные сайта ===== -->
<form class="tab card is-active js-tab-form" data-tab="site" data-action="settings_save_site" id="settings-site-form">

	<!-- телефоны: строк сколько нужно, минимум одна -->
	<div class="field__label">Телефоны</div>
	<p class="field__hint field__hint--top">Показываются в шапке, подвале и в формах. Первый — основной.</p>
	<div id="phones-list">
		<?php foreach ($sPhones as $p): ?>
		<div class="phone-row js-phone-row">
			<label class="field">
				<span class="field__label">Как показывать</span>
				<input type="text" name="phone_display[]" value="<?= h(isset($p["display"]) ? $p["display"] : "") ?>" placeholder="+7 (495) 109-32-00">
			</label>
			<label class="field">
				<span class="field__label">Номер для ссылки</span>
				<input type="text" name="phone_tel[]" value="<?= h(isset($p["tel"]) ? $p["tel"] : "") ?>" placeholder="+74951093200">
			</label>
			<button type="button" class="icon-btn icon-btn--danger js-phone-del" title="Удалить телефон">✕</button>
		</div>
		<?php endforeach; ?>
	</div>
	<div class="row-actions">
		<button type="button" class="btn btn--small" id="phone-add">+ Добавить телефон</button>
	</div>

	<label class="field">
		<span class="field__label">E-mail</span>
		<input type="text" name="email" value="<?= h($sVal("email")) ?>" placeholder="mail@example.ru">
		<span class="field__hint">Ссылка в подвале и адрес в формах.</span>
	</label>

	<label class="field">
		<span class="field__label">Адрес</span>
		<input type="text" name="address" value="<?= h($sVal("address")) ?>">
		<span class="field__hint">Строкой, как показывать в подвале.</span>
	</label>

	<!-- две строки мелким шрифтом в подвале -->
	<label class="field">
		<span class="field__label">Реквизиты в подвале</span>
		<input type="text" name="legal" value="<?= h($fVal("legal")) ?>">
		<span class="field__hint">Первая строка мелким шрифтом: название, ИП, ИНН.</span>
	</label>

	<label class="field">
		<span class="field__label">Лицензия в подвале</span>
		<input type="text" name="license" value="<?= h($fVal("license")) ?>">
		<span class="field__hint">Вторая строка мелким шрифтом. Пусто — строка не показывается.</span>
	</label>

	<!-- ссылки мессенджеров: на них ведут иконки в шапке, героях и формах -->
	<div class="field__label">Мессенджеры</div>
	<p class="field__hint field__hint--top">Полные ссылки со https://. Если мессенджера нет — оставьте решётку «#», иконка останется без перехода.</p>

	<label class="field">
		<span class="field__label">WhatsApp</span>
		<input type="text" name="msg_whatsapp" value="<?= h($mVal("whatsapp")) ?>" placeholder="https://api.whatsapp.com/send/?phone=7...">
	</label>
	<label class="field">
		<span class="field__label">Telegram</span>
		<input type="text" name="msg_telegram" value="<?= h($mVal("telegram")) ?>" placeholder="https://t.me/...">
	</label>
	<label class="field">
		<span class="field__label">Max</span>
		<input type="text" name="msg_max" value="<?= h($mVal("max")) ?>" placeholder="https://max.ru/...">
	</label>

	<label class="field">
		<span class="field__label">Адрес сайта</span>
		<input type="text" name="site_url" value="<?= h($sVal("site_url")) ?>" placeholder="https://dezinhelp.ru">
		<span class="field__hint">Нужен для карты сайта и ссылок при репостах. Менять только при переезде на другой домен.</span>
	</label>

	<div class="tab__actions"><button type="submit" class="btn btn--primary">Сохранить</button></div>
</form>

<!-- ===== Доступ ===== -->
<form class="tab card js-tab-form" data-tab="auth" data-action="settings_save_auth" id="settings-auth-form" autocomplete="off">

	<div class="notice notice--warn">
		<div class="notice__title">Меняйте эти данные осторожно</div>
		<p>Новый логин и пароль сразу же становятся единственным способом войти в панель. Запишите их и сохраните в надёжном месте — в менеджере паролей или на бумаге отдельно от компьютера.</p>
		<p>Восстановить забытый пароль из панели нельзя: он хранится в зашифрованном виде. Вернуть доступ сможет только веб-мастер — правкой файлов на сервере.</p>
	</div>

	<label class="field">
		<span class="field__label">Логин</span>
		<input type="text" name="new_user" value="<?= h($authCreds["user"]) ?>" autocomplete="off">
		<span class="field__hint">Латинские буквы, цифры, точка, дефис или подчёркивание.</span>
	</label>

	<label class="field">
		<span class="field__label">Новый пароль</span>
		<input type="password" name="new_pass" autocomplete="new-password" placeholder="Оставьте пустым, если меняете только логин">
		<span class="field__hint">Не короче 8 символов.</span>
	</label>

	<label class="field">
		<span class="field__label">Новый пароль ещё раз</span>
		<input type="password" name="new_pass2" autocomplete="new-password">
	</label>

	<label class="field field--sep">
		<span class="field__label">Действующий пароль</span>
		<input type="password" name="current_pass" autocomplete="current-password">
		<span class="field__hint">Тот, которым вы вошли сейчас. Без него изменения не сохранятся.</span>
	</label>

	<div class="tab__actions"><button type="submit" class="btn btn--primary">Сохранить доступ</button></div>
</form>

<?php // пустой слаг: общий обработчик сохранения его требует, экшены настроек — игнорируют ?>
<input type="hidden" id="edit-slug" value="">