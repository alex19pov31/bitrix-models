<?php

namespace Alex19pov31\BitrixModel;

use Alex19pov31\BitrixORMHelper\DataManager;
use Bitrix\Main\ORM\Data\DataManager as BitrixDataManager;

abstract class TableModel extends BaseDataManagerModel
{
    static $entityList;

    abstract protected static function getTableName(): string;
    abstract protected static function getCacheMinutes(): int;

    protected static function getEntity(): BitrixDataManager
    {
        $tableName = static::getTableName();
        if (static::$entityList[$tableName]) {
            return static::$entityList[$tableName];
        }

        return static::$entityList[$tableName] = DataManager::init(static::getTableName());
    }
}
