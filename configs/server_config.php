<?php
$GLOBALS['ws']['pidfile']=ABSPATH."main/pid_file";
$GLOBALS['ws']['offfile']=ABSPATH."main/off_file";

$GLOBALS['ws']['commandtostart'] = "php -q ".ABSPATH."main/init.php &";
$GLOBALS['ws']['wincommandtostart'] = "start /b C:\openserver\modules\php\PHP-7.2-x64\php.exe -q ".ABSPATH."main/init.php";

$GLOBALS['ws']['addr'] = '127.0.0.1'; 
$GLOBALS['ws']['port'] = 8889; 
$GLOBALS['ws']['maxconnectsfromip'] = 7;
