<?php

namespace Alex19pov31\BitrixModel\InternalModels;

use Alex19pov31\BitrixModel\BaseDataManagerModel;
use Bitrix\Iblock\PropertyFeatureTable;
use Bitrix\Main\Loader;

class IblockFeatureModel extends BaseDataManagerModel
{
    const TTL = 180;

    protected static function getCacheMinutes(): int
    {
        return static::TTL;
    }
    
    /**
     * @return BitrixDataManager
     */
    protected static function getEntity()
    {
        Loader::includeModule('iblock');
        return PropertyFeatureTable::class;
    }

    public function getPropertyId(): int
    {
        return (int)$this['MODULE_ID'];
    }

    public function getModuleId(): string
    {
        return (string)$this['PROPERTY_ID'];
    }

    public function getFeatureId(): string
    {
        return (string)$this['FEATURE_ID'];
    }

    public function isEnabled(): bool
    {
        return $this['IS_ENABLED'] === 'Y';
    }
}