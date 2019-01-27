<?php
class Game
{    
    public $act; 
    public $map;
    public $players;    
    
    function __construct()
    {       
        $this->map = new Map();    
        $this->players = array();             
    }

    public function addPlayer($connection)    
    {
        $this->players[$connection] = new Player();
    }

    public function createUnit($con)
    {
        $this->players[$con]->units[] = new Knight(new Point(0,0), $this->map);
    }
    
    public function wsmsg($con,$data)
    {    
        if($data->cmd == 'start') 
        { 
            return '{"map":'.json_encode($this->map->hexagonsForJson).'}';
        }
        else if ($data->cmd == 'getState')
        {            
            $units = array();
            $enemies = array();
            foreach ($this->players as $player)
            {
                if($player == $this->players[$con])
                {
                    foreach ($player->units as $key=>$unit)
                    {
                        //TODO: уничтожить объект
                        if($unit->health <=0)                         
                            unset($player->units[$key]);                        
                        else                        
                            $units[] = $unit->Serialize();
                    }
                }
                else
                {
                    foreach ($player->units as $key=>$unit)
                    {
                        if($unit->health <=0)                         
                            unset($player->units[$key]); 
                        else 
                            $enemies[] = $unit->Serialize();
                    }
                }
         
            }
            return '{"units":['.implode(',', $units).'], "enemies":['.implode(',',$enemies).']}';
        }    
        else if($data->cmd == 'createUnit')
        {
            $this->createUnit($con);
        }
        else if($data->cmd=='go')
        {  
            foreach($this->players[$con]->units as $u)
            {
                if($u->id == $data->unit)
                {
                    $unit = $u;    
                    break;    
                } 
            }
            if(isset($unit))    
            {
                //TODO:проверка может ли идти
                $path = array();
                foreach($data->path as $point)
                {
                    $path[] = new Point($point->X, $point->Y);
                }
                $unit->giveAction(new Go($path));                
            }            
        }
        else if($data->cmd == 'attack')
        {
            $unit = null;
            foreach($this->players[$con]->units as $u)
            {
                if($u->id == $data->unit)
                {
                    $unit = $u; 
                    break;                           
                }                 
            }
            foreach($this->players as $p)
            {
                foreach($p->units as $u)
                {
                    if($u->id == $data->target)
                    {
                        $target = $u; 
                    } 
                }
            }
            if($unit != null)                                
                $unit->giveAction(new Attack($target));  
        } 
    }	
}