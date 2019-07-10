<?php

namespace Alex19pov31\BitrixModel\Traits\Hl;

use Alex19pov31\BitrixModel\InternalModels\HlModel;
use Alex19pov31\BitrixModel\BaseModelCollection;
use Alex19pov31\BitrixModel\InternalModels\UserFieldModel;

trait HlTrait
{
    protected static $properties;
    protected static $hlblock;

    abstract protected static function getTableName(): string;

    /**
     * @return HlModel|null
     */
    protected static function getHlBlock()
    {
        if (!is_null(static::$hlblock[static::getTableName()])) {
            return static::$hlblock[static::getTableName()];
        }

        /**
         * @var HlModel $hlBlock
         */
        $hlBlock = HlModel::getListCollection([
            'filter' => [
                '=TABLE_NAME' => static::getTableName(),
            ],
        ])->current();
        
        return static::$hlblock[static::getTableName()] = $hlBlock;
    }

    public function getPropertiesInfo(): BaseModelCollection
    {
        
        $tableName = static::getTableName();
        if (empty($select) && static::$properties[$tableName] instanceof BaseModelCollection) {
            return static::$properties[$tableName];
        }

        /**
         * @var HlModel $hlBlock
         */
        $hlBlock = static::getHlBlock();
        if ($hlBlock) {
            return new BaseModelCollection([], HlModel::class);
        }

        return static::$properties[$tableName] = UserFieldModel::getListCollection([
            'filter' => [
                '=ENTITY_ID' => 'HLBLOCK_'.$hlBlock->getId(),
            ],
        ]);
    }
}