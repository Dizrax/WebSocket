<?
class ServerHexagon extends Hexagon
{
    public $topLeft;
    public $top;
    public $topRight;
    public $downLeft;
    public $down;
    public $downRight;
    
    public $position;
    public $position3D;

    function  __construct ($type, Point $position) 
    {
        parent::__construct($type);
        $this->position = $position;
    }
}