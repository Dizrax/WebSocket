<?php

class WebSocketServer
{
	private $config; 
	private $server;
	private $connects;
	private $users;
	private $ips; 		//Массив IP адресов, запрещаем больше 3х подключений с одного IP	
	private $online;
	private $games; 
	public $players_in_games;

	public function __construct($config) 
	{
        $this->config = $config;
		$this->connects = array();
		$this->users = array();
		$this->ips = array();
		$this->id = 1; //Начинаем с 1го номера
		$this->online = 0; 	
		$this->games = array();
		$this->players_in_games = array();
    }

	public function __destruct() 
	{	
		exit();		
    }

	public function start() 
	{
		$this->server = stream_socket_server("tcp://".$this->config['host'].":".$this->config['port'], $errno, $errstr);

		if (!$this->server)				
			die($errstr. "(" .$errno. ")\n");
		
		$pidfile = $this->config['pidfile'];
		$offfile = $this->config['offfile'];

		file_put_contents($pidfile, getmypid());

		$this->games[] = new Game();   
        
		while (true)
		{	
			Callback::callEvent('gameCycle');		

			//формируем массив прослушиваемых сокетов:
			$read = $this->connects;
			$read[]= $this->server;
            
			@stream_select($read, $write, $except,  1,50000);
			
			if (in_array($this->server, $read)) 
			{
				//принимаем новое соединение и производим рукопожатие
				if (($connect = stream_socket_accept($this->server, 2)) 
					&& $info = $this->handshake($connect)) 
				{
					if(!isset($this->ips[$info['ip']]))					
						$this->ips[$info['ip']] = 1;					
					else 
					{
						$this->ips[$info['ip']]++;
						if($this->ips[$info['ip']]>$this->config['max_connects_from_ip'])												
							continue;						
					}
					
					$this->connects[] = $connect;					
					$this->online ++;
					$this->users[] = $info;			
				}
				unset($read[ array_search($this->server, $read) ]);
			}

			//обрабатываем все соединения
			foreach($read as $connect) 
			{
				$data = fread($connect, 100000);
				
				//соединение было закрыто	
				if (!$data) 
				{ 				
					$uid = array_search($connect, $this->connects);

					if($this->ips[$this->users[$uid]['ip']] == 1) 
						unset($this->ips[$this->users[$uid]['ip']]); 
					else 
						$this->ips[$this->users[$uid]['ip']]--;

					unset($this->players_in_games[$uid]);
					unset($this->games[0]->players[$connect]);
					
					$this->online --;

					unset($this->users[$uid]); 
					unset($this->connects[$uid]); 

					fclose($connect);  
					continue;
				}

				$this->onMessage($connect, $data);
			}

			if(file_exists($offfile))
			{   
				//Если встретили offile то завершаем процесс				
				fclose($this->server);			
				unlink($pidfile);
				if(!unlink($offfile)) 
					exit(-1);							
				exit();		
			}
		}
		
        
	}

	//--------------------------------------------------------------------------------------------------------------

