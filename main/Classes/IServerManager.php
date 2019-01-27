<?
interface IServerManager
{
	static function GetStatus($pidfile) ;
	static function is_alive($pidfile) ;
	static function GetJsonStatus($pidfile) ;
}