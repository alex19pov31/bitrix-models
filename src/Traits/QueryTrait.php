<?php


namespace Alex19pov31\BitrixModel\Traits;


use Alex19pov31\BitrixModel\Query;
use Bitrix\Main\ORM\Entity;

trait QueryTrait
{
    abstract protected static function getEntity(): Entity;

    public static function query(): Query
    {
        return new Query(static::getEntity(), static::class);
    }
}