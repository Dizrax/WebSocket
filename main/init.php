<?php
function __autoload($classname)
{
	require_once("Classes/$classname.php");
}

define( 'ABSPATH', dirname(__FILE__) . '/../' );

require(ABSPATH."configs/server_config.php");
define( 'SERVER_VER', "v.0.1");
define( 'CONSOLE_ON', true);

$GLOBALS['chat']['img']=ABSPATH."img/";


if (ServerManager::is_alive($GLOBALS['ws']['pidfile']) >0)
	die();	
if(file_exists($GLOBALS['ws']['pidfile']) and !unlink($GLOBALS['ws']['pidfile'])) 				
	exit(-1);	 

error_reporting(E_ALL); 	//Выводим все ошибки и предупреждения
set_time_limit(0);			//Время выполнения скрипта безгранично
ob_implicit_flush();		//Включаем вывод без буферизации
ignore_user_abort(true);	//Выключаем зависимость от пользователя


$config = array(
	'pidfile' => $GLOBALS['ws']['pidfile'],
	'offfile' => $GLOBALS['ws']['offfile'],
	'max_connects_from_ip' => $GLOBALS['ws']['maxconnectsfromip'],
	'host' => $GLOBALS['ws']['addr'],
    'port' => $GLOBALS['ws']['port']
);

$server = new WebSocketServer($config);
$server->start();