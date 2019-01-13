<?php 
session_start(); 
echo '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8" />
</head>
<body>
	<h1>WebSocket Server Admin panel</h1>
';
if (isset($_SESSION['wsadmin']['login']))
{		
	echo '
	<input id="ws-start" type="button" value="Запустить" /> 
	<input id="ws-stop" type="button" value="Остановить" /> 
	<input id="ws-kill" type="button" value="Принудительно остановить" />
	<br>
	<br>
	<label>Состояние процесса:</label>
	<br>
	<div id="ws-status" style="display:inline-block;border: 1px solid">Loading...</div>
	<br>
	<br>
	<label>Ответ сервера:</label>
	<br>
	<div id="ws-status-proc" style="display:inline-block;border: 1px solid">Loading...</div>
	<br>
	<br>
	<input id="ws-exit" type="button" value="Выйти" />	
	';
} 
else 
{ 
	echo'
	Логин:<br>
	<input id="login" type="text" maxlength="15" size="15" name="login" /><br>
	Пароль:<br>
	<input id="pass" type="password" maxlength="15" size="15" name="pass" /><br><br>
	<input id="gologin" type="button" value="Войти" /><br>
	<p id="loginmsg" style="color:red;"></p><br>
	';	
} 
echo'
<script src="../js/admin.js" type="text/javascript"></script>
</body>
</html>
';