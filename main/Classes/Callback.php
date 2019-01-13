<?
class Callback {

    static public $handlers = array();

    //Регистрирует события (функции обратного вызова).
    static function subscribe($event_name, $method, $obj, $args)
    {
        if (!array_key_exists($event_name, self::$handlers))     
            self::$handlers[$event_name ] =  array();            
        
        $handler = array('method'=>$method, 'obj' => $obj, 'args' => $args);
        self::$handlers[$event_name][]= $handler;
        return array_search($handler, self::$handlers[$event_name]);        
    }

    static function unsubscribe($event_name, $index)
    {
        if (array_key_exists($event_name, self::$handlers) and isset(self::$handlers[$event_name][$index])) 
        {
            unset(self::$handlers[$event_name][$index]);
            return true;
        }
        return false;        
    }

    static function callEvent($event_name) 
    {
        if (array_key_exists($event_name, self::$handlers)) 
        {
            foreach (self::$handlers[$event_name] as $handler) 
            {  
                $method = $handler['method'];
                $obj = $handler['obj'];
                $args = $handler['args'];               
                $obj->$method($args);                 
            }
        }
    }
}
