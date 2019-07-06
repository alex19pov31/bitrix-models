<?php
namespace Alex19pov31\BitrixModel;

use Alex19pov31\BitrixHelper\Iblock\IblockElementTable;
use Bitrix\Main\ORM\Data\DataManager;
use Alex19pov31\BitrixModel\Traits\IblockTrait;
use Alex19pov31\BitrixModel\Traits\IblockSeoTrait;
use Alex19pov31\BitrixModel\Traits\IblockFeatureTrait;
use Alex19pov31\BitrixModel\Traits\IblockEventTrait;

abstract class BaseIblockModelD7 extends BaseDataManagerModel
{
    use IblockTrait;
    use IblockSeoTrait;
    use IblockFeatureTrait;
    use IblockEventTrait;

    abstract protected static function getIblockId(): int;
    abstract protected static function getCacheMinutes(): int;

    /**
     * @return DataManager
     */
    protected static function getEntity()
    {
        return IblockElementTable::class;
    }

    public static function getListCollection(array $params = [], $keyBy = null): BaseModelCollection
    {
        $params['filter']['IBLOCK_ID'] = static::getIblockId();
        return parent::getListCollection($params);
    }
}