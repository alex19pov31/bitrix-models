<?php
namespace Alex19pov31\BitrixModel;

use Alex19pov31\BitrixModel\Entity\IblockElementTable;
use Alex19pov31\BitrixModel\Entity\IblockNewElementTable;
use Alex19pov31\BitrixModel\InternalModels\IblockPropertyModel;
use Alex19pov31\BitrixModel\Traits\Iblock\IblockComponentTrait;
use Alex19pov31\BitrixModel\Traits\Iblock\IblockEventTrait;
use Alex19pov31\BitrixModel\Traits\Iblock\IblockFeatureTrait;
use Alex19pov31\BitrixModel\Traits\Iblock\IblockSeoTrait;
use Alex19pov31\BitrixModel\Traits\Iblock\IblockTrait;
use Alex19pov31\BitrixModel\Traits\SefUrlTrait;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Entity;

abstract class BaseIblockModelD7 extends BaseDataManagerModel
{
    use IblockTrait;
    use IblockSeoTrait;
    use IblockFeatureTrait;
    use IblockEventTrait;
    use IblockComponentTrait;
    use SefUrlTrait;

    abstract protected static function getIblockId(): int;

    public static function query(): Query
    {
        return new Query(
            static::getEntity(),
            static::class,
            [
                'IBLOCK_ID' => static::getIblockId(),
            ]
        );
    }

    protected function getPropertyCodeList(): array
    {
        $fields = appInstance()->getConnection()->getTableFields('b_iblock_element');
        $propertyList = array_keys($fields);
        $list = static::getPropertiesInfo([
            'CODE',
        ])->addField('PROPERTY', function (IblockPropertyModel $prop) {
            return 'PROPERTY_' . $prop->getCode();
        })->column('PROPERTY');

        return array_merge($propertyList, $list);
    }

    /**
     * @return DataManager
     */
    protected static function getDataManager()
    {
        if (static::getIblock()->getVersion() == 2) {
            return IblockNewElementTable::class;
        }

        return IblockElementTable::class;
    }

    protected static function getEntity(): Entity
    {
        return static::getDataManager()::getExtEntity(static::getIblockId(), []);
    }

    public static function getListCollection(array $params = [], $keyBy = null): BaseModelCollection
    {
        $params['filter']['IBLOCK_ID'] = static::getIblockId();
        return parent::getListCollection($params);
    }
}
