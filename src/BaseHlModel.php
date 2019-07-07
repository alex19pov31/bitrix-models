<?php

namespace Alex19pov31\BitrixModel;

use Alex19pov31\BitrixModel\Exceptions\ExceptionAddElementHlBlock;
use Alex19pov31\BitrixModel\Exceptions\ExceptionUpdateElementHlBlock;
use Bitrix\Main\ORM\Data\AddResult;
use Bitrix\Main\EventManager;
use Bitrix\Main\Loader;
use Bitrix\Main\ORM\EventResult;
use Alex19pov31\BitrixModel\Traits\HlEventTrait;

abstract class BaseHlModel extends BaseModel
{
    use HlEventTrait;

    protected $props = [];
    protected static $entity;
    protected static $hlblock;
    abstract protected static function getTableName(): string;
    abstract protected static function getCacheMinutes(): int;

    /**
     * @return DataManager|null
     */
    private static function getEntity()
    {
        if (!is_null(static::$entity[static::getTableName()])) {
            return static::$entity[static::getTableName()];
        }

        $hlBlock = static::getHlBlock();
        if (!$hlBlock) {
            return null;
        }

        return static::$entity[static::getTableName()] = HighloadBlockTable::compileEntity($hlBlock)->getDataClass();
    }

    /**
     * @return array|null
     */
    protected static function getHlBlock()
    {
        if (!is_null(static::$hlblock[static::getTableName()])) {
            return static::$hlblock[static::getTableName()];
        }

        Loader::includeModule('highloadblock');
        $hlBlock = HighloadBlockTable::getList([
            'filter' => [
                '=TABLE_NAME' => static::getTableName(),
            ],
            'limit' => 1,
        ])->fetch();
        
        return static::$hlblock[static::getTableName()] = $hlBlock;
    }

    public static function getList(array $params)
    {
        $params['cache'] = [
            'ttl' => static::getCacheMinutes() * 60,
        ];
        return static::getEntity()::getList($params);
    }

    public static function getListCollection(array $params = [], $keyBy = null): BaseModelCollection
    {
        if ($keyBy === null) {
            $list = static::getList($params)->fetchAll();
            return new BaseModelCollection($list, static::class);
        }

        $list = [];
        $res = static::getList($params);
        while ($item = $res->fetch()) {
            if (!$keyBy || !isset($item[$keyBy])) {
                $list[] = $item;
                continue;
            }
            $key = $item[$keyBy];
            $list[$key] = $item;
        }

        return new BaseModelCollection($list, static::class);
    }

    public static function add(array $data): BaseModel
    {
        $result = static::getEntity()::add($data);
        if (!$result->isSuccess()) {
            throw new ExceptionAddElementHlBlock(
                static::getTableName(),
                implode(' ', $result->getErrorMessages())
            );
        }
        $data['ID'] = (int) $result;
        return new static($data);
    }

    public static function update(int $id, array $data): BaseModel
    {
        /**
         * @var AddResult $result
         */
        $result = static::getEntity()::update($id, $data);
        if (!$result->isSuccess()) {
            throw new ExceptionUpdateElementHlBlock(
                static::getTableName(),
                implode(' ', $result->getErrorMessages())
            );
        }

        $data['ID'] = $result->getId();
        return new static($data);
    }

    public static function delete(int $id): bool
    {
        $result = static::getEntity()::delete($id);
        return $result->isSuccess();
    }

    public function getId(): int
    {
        return (int) $this['ID'];
    }

    /**
     * Удалить все записи
     *
     * @return void
     */
    public static function deleteAll()
    {
        sql("truncate " . static::getTableName());
    }
}
