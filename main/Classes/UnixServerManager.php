<?
class UnixServerManager implements IServerManager
{		
	public static function GetStatus($pidfile) 
	{
		if(file_exists($pidfile)) 
		{			
			exec("ps -o user,pid,pcpu,pmem,vsz,rssize,tname,stat,stime,command -p ".$pid, $output);

			if(count($output) > 1)	
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
		
			exec("ps -o user,pid,pcpu,pmem,vsz,rssize,tname,stat,stime,command -p ".$pid, $output);

			if(count($output)>1)	
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
			
			//получаем статус процесса
			exec("ps -o user,pid,pcpu,pmem,vsz,rssize,tname,stat,stime,command -p ".$pid, $output);
			
			if(count($output)>1)
			{//Если в результате выполнения больше одной строки то процесс есть! т.к. первая строка это заголовок, а вторая уже процесс
				
				echo "{color:\"green\",msg:\"*NIX:[<b>".date("Y.m.d-H:i:s")."</b>] ws server is running with PID =".$pid." ws://".$GLOBALS['ws']['addr'].":".$GLOBALS['ws']['port']."<br />";
				
				echo $output[0]."<br />";//строка с информацией о процессе
				echo $output[1]."\"}";//строка с информацией о процессе
				
			} 
			else 				
				echo  '{color:"red",msg:"*NIX:[<b>'.date("Y.m.d-H:i:s")."</b>] ws server is down cause abnormal reason with PID =".$pid.'<br />"}';						
			
			return;
		}
			 
		echo '{color:"grey",msg:"*NIX: [<b>'.date("Y.m.d-H:i:s").'</b>] ws server is off, press start<br />ws://'.$GLOBALS['ws']['addr'].":".$GLOBALS['ws']['port'].'"}';
	}		
}