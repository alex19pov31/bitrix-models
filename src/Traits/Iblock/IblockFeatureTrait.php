<?php

namespace Alex19pov31\BitrixModel\Traits\Iblock;

use Alex19pov31\BitrixModel\BaseModelCollection;
use Alex19pov31\BitrixModel\InternalModels\IblockFeatureModel;

trait IblockFeatureTrait
{
    protected static $propsEnabledFeature;
    protected static $propsDisabledFeature;

    abstract public static function getIblockId(): int;
    abstract protected static function getCacheMinutes(): int;

    /**
     * @return BaseModelCollection
     */
    public static function getFeatureList(): BaseModelCollection
    {
        if (static::$propsEnabledFeature instanceof BaseModelCollection) {
            return static::$propsEnabledFeature;
        }

        return static::$propsEnabledFeature = IblockFeatureModel::getListCollection([
            'filter' => [
                '=PROPERTY.IBLOCK_ID' => static::getIblockId(),
            ],
            'select' => [
                'FEATURE_ID',
                'CODE' => 'PROPERTY.CODE',
                'IS_ENABLED',
            ],
        ]);
    }

    /**
     * @param string $fetureId
     * @return BaseModelCollection
     */
    public static function getFetureProps(string $fetureId): BaseModelCollection
    {
        return static::getFeatureList()->where('FEATURE_ID', $fetureId);
    }

    /**
     * Список свойств для показа на детальной странице
     *
     * @param boolean $selectQuery
     * @return array
     */
    public static function getPropsShowOnDetailPage(bool $selectQuery = true): array
    {
        /**
         * @var BaseModelCollection $list
         */
        $list = static::getFetureProps('DETAIL_PAGE_SHOW');
        return $selectQuery ? static::getSelectFields($list) : $list->column('CODE');
    }

    /**
     * Список свойств для показа на странице списка
     *
     * @param boolean $selectQuery
     * @return array
     */
    public static function getPropsShowOnListPage(bool $selectQuery = true): array
    {
        /**
         * @var BaseModelCollection $list
         */
        $list = static::getFetureProps('LIST_PAGE_SHOW');
        return $selectQuery ? static::getSelectFields($list) : $list->column('CODE');
    }

    /**
     * Список свойст для показа в корзине
     *
     * @param boolean $selectQuery
     * @return array
     */
    public static function getPropsShowInBasket(bool $selectQuery = true): array
    {
        /**
         * @var BaseModelCollection $list
         */
        $list = static::getFetureProps('IN_BASKET');
        return $selectQuery ? static::getSelectFields($list) : $list->column('CODE');
    }

    private static function getSelectFields(BaseModelCollection $list): array
    {
        return $list->addField('SELECT_FIELD', function (IblockFeatureModel $item) {
            return 'PROPERTY_' . $item['CODE'];
        })->column('SELECT_FIELD');
    }
}
