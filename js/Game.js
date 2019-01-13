"use strict"; 

class Unit
{
	constructor(id,pos) 
	{
		this.id = id;
		this.position = pos;
		this.path = null;		
		this.view_dx = 0;
		this.view_dy = 0;
		this.start_go_time = null;
		this.previous_pos = null;
		this.viewPath = null;
	}
}

class Hexagon 
{
	constructor() 
	{
        this.topLeft = null;
        this.top = null;
        this.topRight = null;
        this.downLeft = null;
        this.down = null;
        this.downRight = null;
        this.points = null;
		this.type = "grass";
		this.neighbors = [];
		this.position = null;    
		this.position3D = null;    
	}
	
	static Height(outerRadius) 
	{
        return Math.tan(60 * Math.PI / 180) * outerRadius / 2 / 4;
	}
	
	IsInside(point) 
	{
        var ps = this.points;
        var p = point;

        if (new Line(ps[1], ps[0]).checkIntersection(p) == -1 &&
            p.Y < ps[1].Y &&
            new Line(ps[2], ps[3]).checkIntersection(p) == -1 &&
            new Line(ps[3], ps[4]).checkIntersection(p) == 1 &&
            p.Y > ps[4].Y &&
            new Line(ps[5], ps[0]).checkIntersection(p) == 1) {
            return true;
        }
        return false;
    }
}

class Line 
{
	constructor(point1, point2) 
	{
        this.Point1 = point1;
        this.Point2 = point2;
	}
	
	checkIntersection(point) 
	{
        var value = (point.X - this.Point1.X) * (this.Point2.Y - this.Point1.Y) / (this.Point2.X - this.Point1.X) + this.Point1.Y;
        if (point.Y > value) return 1;
        if (point.Y < value) return -1;
        return 0;
    }
}

class Point3D
{
	constructor(x,y,z) 
	{
        this.X = x;
		this.Y = y;
		this.Z = z;
    }
}

class Point 
{   
	constructor(x,y) 
	{
        this.X = x;
        this.Y = y;
    }
	
	equals(point)
	{
		return this.X==point.X && this.Y==point.Y;
	}
}
var img = new Image();
img.src = '../hex_tiles/grass.png';
var rimg  = new Image();
rimg.src = '../hex_tiles/rock.png';
var wimg  = new Image();
wimg.src = '../hex_tiles/ocean1.png';
var eimg  = new Image();
eimg.src = '../hex_tiles/alienPink.png';
var uimg  = new Image();
uimg.src = '../hex_tiles/Рыцарь.png';

		

function drawHex(hex, strokeStyle = "black") 
{
	if(hex.type == 'grass')
	{		
		ctx.drawImage(img, hex.points[0].X, hex.points[5].Y,outerHexRadius*2+3,Hexagon.Height(outerHexRadius)*2+1);
		/*var points = hex.points;
		ctx.beginPath();
		var i = points.length-1;
		ctx.moveTo(points[i].X, points[i].Y);
		for (--i; i >=0; i--) 
		{
			ctx.lineTo(points[i].X, points[i].Y);
		}
		ctx.closePath();
		if (strokeStyle) 
		{
			ctx.lineWidth = strokeStyle!= "black" ? 3: 1;
			ctx.strokeStyle = strokeStyle;
			ctx.stroke();
		}*/
	
	
	  			
	}
	else if(hex.type == 'rock')
	{		
		ctx.drawImage(rimg, hex.points[0].X, hex.points[5].Y,outerHexRadius *2+3, Hexagon.Height(outerHexRadius)*2+1);			
	}
	else if(hex.type == 'water')
	{		
		ctx.drawImage(wimg, hex.points[0].X, hex.points[5].Y,outerHexRadius *2+3, Hexagon.Height(outerHexRadius)*2+1);			
	}
	else{
		
		/*
	var points = hex.points;
    ctx.beginPath();
	var i = points.length-1;
    ctx.moveTo(points[i].X, points[i].Y);
	for (--i; i >=0; i--) 
	{
        ctx.lineTo(points[i].X, points[i].Y);
    }
    ctx.closePath();
	if (strokeStyle) 
	{
		ctx.lineWidth = strokeStyle!= "black" ? 3: 1;
        ctx.strokeStyle = strokeStyle;
        ctx.stroke();
    }
	
	
	ctx.fillStyle = colors[hex.type];
	ctx.fill();    */
	}
}

