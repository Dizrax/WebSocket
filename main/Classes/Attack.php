<?
class Attack extends Action
{
    public $target;
    function  __construct ($target) 
    {
        $this->target = $target;
    }
}