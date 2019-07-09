<?php

namespace Alex19pov31\BitrixModel\InternalModels;

use Alex19pov31\BitrixModel\TableModel;

class HlPropertyEnumModel extends TableModel
{
    const TTL = 180;

    protected static function getCacheMinutes(): int
    {
        return static::TTL;
    }

    protected static function getTableName(): string
    {
        return 'b_user_field_enum';
    }
    
    public function getUserFieldId(): string
    {
        return (string)$this['USER_FIELD_ID'];
    }
    
    public function getValue(): string
    {
        return (string)$this['VALUE'];
    }
    
    public function isDefault(): bool
    {
        return $this['DEF'] == 'Y';
    }
    
    public function getSort(): int
    {
        return (int)$this['SORT'];
    }
    
    public function getXmlId(): string
    {
        return (string)$this['XML_ID'];
    }
}