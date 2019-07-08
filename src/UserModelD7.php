<?php

namespace Alex19pov31\BitrixModel;

use Bitrix\Main\ORM\Data\DataManager;
use Alex19pov31\BitrixModel\BaseModelCollection;
use Bitrix\Main\UserTable;
use Alex19pov31\BitrixModel\Traits\UserTrait;
use Alex19pov31\BitrixModel\Traits\UserEventTrait;

class UserModelD7 extends BaseDataManagerModel
{
    use UserTrait;
    use UserEventTrait;

    protected static $ttl = 180;
    private static $entity;

    protected static function getCacheMinutes(): int
    {
        return (int) static::$ttl;
    }

    /**
     * @return DataManager
     */
    protected static function getEntity()
    {
        if (static::$entity) {
            return static::$entity;
        }

        return static::$entity = new UserTable;
    }

    public static function getListCollection(array $params = [], $keyBy = null): BaseModelCollection
    {
        if (empty($params['select'])) {
            $params['select'] = ['*', 'UF_*'];
        }

        return parent::getListCollection($params, $keyBy);
    }
}
