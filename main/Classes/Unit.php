<?
class Unit implements IJsonSerializable
{
    public $id;
    public $health;
    public $damage;
    public $range;      
    public $position;
    public $state;
    public $speed = 2;

    public $lastActionTime = 0;
    public $actionId;
    public $actionQueue;
    
    public $map;
    protected $idStartAction;
    protected $action; 

    function __construct ($position, $map) 
    {
        $this->map = $map;
        $this->position = $position;
        $this->id = Id::get();        
        $this->damage = 50;
        $this->health = 100;
        $this->actionQueue = array();
        $this->idStartAction=Callback::subscribe('gameCycle', 'tryStartNextAction', $this, null);
    }    

    function remove()
    {
        Callback::unsubscribe('gameCycle', $this->idStartAction);
    }

    public function giveAction($action)
    {
        if(is_a ($action, 'Go'))
        {
            $this->actionQueue = [$action];
            $this->startNextAction();
        }
        else $this->actionQueue[] = $action;
    }

    public function tryStartNextAction()
    {
        if($this->action == null and count($this->actionQueue)>0)            
        {
            $this->startNextAction();
        }
    }

    public function startNextAction()
    {
        if($this->action != null)            
        {
            Callback::unsubscribe('gameCycle', $this->actionId);
            $this->action = null;
        }
        if(count($this->actionQueue)>0)
        {            
            $this->action = array_shift($this->actionQueue);
            if(is_a ($action, 'Go'))
            {
                $this->actionId=Callback::subscribe('gameCycle', 'move', $this, null);
            }
            if(is_a ($action, 'Attack'))
            {
                $this->actionId=Callback::subscribe('gameCycle', 'attack', $this, null);
            }           
        }
    }   

    protected function Prepare()
    {
        $result = new stdClass();
        $result-> $id = $this->id;
        $result-> $health = = $this->health;
        $result-> $damage = $this->damage;
        $result-> $range = $this->range;      
        $result-> $position = $this->position;
        $result-> $state = $this->state;
        $result-> $speed = $this->speed;
        return $result;

    }
    
    public function ConvertToJson()
    {
        //return json_encode(get_object_vars($this));
        return json_encode(Prepare());
    }
}