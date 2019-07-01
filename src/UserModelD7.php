<?php

namespace Alex19pov31\BitrixModel;

use Bitrix\Main\ORM\Data\DataManager;
use Alex19pov31\BitrixModel\Entity\UserTable;

class UserModelD7 extends BaseDataManagerModel
{
    const TTL = 180;
    private static $entity;

    protected static function getCacheMinutes(): int
    {
        return static::TTL;
    }

    /**
     * @return DataManager
     */
    protected static function getEntity()
    {
        return static::$entity = new UserTable;
    }
}
