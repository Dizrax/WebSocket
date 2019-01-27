<?
class Id
{
    protected static $value = 0;

    static function get()
    {
        return $value++;
    }
}