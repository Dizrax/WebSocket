(function () {
	
	var srvaddress = 'http://localhost/admin/';
	var adminaddress = srvaddress+'ajax.php?';
	var xhttp, xhttpauth;

	var wssaddr = 'ws://localhost:8889';
	var socket;
	var wssinit_interval = null;
	var wsstatus_interval = null;
	

    var init = function () 
	{
		if(document.getElementById('ws-stop') == null) 
		{
			document.getElementById('gologin').onclick = function () 
			{
				document.getElementById('gologin').disabled = true;
				xhttpauth = new XMLHttpRequest();
				xhttpauth.open('POST',adminaddress,true);
				xhttpauth.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

				var params = 'login=' + encodeURIComponent(document.getElementById("login").value) +
				'&pass=' + encodeURIComponent(document.getElementById("pass").value);

				xhttpauth.send(params);
				xhttpauth.onreadystatechange=function()
				{
					if (xhttpauth.readyState == 4)
					{						 
						var json=eval( '('+xhttpauth.responseText+')' ); 

						if (json.msg == 1)						 	
							location.reload();
						else 							
							document.getElementById('loginmsg').innerHTML = 'Не верный логин или пароль';
						
						document.getElementById('gologin').disabled = false;
					}
				}
			}; 			

			return; 
		}

		loaddataloop();

		document.getElementById('ws-start').onclick = function () 
		{
			loaddata('act=start');
		}; 

		document.getElementById('ws-stop').onclick = function () 
		{
			loaddata('act=stop');
		}; 

		document.getElementById('ws-kill').onclick = function ()
		 {
			loaddata('act=kill');
		}; 		

		document.getElementById('ws-exit').onclick = function () 
		{
			loaddata('act=exit');
		}; 

    };

   
	var loaddata = function(act) 
	{
		if(document.getElementById('ws-stop')==null) 
			return; 
		
        xhttp = new XMLHttpRequest();
        xhttp.open('GET',adminaddress+act,true);
        xhttp.send();
		xhttp.onreadystatechange=function()
		{
			if (xhttp.readyState==4)
			{				
				console.log(xhttp.responseText);
                
				var json = eval( '('+xhttp.responseText+')' ); 

				if (json.msg == -1) 
				location.reload();
				document.getElementById('ws-status').style.color = json.color;
				document.getElementById('ws-status').innerHTML = json.msg;		
			}
        }
    };   


	var loaddataloop = function () 
	{
		if(document.getElementById('ws-stop')==null) 
			return; 
		loaddata('act=status');       
        setTimeout(loaddataloop, 1000);
	};
	
	

	var initWS = function () 
	{			
		socket = new WebSocket(wssaddr);
		socket.onopen = connectionOpen; 
		socket.onmessage = messageReceived; 		
		socket.onclose = sonclose;	
	};

	function sonclose(e)
	{
		if (wsstatus_interval != null) 
		{
			clearInterval (wsstatus_interval);
			wsstatus_interval = null;
		}
		document.getElementById('ws-status-proc').innerHTML = 'disconnected';
		if(wssinit_interval == null)
			wssinit_interval = setInterval (initWS, 3000);
	}



	function connectionOpen() 
	{		
		if (wssinit_interval != null) 
		{
			clearInterval (wssinit_interval);
			wssinit_interval = null;
		}		
		wsstatus_interval = setInterval (func, 1000);	
	}

	function messageReceived(e) 
	{
		document.getElementById('ws-status-proc').innerHTML = e.data;
	}

	function func() 
	{
		try 
		{
			if (socket.readyState === 1) 
				socket.send('status');  
		} 
		catch (err) 
		{
			document.getElementById('ws-status-proc').innerHTML = err;
		}
	}	

    return {       
        load : function () {

            window.addEventListener('load', function () {
                init();
			}, false);

			window.addEventListener('load', function () {
				sonclose();
			}, false);
        }
	}
	
})().load();


////////////////////////////////////////////////////////////////////////////
