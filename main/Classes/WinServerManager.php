<?
class WinServerManager implements IServerManager
{		

	public static function GetStatus($pidfile) 
	{
		if(file_exists($pidfile)) 
		{
			$pid = file_get_contents($pidfile);			
			
			exec("tasklist /fi \"pid eq $pid\"", $output);
			//Если в результате выполнения больше одной строки то процесс есть! т.к. первая строка это заголовок, а третья уже процесс
			if(count($output) > 3)					
				return true;				
		}
		return false;
	}	

	//-----------------------------------------------------------------------------------
	/**
	* Проверяем наличие процесса, возвращаем либо pid, либо -1 если процесса и файла нет, -2 если файл есть а процесса нет
	*/
	public static function is_alive($pidfile) 
	{
		if( file_exists($pidfile)) 
		{
			$pid = file_get_contents($pidfile);
			$output = null;
			
			exec("tasklist /fi \"pid eq $pid\"", $output);

			if(count($output)>3)		
				return $pid; 
			else 
				return -2;					
		}
		return -1;
	}	
	
	//-----------------------------------------------------------------------------------
	/**
	* Передаём в виде JSON все данные о том, что происходит с процессом ws
	*/
	public static function GetJsonStatus($pidfile) 
	{
		if(file_exists($pidfile)) 
		{
		    $pid = file_get_contents($pidfile);			

		 	//Для Windows всё просто, не смотрим PID и не заморачиваемся с процессами, т.к. это наша отладочная лошадка
			exec("tasklist /fi \"pid eq $pid\"", $output);				

			if(count($output)>3)
			{//Если в результате выполнения больше одной строки то процесс есть! т.к. первая строка это заголовок, а вторая уже процесс
					//Если сокет живой
				echo "{color:\"green\",msg:\"WINDOWS: [<b>".date("Y.m.d-H:i:s")."</b>] Сервер запущен PID =".$pid." ws://".$GLOBALS['ws']['addr'].":".$GLOBALS['ws']['port']."<br />";
									
				echo mb_convert_encoding($output[1], "utf-8", "cp866")."<br />";//строка с информацией о процессе
				echo mb_convert_encoding($output[3], "utf-8", "cp866")."\"}";//строка с информацией о процессе					
								
			} 
			else 				
				echo  "{color:\"red\",msg:\"WINDOWS:[<b>".date("Y.m.d-H:i:s")."</b>] Нет процесса PID =".$pid."<br />\"}";				 
			
			return;
		}

		 //Для Windows всё просто, не смотрим PID и не заморачиваемся с процессами, т.к. это наша отладочная лошадка
		echo '{color:"grey",msg:"WINDOWS: [<b>'.date("Y.m.d-H:i:s").'</b>] Файл процесса не обнаружен <br />ws://'.$GLOBALS['ws']['addr'].":".$GLOBALS['ws']['port'].'"}';
	}		
}
