<?
class Point3D
{
	function  __construct ($x,$y,$z) 
	{
        $this->X = $x;
		$this->Y = $y;
		$this->Z = $z;
    }
}

class Point()
{
    public $X;
    public $Y;
    public __construct($x,$y)
    {
        $this->X = $x;
		$this->Y = $y;
    }

    public function equals(Point $p)
    {
        if($this->X == $p->X and $this->Y == $p->Y)
            return true;
        return false;
    }
}

class Map
{
    public $hexagonsForJson;
    public $hexagons;

    function   __construct () {

        $this->hexagons = array();
        $mapVerSize = 80;
        $mapHorSize = 80;
        for ($y = 0; $y < $mapVerSize; $y++) {
            $arr = array(); 
            $arrJson = array();         
            
            for ($x = 0; $x < $mapHorSize; $x++) {
               
                
                $r = rand(0,100);
                $type = "grass";
                if($r<90)
                {
                    $type = "grass";
                }
                else if ($r<92){
                    $type = "water";
                }
                else {
                    $type = "rock";
                }
                $hexJ = new Hexagon($type);
                $arrJson[] = $hexJ; 
                $hex = new ServerHexagon($type, new Point($x, $y));
                $arr[] = $hex;
               
                if($x==0 and $y==0)
				{
					$hex->position3D = new Point3D(0,0,0);
				}

                if ($x > 0 and $x%2 == 1) 
				{					
					$downLeftHex = $arr[$x-1];
					$hex->downLeft = $downLeftHex;
					$downLeftHex->topRight = $hex;
					
					$hex->neighbors[]=$downLeftHex;
					$downLeftHex->neighbors[]=$hex;
					if($y == 0)
						$hex->position3D = new Point3D($downLeftHex->position3D->X+1, $downLeftHex->position3D->Y, $downLeftHex->position3D->Z-1);
					if($y>0)
					{
						$topLeftHex = $this->hexagons[$y-1][$x-1];
						$hex->topLeft = $topLeftHex;
						$topLeftHex->downRight = $hex;
						
						$hex->neighbors[]=$topLeftHex;
						$topLeftHex->neighbors[]=$hex;
						if(isset($this->hexagons[$y-1][$x+1]))
						{
							$topRightHex = $this->hexagons[$y-1][$x+1];
							$hex->topRight = $topRightHex;
							$topRightHex->downLeft = $hex;
							
							$hex->neighbors[]=$topRightHex;
							$topRightHex->neighbors[]=$hex;
						}
					}
				}

				if($y>0)
				{
					$topHex = $this->hexagons[$y-1][$x];
					$hex->top = $topHex;				
					$topHex->down = $hex;
					
					$hex->neighbors[]=$topHex;
                    $topHex->neighbors[]=$hex;	
                    $hex->position3D = new Point3D($topHex->position3D->X, $topHex->position3D->Y-1, $topHex->position3D->Z+1);				
					
				}
				
				if ($x> 0 and $x % 2 == 0) 
				{					
					$topLeftHex = $arr[$x-1];
					$hex->topLeft = $topLeftHex;
					$topLeftHex->downRight = $hex;
					
					$hex->neighbors[]=$topLeftHex;
                    $topLeftHex->neighbors[]=$hex;
                    
                    if(y == 0)
                        hex->position3D = new Point3D(topLeftHex->position3D->X+1, topLeftHex->position3D->Y-1, topLeftHex->position3D->Z);
					
				}					
			
            }
            $this->hexagons[] = $arr;
            $this->hexagonsForJson[] = $arrJson;
        }
    }

    function createPath($from,$to)
    {	
        $start = $from;
      
        $goal = $to;
        
        $frontier = [];
        $frontier []= ['key' => start, 'value' => 0];
        $came_from = [];
        $came_from []= ['key' => start, 'value' => null });
        $cost_so_far = [];
        cost_so_far []= ['key' => start, 'value' => 0 })
        
        while (count($frontier) != 0)
        {
            $mins = $this->getSmaller($frontier);			
            $pos = $mins[count($mins)-1];
            $current =  $frontier[$pos]['key'];
            for($i = count($mins)-2; $i>=0 ; $i--)
            {
                if(abs($current->position->Y - $goal->position->Y) > abs($frontier[$mins[$i]]['key']->position->Y - $goal->position->Y))
                {
                    $pos = $mins[$i];
                    $current =  $frontier[$pos]['key'];
                }
            }
            
            array_shift($frontier); 
            if ($current->position->equals($goal->position))
            {			   
                $current = $goal; 
                $path = [$current];
                while (!$current->position->equals($start->position))
                {
                    $current = $this->searchDict($came_from, $current);
                    $path []= $current;
                }	
                array_pop($path);	
                return $path;			  
            }
            for ($current->neighbors as $num) 
            {	
                $next = $current->neighbors[$num];
                if($next->type=="grass")	
                {
                    $new_cost = $this->searchDict($cost_so_far, $current)+1;
                    $value =  $this->searchDict($came_from, $next) ;	   
                    $cost = $this->searchDict($cost_so_far, $next);
                    if ( $value == null or $new_cost  < $cost)
                    {
                        $cost_so_far []= ['key' =>$next, 'value' =>$new_cost];
                        $priority = $new_cost + $this->heuristic($goal->position3D, $next->position3D);
                        
                        $frontier []= ['key' => $next, 'value' => $priority];
                        $came_from []= ['key' => $next, 'value' => $current ];
                    }
                }
            }
        }
        return null;	
    }

    function getHexagon(Point $p)
    {
        return $this->hexagons[$p->Y][$p->X];
    }

    function heuristic($a, $b)	
    {	
        return max([abs($a->X - $b->X), abs($a->Y - $b->Y), abs($a->Z - $b->Z)]);
    }

    function getSmaller($dictArray)
    {	
        $min = count($dictArray)-1;
        $mins = [$min];
        for($i = count($dictArray)-2; $i>=0 ; $i--)
        {
            if ($dictArray[$i]['value'] == $dictArray[$min]['value'])
            {
                array_push($mins,$i);
            }
            if ($dictArray[$i]['value'] < $dictArray[$min]['value']) 
            {
                $mins = [];
                $min = $i;
                array_push($mins,$i);
            }
        }	
        return $mins;
    }
    
    function searchDict($dictArray, $target)
    {
        for($i = count($dictArray)-1; $i>=0 ; $i--)
        {
            if ($dictArray[i]['key'] == $target) 
                return $dictArray[$i]['value'];
        }
        return null;
    }
}