	private function handshake($connect) 
	{
		$info = array();

		$line = fgets($connect);
		$header = explode(' ', $line);
		$info['method'] = $header[0];
		$info['uri'] = $header[1];

		//считываем заголовки из соединения
		while ($line = rtrim(fgets($connect))) {
			if (preg_match('/\A(\S+): (.*)\z/', $line, $matches)) {
				$info[$matches[1]] = $matches[2];
			} else {
				break;
			}
		}

		$address = explode(':', stream_socket_get_name($connect, true)); //получаем адрес клиента
		$info['ip'] = $address[0];
		$info['port'] = $address[1];

		if (empty($info['Sec-WebSocket-Key'])) {
			return false;
		}

		//отправляем заголовок согласно протоколу вебсокета
		$SecWebSocketAccept = base64_encode(pack('H*', sha1($info['Sec-WebSocket-Key'] . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11')));
		$upgrade = "HTTP/1.1 101 Web Socket Protocol Handshake\r\n" .
			"Upgrade: websocket\r\n" .
			"Connection: Upgrade\r\n" .
			"Sec-WebSocket-Accept:".$SecWebSocketAccept."\r\n\r\n";
		fwrite($connect, $upgrade);

		return $info;
	}	

	private function encode($payload, $type = 'text', $masked = false)
	{
		$frameHead = array();
		$payloadLength = strlen($payload);

		switch ($type) {
			case 'text':
				// first byte indicates FIN, Text-Frame (10000001):
				$frameHead[0] = 129;
				break;

			case 'close':
				// first byte indicates FIN, Close Frame(10001000):
				$frameHead[0] = 136;
				break;

			case 'ping':
				// first byte indicates FIN, Ping frame (10001001):
				$frameHead[0] = 137;
				break;

			case 'pong':
				// first byte indicates FIN, Pong frame (10001010):
				$frameHead[0] = 138;
				break;
		}

		// set mask and payload length (using 1, 3 or 9 bytes)
		if ($payloadLength > 65535) {
			$payloadLengthBin = str_split(sprintf('%064b', $payloadLength), 8);
			$frameHead[1] = ($masked === true) ? 255 : 127;
			for ($i = 0; $i < 8; $i++) {
				$frameHead[$i + 2] = bindec($payloadLengthBin[$i]);
			}
			// most significant bit MUST be 0
			if ($frameHead[2] > 127) {
				return array('type' => '', 'payload' => '', 'error' => 'frame too large (1004)');
			}
		} elseif ($payloadLength > 125) {
			$payloadLengthBin = str_split(sprintf('%016b', $payloadLength), 8);
			$frameHead[1] = ($masked === true) ? 254 : 126;
			$frameHead[2] = bindec($payloadLengthBin[0]);
			$frameHead[3] = bindec($payloadLengthBin[1]);
		} else {
			$frameHead[1] = ($masked === true) ? $payloadLength + 128 : $payloadLength;
		}

		// convert frame-head to string:
		foreach (array_keys($frameHead) as $i) {
			$frameHead[$i] = chr($frameHead[$i]);
		}
		if ($masked === true) {
			// generate a random mask:
			$mask = array();
			for ($i = 0; $i < 4; $i++) {
				$mask[$i] = chr(rand(0, 255));
			}

			$frameHead = array_merge($frameHead, $mask);
		}
		$frame = implode('', $frameHead);

		// append payload to frame:
		for ($i = 0; $i < $payloadLength; $i++) {
			$frame .= ($masked === true) ? $payload[$i] ^ $mask[$i % 4] : $payload[$i];
		}

		return $frame;
	}	

	private function decode($data)
	{
		$unmaskedPayload = '';
		$decodedData = array();

		// estimate frame type:
		$firstByteBinary = sprintf('%08b', ord($data[0]));
		$secondByteBinary = sprintf('%08b', ord($data[1]));
		$opcode = bindec(substr($firstByteBinary, 4, 4));
		$isMasked = ($secondByteBinary[0] == '1') ? true : false;
		$payloadLength = ord($data[1]) & 127;

		// unmasked frame is received:
		if (!$isMasked) {
			return array('type' => '', 'payload' => '', 'error' => 'protocol error (1002)');
		}

		switch ($opcode) {
			// text frame:
			case 1:
				$decodedData['type'] = 'text';
				break;

			case 2:
				$decodedData['type'] = 'binary';
				break;

			// connection close frame:
			case 8:
				$decodedData['type'] = 'close';
				break;

			// ping frame:
			case 9:
				$decodedData['type'] = 'ping';
				break;

			// pong frame:
			case 10:
				$decodedData['type'] = 'pong';
				break;

			default:
				return array('type' => '', 'payload' => '', 'error' => 'unknown opcode (1003)');
		}

		if ($payloadLength === 126) {
			$mask = substr($data, 4, 4);
			$payloadOffset = 8;
			$dataLength = bindec(sprintf('%08b', ord($data[2])) . sprintf('%08b', ord($data[3]))) + $payloadOffset;
		} elseif ($payloadLength === 127) {
			$mask = substr($data, 10, 4);
			$payloadOffset = 14;
			$tmp = '';
			for ($i = 0; $i < 8; $i++) {
				$tmp .= sprintf('%08b', ord($data[$i + 2]));
			}
			$dataLength = bindec($tmp) + $payloadOffset;
			unset($tmp);
		} else {
			$mask = substr($data, 2, 4);
			$payloadOffset = 6;
			$dataLength = $payloadLength + $payloadOffset;
		}

		/**
		 * We have to check for large frames here. socket_recv cuts at 1024 bytes
		 * so if websocket-frame is > 1024 bytes we have to wait until whole
		 * data is transferd.
		 */
		if (strlen($data) < $dataLength) {
			return false;
		}

		if ($isMasked) {
			for ($i = $payloadOffset; $i < $dataLength; $i++) {
				$j = $i - $payloadOffset;
				if (isset($data[$i])) {
					$unmaskedPayload .= $data[$i] ^ $mask[$j % 4];
				}
			}
			$decodedData['payload'] = $unmaskedPayload;
		} else {
			$payloadOffset = $payloadOffset - 4;
			$decodedData['payload'] = substr($data, $payloadOffset);
		}

		return $decodedData;
	}

	//--------------------------------------------------------------------------------------------------------------

	
	protected function msg($con, $msg)
	{
		 fwrite($con, $this->encode($msg));
	}

	protected function onMessage($connect, $data) 
	{		    
		$text = $this->decode($data)['payload'];

		if($text == 'status')
		{
			$this->msg($connect, 'Онлайн: '. $this->online .'<br>Время ответа: '.date("Y.m.d-H:i:s"));	
			return;		
		}

		try
		{
			$data = json_decode($text);
		}
		catch (Exception $e)	
		{		
			return;	
		}
			

		if($data->cmd == 'start')
		{
			$game =  $this->games[0];
			$this->players_in_games[array_search($connect, $this->connects)] = $game;
			$game->addPlayer($connect);
		}
		
		if(in_array($data->cmd, array('start', 'getState', 'go', 'attack', 'createUnit')))		
		{
			if(isset($this->players_in_games[array_search($connect, $this->connects)]) )
			{
				$answer = $this->players_in_games[array_search($connect, $this->connects)]->wsmsg($connect,$data);	
				
				$this->msg($connect, $answer);	
			}
		}
		
	}

}

