<?php

namespace Alex19pov31\BitrixModel\InternalModels;

use Alex19pov31\BitrixModel\BaseDataManagerModel;
use Bitrix\Iblock\PropertyTable;
use DateTime;
use Bitrix\Main\Loader;

class IblockPropertyModel extends BaseDataManagerModel
{
    const TTL = 180;

    protected static function getCacheMinutes(): int
    {
        return static::TTL;
    }

    /**
     * @return BitrixDataManager
     */
    protected static function getDataManager()
    {
        Loader::includeModule('iblock');
        return PropertyTable::class;
    }

    public function getCode(): string
    {
        return (string) $this['CODE'];
    }

    public function getName(): string
    {
        return (string) $this['NAME'];
    }

    public function getIblockId(): int
    {
        return (int) $this['IBLOCK_ID'];
    }

    public function isActive(): bool
    {
        return (bool) $this['ACTIVE'];
    }

    public function getSort(): int
    {
        return (int) $this['SORT'];
    }

    public function getDateUpdate(): DateTime
    {
        return new DateTime($this['TIMESTAMP_X']);
    }

    public function getDefaultValue(): string
    {
        return (string) $this['DEFAULT_VALUE'];
    }

    public function getPropertyType(): string
    {
        return (string) $this['PROPERTY_TYPE'];
    }

    public function isMultiple(): bool
    {
        return $this['MULTIPLE'] == 'Y';
    }

    public function isRequired(): bool
    {
        return $this['IS_REQUIRED'] == 'Y';
    }

    public function getXmlId(): string
    {
        return (string) $this['XML_ID'];
    }

    public function getVersion(): int
    {
        return (int) $this['VERSION'];
    }

    public function getUserType(): string
    {
        return (string) $this['USER_TYPE'];
    }

    public function getUserTypeSettings(): string
    {
        return (string) $this['USER_TYPE_SETTINGS'];
    }

    public function getHint(): string
    {
        return (string) $this['HINT'];
    }
}