function checkSelection(e) 
{
	if(!IsSearchingIntersection && !moved) 
	{
		IsSearchingIntersection = true;
		
		selection = intersection(mousePos);
		selectedUnit = null;
		if(selection!= null)
		{
			for (var id in units)
			{
				if(units[id].position.equals(selection))
				{
					selectedUnit = units[id];
				}
			}	
		}
		
		IsSearchingIntersection = false;
		drawMap();
	}
	moved = false;
}

function intersection(point)
{
	for (var i = 0; i < hexagons.length; i++) {
        for (var j = 0; j < hexagons[i].length; j++) {
            if (hexagons[i][j].IsInside(point)) {
				return hexagons[i][j].position; 
            }
        }
    }
	return null;	
}

var units = {};
var enemies = {};

var selectedUnit = null;

var ctx = document.querySelector('#map').getContext('2d');
var uctx = document.querySelector('#units').getContext('2d');
var hexagons = new Array();

ctx.lineWidth = 1;
uctx.lineWidth = 1;
var outerHexRadius = 128;
var startX = 10;
var startY = 0;
var hexHeight = Hexagon.Height(outerHexRadius);

var IsSearchingIntersection = false;
var moved = false;
var mousedown = null;
var selection = null;
var mousePos = null;

document.querySelector('#units').onclick = checkSelection;
document.querySelector('#units').onmousedown = mousedownHandler;
document.querySelector('#units').onmousemove = mousemoveHandler;
document.querySelector('#units').onmouseup = mouseupHandler;
document.querySelector('#units').onmousewheel = mousewheelHandler;
document.querySelector('#createUnit').onclick = createUnit;

document.querySelector('#units').addEventListener('contextmenu', function(e) 
{
	e.preventDefault();
	if(selectedUnit != null)	
	{
		var pos = selectedUnit.position;
		var path= createPath(pos, mousePos);

		var target = intersection(mousePos);
		
		var attack_cmd = null;
		for(var id in enemies)
		{
			var unit = enemies[parseInt(id)];
			if(unit.position.equals(target))
			{
				path = path.slice(1);
				
				attack_cmd = {cmd:"attack", unit:selectedUnit.id, target:unit.id};
				break;
			}
		}

		var go_cmd= {cmd:"go", unit:selectedUnit.id, path:[]};
		if(attack_cmd == null && path != null)
		{				
			for (var num in path)			
				go_cmd.path.push({X:path[num].position.X ,Y:path[num].position.Y });
		}

		var msg = '';
		if(attack_cmd != null)
			msg = JSON.stringify(attack_cmd)
		else 
			msg = JSON.stringify(go_cmd)
		socket.send(msg);	
	}
}, false);

