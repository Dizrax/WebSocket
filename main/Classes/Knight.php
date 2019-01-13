<?
class Knight extends Unit
{

    public __construct($position, $map)
    {
        parent::__construct($position, $map);
    }

    public function attack()
    { 
        //TODO:проверка может ли достать

        $knight_hex = $this->map->getHexagon($this->position);
        $target_hex = $this->map->getHexagon($this->action->target->position);
        if($this->$map->heuristic($knight_hex,$target_hex) <= $this->range)
        {
            if($this->lastActionTime == 0 or ($this->lastActionTime + $this->speed) < microtime(true))
            {
                if(isset($this->action->target) and $this->action->target->health>0)
                    $this->action->target->health = $this->action->target->health - $this->damage;           
                
                if(!isset($this->action->target) or $this->action->target->health<=0)             
                $this->startNextAction();     
                
                
                $this->lastActionTime = microtime(true);
            }
        }
        else
    }

    public function move()
    {   
        if($this->lastActionTime == 0 or ($this->lastActionTime + $this->speed) < microtime(true))
        {
            if(count($this->action->path))
                $this->position = array_pop($this->action->path); 

            if(count($this->action->path)==0)             
                $this->startNextAction();    
                
                
            $this->lastActionTime = microtime(true);
        }
    }    
}