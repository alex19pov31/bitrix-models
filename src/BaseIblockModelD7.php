<?php
namespace Alex19pov31\BitrixModel;

use Alex19pov31\BitrixModel\Entity\IblockElementTable;
use Alex19pov31\BitrixModel\Traits\Iblock\IblockComponentTrait;
use Alex19pov31\BitrixModel\Traits\Iblock\IblockEventTrait;
use Alex19pov31\BitrixModel\Traits\Iblock\IblockFeatureTrait;
use Alex19pov31\BitrixModel\Traits\Iblock\IblockSeoTrait;
use Alex19pov31\BitrixModel\Traits\Iblock\IblockTrait;
use Bitrix\Main\ORM\Data\DataManager;

abstract class BaseIblockModelD7 extends BaseDataManagerModel
{
    use IblockTrait;
    use IblockSeoTrait;
    use IblockFeatureTrait;
    use IblockEventTrait;
    use IblockComponentTrait;

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