function createPath(from,to)
{	
	var start = hexagons[from.Y][from.X];
	var end = intersection(to);
	if(end == null)
		return null;
	var goal = hexagons[end.Y][end.X];
	
	var frontier = [];
	frontier.push({key:start, value: 0});
	var came_from = [];
	came_from.push({key: start, value: null });
	var cost_so_far = [];
	cost_so_far.push({key: start, value: 0 })
	
	while (frontier.length != 0)
	{
		var mins = getSmaller(frontier);			
		var pos = mins[mins.length-1];
		var current =  frontier[pos].key;
		for(var i = mins.length-2; i>=0 ; i--)
		{
			if(Math.abs(current.position.Y - goal.position.Y) > Math.abs(frontier[mins[i]].key.position.Y - goal.position.Y))
			{
				pos = mins[i];
				current =  frontier[pos].key;
			}
		}
		
		frontier.splice(pos , 1); 
		if (current.position.equals(goal.position))
		{			   
			current = goal; 
			var path = [current];
			while (!current.position.equals(start.position))
			{
				current = searchDict(came_from, current);
				path.push(current);
			}	
			path.pop();	
			return path;			  
		}
		for (var num in current.neighbors) 
		{	
			var next = current.neighbors[num];
			if(next.type=="grass")	
			{
				var new_cost = searchDict(cost_so_far, current)+1;
				var value =  searchDict(came_from, next) ;	   
				var cost = searchDict(cost_so_far, next);
				if ( value == null || new_cost  < cost)
				{
					cost_so_far.push({key:next, value:new_cost});
					var priority = new_cost + heuristic(goal.position3D, next.position3D);
					
					frontier.push({key:next, value: priority});
					came_from.push({key: next, value: current });
				}
			}
		}
	}
	return null;	
}

function heuristic(a, b)	
{	
	return Math.max(Math.abs(a.X - b.X), Math.abs(a.Y - b.Y), Math.abs(a.Z - b.Z))
}

function getSmaller(dictArray)
{	
	var min = dictArray.length-1;
	var mins = [min];
	for(var i = dictArray.length-2; i>=0 ; i--)
	{
		if (dictArray[i].value == dictArray[min].value)
		{
			mins.push(i);
		}
		if (dictArray[i].value < dictArray[min].value) 
		{
			mins = []
			min = i;
			mins.push(i);
		}
	}	
	return mins;
}
   
function searchDict(dictArray, target)
{
	for(var i = dictArray.length-1; i>=0 ; i--)
	{
		if (dictArray[i].key == target) return dictArray[i].value;
	}
	return null;
}

function drawMap() {
	
	var x = startX;
	var date1 = new Date();
	var temp_y = startY;
	var y = temp_y;
			
	var w = ctx.canvas.clientWidth;	
	var h = ctx.canvas.clientHeight ;
	ctx.clearRect(0, 0, ctx.canvas.clientWidth, ctx.canvas.clientHeight);
    hexHeight = Hexagon.Height(outerHexRadius);
	var dh = hexHeight*2;
	var hr = outerHexRadius/2;
	var pr = outerHexRadius * 1.5;
	for (var i = 0; i < hexagons.length; i++) 
	{
		x = startX;
		
        for (var k = 1; k >=0; k--) 
		{
			for (var j = k; j < hexagons[i].length; j=j+2) 
			{
				var hex = hexagons[i][j];
				if (j % 2 == 0) 
				{
					y = hexHeight + temp_y;					
				} 
				else 
				{					
					y = temp_y;
				}
				x = startX +(j-1)*(pr);
				hex.points = [];
				hex.points[0] = new Point(x, y + hexHeight);
				hex.points[1] = new Point(x + hr, y + dh);
				hex.points[2] = new Point(x + pr, y + dh);
				hex.points[3] = new Point(x + outerHexRadius * 2, y + hexHeight);
				hex.points[4] = new Point(x + pr, y);
				hex.points[5] = new Point(x + hr, y);
				
				var canDraw = false;
				for (var num in hex.points)
				{
					var p = hex.points[num];
					if(p.X <w  &&  p.X>0 && p.Y < h && p.Y > 0)
					{
						canDraw = true;		
						break;
					}
				}
					if (canDraw)
						drawHex(hex);
				x += pr;
			}  
		}
		temp_y += dh;  
	}
	
	drawUnits();
	
	if( selection != null )
	{
		var j = selection.X;
		var i = selection.Y;
		var hex = hexagons[i][j];
		drawHex(hex, 'red');
	}	
	var date2 = new Date();
				var diff = date2 - date1;
}

function mousedownHandler(e) {
    mousedown = new Point(e.layerX, e.layerY);
}

