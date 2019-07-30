<?php

namespace Alex19pov31\BitrixModel\InternalModels;

use Alex19pov31\BitrixModel\BaseDataManagerModel;
use Bitrix\Main\Loader;
use Bitrix\Highloadblock\HighloadBlockTable;

class HlModel extends BaseDataManagerModel
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
        Loader::includeModule('highloadblock');
        return HighloadBlockTable::class;
    }

    public function getName(): string
    {
        return (string)$this['NAME'];
    }
}