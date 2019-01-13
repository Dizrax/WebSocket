<?php

define('ABSPATH', $_SERVER['DOCUMENT_ROOT'].'/' );

require_once(ABSPATH."main/Classes/ServerManager.php");

require(ABSPATH."configs/server_config.php");

$login = 'admin';
$pass  = '1234';

ini_set('display_errors', 1);
error_reporting(E_ALL); 

session_start();

if(isset($_POST['login']) && isset($_POST['pass']))
{
	if($_POST['login'] == $login && $_POST['pass'] == $pass)
	{
		$_SESSION['wsadmin']['login'] = $login;
		echo "{msg:1}";
		exit();
	}
}

if(isset($_GET['act']) && isset($_SESSION['wsadmin']['login'])) 
	$act = $_GET['act']; 
else 
{
	echo "{msg:-1}";
	exit();
}

if($act =='start') 
{ 
	if (ServerManager::getOS() === 'WIN') 
		pclose(popen($GLOBALS['ws']['wincommandtostart'], "r"));
	else 
		exec($GLOBALS['ws']['commandtostart']);

	//чтобы ws сервак мог нормально стартануть
	usleep(300000);
	ServerManager::GetJsonStatus($GLOBALS['ws']['pidfile']);
	exit();
} 
elseif($act =='kill')
{
	$pid = ServerManager::is_alive($GLOBALS['ws']['pidfile']);
	exec("taskkill /f /pid $pid");
	if(file_exists($GLOBALS['ws']['pidfile']))
		@unlink($GLOBALS['ws']['pidfile']);

	if (file_exists($GLOBALS['ws']['offfile']))
		@unlink($GLOBALS['ws']['offfile']);
	return;
	
}
elseif($act =='stop')
{	
	$pid = ServerManager::getstatusf($GLOBALS['ws']['pidfile']);
	if($pid<0)
	{ 
		//Процесс не работает и пришел код ошибки, который всегда меньше 0		
		ServerManager::GetJsonStatus($GLOBALS['ws']['pidfile']);
		exit();
	} 
	//создаём offfile только зная что процесс запущен, чтобы избежать глюков при следующем запуске процесса
	file_put_contents($GLOBALS['ws']['offfile'], $pid);//СОХРАНЯЕМ PID в OFF файле
	
	sleep(5);

	//Для того, чтобы полностью отключить сервер, нужно отправить ему сообщение, чтобы у него сработал read
	$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
	if ($socket < 0)
		{/* Ошибка */ }
	$connect = socket_connect($socket, $GLOBALS['ws']['addr'], $GLOBALS['ws']['port']);
	if($connect === false) 
		{ /* echo "Ошибка : ".socket_strerror(socket_last_error())."<br />"; */ } 
	else 
	{ //Общение
		//echo 'Сервер сказал: '; $awr = socket_read($socket, 1024); echo $awr."<br />";
		//$msg = "Hello Сервер!"; echo "Говорим серверу \"".$msg."\"..."; socket_write($socket, $msg, strlen($msg));
	}

	if(isset($socket))	
	socket_close($socket);

	//воткнуть паузу для того, чтобы сервак мог нормально завершить работу
	sleep(5);	

	ServerManager::GetJsonStatus($GLOBALS['ws']['pidfile']);
	exit();

} 
elseif($act=='status')
{ 
	//Если действите старт не произошло и игра не инициализирована, то выходим	
	ServerManager::GetJsonStatus($GLOBALS['ws']['pidfile']);
	exit();
} 
elseif($act=='exit')
{ 
	unset($_SESSION['wsadmin']['login']);
	echo "{msg:-1}";
	exit();
}