<?php

namespace Alex19pov31\BitrixModel;

use Alex19pov31\BitrixModel\BaseModelCollection;
use Alex19pov31\BitrixModel\Traits\User\UserEventTrait;
use Alex19pov31\BitrixModel\Traits\User\UserTrait;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\UserTable;

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
    protected static function getDataManager()
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

        return parent::getListCollection($params, $keyBy, $params);
    }
}
