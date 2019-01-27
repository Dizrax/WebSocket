<?
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