function mousewheelHandler(e) {
	var delta = 10;
  if(e.deltaY >0 && outerHexRadius>32)
  {
	  
	  startX += (e.layerX - startX)/outerHexRadius * (outerHexRadius+1) - (e.layerX - startX);
	  startY += (e.layerY - startY)/outerHexRadius * (outerHexRadius+1) - (e.layerY - startY);
	  outerHexRadius-=delta;
}
  else if (e.deltaY < 0 && outerHexRadius<256) {
	  startX += (e.layerX - startX)/outerHexRadius * (outerHexRadius-1) - (e.layerX - startX);
	  startY += (e.layerY - startY)/outerHexRadius * (outerHexRadius-1) - (e.layerY - startY);
	  outerHexRadius+= delta;
	  }
  
  drawMap();
}

function mousemoveHandler(e) {
  IsSearchingIntersection = false;
  mousePos = new Point(e.layerX, e.layerY);
    if (mousedown != null && !mousedown.equals(mousePos)/*(Math.abs(mousedown.X-e.x)+ Math.abs(mousedown.Y-e.y)>4)*/) { 
		moved = true;
		
		
        startX += mousePos.X - mousedown.X;
        startY += mousePos.Y - mousedown.Y;
		mousedown = mousePos;
        drawMap();
		
	}
	else if(selectedUnit != null)
	{
		selectedUnit.path = createPath(selectedUnit.position, mousePos); 
		var pos = selectedUnit.position;
		if(selectedUnit.path != null)
		{
			selectedUnit.path.push(hexagons[pos.Y][pos.X]);
			console.log(e.layerX +', ' + e.layerY);
			var path = selectedUnit.path;
			//console.log(hexagons[path[0].position.Y][path[0].position.X].points[0]);
		}
		
	}
}



function mouseupHandler(e) {    
    mousedown = null;
}
function set_screen_size()
{
	document.querySelector('#units').width = window.innerWidth-10;
	document.querySelector('#units').height = window.innerHeight-10;
	document.querySelector('#map').width = window.innerWidth-10;
	document.querySelector('#map').height = window.innerHeight-10;
}
set_screen_size();
window.onresize = function(event) {
	set_screen_size();
	drawMap();
};
function createUnit(e)
{
	socket.send('{"cmd":"createUnit"}');    
}

////////////////////////////////////////////////////////////////////////////
var wssaddr = 'ws://localhost:8889';
var socket;
var wssinit_interval = null;
var wsstatus_interval = null;

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
	//document.getElementById('ws-status-proc').innerHTML = 'disconnected';
	if(wssinit_interval == null)
		wssinit_interval = setInterval (initWS, 1000);
}

function connectionOpen() 
{		
	if (wssinit_interval != null) 
	{
		clearInterval (wssinit_interval);
		wssinit_interval = null;
	}
	if (socket.readyState === 1) 
		socket.send('{"cmd":"start"}'); 
	wsstatus_interval = setInterval (func, 200);	
}

function func() 
{
	try 
	{
		if (socket.readyState === 1) 
			socket.send('{"cmd":"getState"}');  
		
	} 
	catch (err) 
	{
	  	document.write(err);
	}
}

window.addEventListener('load', function () {
	sonclose();
}, false);



