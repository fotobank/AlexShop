{* Письмо регистрации пользователя *}	{$subject = "Регистрация на сайте `$settings->site_name`" scope=parent}<html>	<body>		<p>{$user->name|escape} Вы успешно были зарегистрированы на сайте <a href='http://{$config->root_url}/'>{$settings->site_name}</a>.</p>		<p>Ваш логин: <b>{$user->email}</b></p>		<p>Ваш пароль: <b>{$password}</b></p>		<br>		<br>		<a href="http://simpla-addons.org">Simpla-Addons.org</a> - Шаблоны, модули и дополнения для Simpla CMS	</body></html>