<?php

namespace Alex19pov31\BitrixModel;

use Alex19pov31\BitrixModel\Exceptions\ExceptionAddElementHlBlock;
use Alex19pov31\BitrixModel\Exceptions\ExceptionUpdateElementHlBlock;
use Alex19pov31\BitrixModel\Traits\Hl\HlComponentTrait;
use Alex19pov31\BitrixModel\Traits\Hl\HlEventTrait;
use Alex19pov31\BitrixModel\Traits\Hl\HlTrait;
use Alex19pov31\BitrixModel\Traits\QueryTrait;
use Alex19pov31\BitrixModel\Traits\SefUrlTrait;
use Bitrix\Main\ORM\Data\AddResult;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Entity;

abstract class BaseHlModel extends BaseModel
{
    use HlTrait;
    use HlEventTrait;
    use HlComponentTrait;
    use QueryTrait;
    use SefUrlTrait;

    protected $props = [];
    protected static $entity;
    protected static $hlblock;
    abstract protected static function getTableName(): string;
    abstract protected static function getCacheMinutes(): int;

    /**
     * @return DataManager|null
     */
    private static function getDataManager()
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

    protected static function getEntity(): Entity
    {
        static::getDataManager()::getEntity();
    }

    protected function getPropertyCodeList(): array
    {
        $fields = appInstance()->getConnection()->getTableFields(static::getDataManager()->getTableName());
        return array_keys($fields);
    }

    public static function getList(array $params)
    {
        $params['cache'] = [
            'ttl' => static::getCacheMinutes() * 60,
        ];
        return static::getDataManager()::getList($params);
    }

    public static function getListCollection(array $params = [], $keyBy = null): BaseModelCollection
    {
        if ($keyBy === null) {
            $list = static::getList($params)->fetchAll();
            return new BaseModelCollection($list, static::class, $params);
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

        return new BaseModelCollection($list, static::class, $params);
    }

    public static function add(array $data): BaseModel
    {
        $result = static::getDataManager()::add($data);
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
        $result = static::getDataManager()::update($id, $data);
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
        $result = static::getDataManager()::delete($id);
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