function messageReceived(e) 
{
	//console.log("msg receive:"+e.data);
	var parsedData = {};
	try
	{
		parsedData =JSON.parse(e.data);
	}	
	catch
	{
		var a = 1;
	}
	 
	if(parsedData['map'] != undefined)
	{
		var array = parsedData['map'];
		hexagons = new Array(array.length);
		for (var y =0; y < array.length; y++)
		{
			var arr =  new Array(array[y].length);
			for (var x =0; x < array[y].length; x++)
			{
				
				var hex = new Hexagon();
				arr[x] = hex;
				hex.type = array[y][x].type;
				hex.position = new Point(x,y);
				if(x==0 && y==0)
				{
					hex.position3D = new Point3D(0,0,0);
				}

				if (x > 0 && x%2 == 1) 
				{					
					var downLeftHex = arr[x-1];
					hex.downLeft = downLeftHex;
					downLeftHex.topRight = hex;
					
					hex.neighbors.push(downLeftHex);
					downLeftHex.neighbors.push(hex);
					if(y == 0)
						hex.position3D = new Point3D(downLeftHex.position3D.X+1, downLeftHex.position3D.Y, downLeftHex.position3D.Z-1);
					if(y>0)
					{
						var topLeftHex = hexagons[y-1][x-1];
						hex.topLeft = topLeftHex;
						topLeftHex.downRight = hex;
						
						hex.neighbors.push(topLeftHex);
						topLeftHex.neighbors.push(hex);
						if(hexagons[y-1][x+1] != undefined)
						{
							var topRightHex = hexagons[y-1][x+1];
							hex.topRight = topRightHex;
							topRightHex.downLeft = hex;
							
							hex.neighbors.push(topRightHex);
							topRightHex.neighbors.push(hex);
						}
					}
				}

				if(y>0)
				{
					var topHex = hexagons[y-1][x];
					hex.top = topHex;				
					topHex.down = hex;
					
					hex.neighbors.push(topHex);
					topHex.neighbors.push(hex);

					hex.position3D = new Point3D(topHex.position3D.X, topHex.position3D.Y-1, topHex.position3D.Z+1);
					
				}
				
				if (x> 0 && x % 2 == 0) 
				{					
					var topLeftHex = arr[x-1];
					hex.topLeft = topLeftHex;
					topLeftHex.downRight = hex;
					
					hex.neighbors.push(topLeftHex);
					topLeftHex.neighbors.push(hex);
					
					if(y == 0)
						hex.position3D = new Point3D(topLeftHex.position3D.X+1, topLeftHex.position3D.Y-1, topLeftHex.position3D.Z);
					
				}					
			}
			hexagons[y] = arr;
		}
		drawMap();
	}
	
	if(parsedData['units'] != undefined)
	{		
		var units_from_server = parsedData['units'];
		set_units_position(units_from_server, units);
	}
	if(parsedData['enemies'] != undefined)
	{
		var enemies_from_server = parsedData['enemies'];
		set_units_position(enemies_from_server, enemies);
	}	
}

function set_units_position(from, to)
{
	var ids =[];
	for(var i =0; i < from.length; i++)
	{
		var x = from[i]["position"]['x'];
		var y = from[i]["position"]['y'];
		var id =from[i]['id'];
		var p = new Point(x,y);
		var u = null;
		ids.push(id);
		if(to[id] != undefined) 
		{
			u = to[id];
			if(!u.position.equals(p))
			{	
				u.start_go_time = Date.now();
				u.previous_pos = u.position;
				u.position = p;	
				if(u == selectedUnit)
				{
					if(from[i].action != undefined && from[i].action.path != undefined)
					{
						var viewPathFromSrv = from[i].action.path;
						var viewPath = [];
						for(var j =0; j < viewPathFromSrv.length; j++)
						{
							var point = viewPathFromSrv[j];
							viewPath.push(hexagons[point.y][point.x]);
						}
						
						

						selectedUnit.viewPath = viewPath;
					}

					if( selectedUnit.path != null)
						selectedUnit.path = createPath(selectedUnit.previous_pos, mousePos);
				}
			}
		}
		else 
		{
			u = new Unit(id, p);			
			to[id] = u;				
		}
	}
	//удаление, чей статус не пришел
	for (var id in to)
	{
		if(!ids.includes(parseInt(id)))
		{	
			delete to[id];
		}
	}
	
	drawUnits();		
}

