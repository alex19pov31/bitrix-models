<?php

namespace Alex19pov31\BitrixModel\InternalModels;

use Alex19pov31\BitrixModel\TableModel;

class IblockAsdMoldel extends TableModel
{
    const TTL = 180;

    protected static function getCacheMinutes(): int
    {
        return static::TTL;
    }

    protected static function getTableName(): string
    {
        return 'b_uts_asd_iblock';
    }
    
    public function getIblockId(): int
    {
        return (int)$this['VALUE_ID'];
    }
}