function drawUnits()
{
	uctx.clearRect(0, 0, uctx.canvas.clientWidth, uctx.canvas.clientHeight);
	if(selectedUnit!= null)
	{
		var path = selectedUnit.path;
		if( path!= null)
		{			
			for(var i =0; i< path.length-1; i++)
			{	var hex =path[i];
				var next_hex = path[i+1];
				uctx.beginPath();
				uctx.moveTo(hex.points[0].X+outerHexRadius,hex.points[5].Y+Hexagon.Height(outerHexRadius));
				uctx.lineTo(next_hex.points[0].X+outerHexRadius,next_hex.points[5].Y+Hexagon.Height(outerHexRadius));
				uctx.stroke();
			}
		}

		 path = selectedUnit.viewPath;
		if( path!= null)
		{			
			for(var i =0; i< path.length-1; i++)
			{	var hex =path[i];
				var next_hex = path[i+1];
				uctx.beginPath();
				uctx.moveTo(hex.points[0].X+outerHexRadius,hex.points[5].Y+Hexagon.Height(outerHexRadius));
				uctx.lineTo(next_hex.points[0].X+outerHexRadius,next_hex.points[5].Y+Hexagon.Height(outerHexRadius));
				uctx.stroke();
			}
		}

		var p = selectedUnit.position;
		var points = hexagons[p.Y][p.X].points;
		uctx.beginPath();
		var i = points.length-1;
		uctx.moveTo(points[i].X, points[i].Y);
		for (--i; i >=0; i--) 
		{
			uctx.lineTo(points[i].X, points[i].Y);
		}
		uctx.closePath();
		
		uctx.lineWidth =  3;
		uctx.stroke();
		
	
	
	   
	}
	for (var id in units)
	{		
		var unit = units[id];
		if(unit.start_go_time != null)
		{
			moveUnit(0.5, unit);
		}
		var pos = unit.position;
		var points = hexagons[pos.Y][pos.X].points;
		var dx = unit.view_dx;
		var dy = unit.view_dy;
		var w = outerHexRadius /2;
		var h = w / 40 * 66;
			uctx.drawImage(uimg, points[0].X+dx+w/2*3, points[0].Y+dy -h/*+Hexagon.Height(outerHexRadius)*/,outerHexRadius/4*3,h);	
		
			
		/*
		uctx.beginPath();
		var i = points.length-1;
		uctx.moveTo(points[i].X+dx, points[i].Y+dy);
		for (--i; i >=0; i--) {
			uctx.lineTo(points[i].X+dx, points[i].Y+dy);
		}
		uctx.closePath();
		uctx.strokeStyle = 'black';
		uctx.stroke();
		
		uctx.fillStyle = 'black';
		uctx.fill();*/
		
	}
	for (var id in enemies)
	{
		var unit = enemies[id];
		if(unit.start_go_time != null)
		{
			moveUnit(0.5, unit);
		}
		var pos = unit.position;
		var points = hexagons[pos.Y][pos.X].points;
		var dx = unit.view_dx;
		var dy = unit.view_dy;
		/*
		uctx.beginPath();
		var i = points.length-1;
		uctx.moveTo(points[i].X+dx, points[i].Y+dy);
		for (--i; i >=0; i--) {
			uctx.lineTo(points[i].X+dx, points[i].Y+dy);
		}
		uctx.closePath();
		uctx.strokeStyle = 'black';
		uctx.stroke();
		
		uctx.fillStyle = 'red';
		uctx.fill();*/
		var w = outerHexRadius /2;
		var h = w / 40 * 66;
		uctx.drawImage(eimg, points[0].X+dx+w/2*3, points[0].Y+dy-h+20,w,h);
	}
	
}

function moveUnit(speed, unit)
{
	var dt = Date.now() - unit.start_go_time;	
	if(dt > 2000)
	{		
		unit.start_go_time = null;		
		unit.view_dx = 0;
		unit.view_dy = 0;		
		return;						
	}
	var e = hexagons[unit.position.Y][unit.position.X].points[0];
	var s = hexagons[unit.previous_pos.Y][unit.previous_pos.X].points[0];

	unit.view_dx = (s.X - e.X) *(2000-dt)/1000*speed;
	unit.view_dy = (s.Y - e.Y) *(2000-dt)/1000*speed;	
}

setInterval(drawUnits,30);

