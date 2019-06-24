<?php
namespace Alex19pov31\BitrixModel;

use Alex19pov31\BitrixORMHelper\DataManager;
use Bitrix\Main\ORM\Data\DataManager as BitrixDataManager;

abstract class TableModel extends BaseModel
{
    static $entityList;

    abstract protected static function getTableName(): string;
    abstract protected static function getCacheMinutes(): int;

    private static function getEntity(): BitrixDataManager
    {
        $tableName = static::getTableName();
        if (static::$entityList[$tableName]) {
            return static::$entityList[$tableName];
        }

        return static::$entityList[$tableName] = DataManager::init(static::getTableName());
    }

    public static function getListCollection(array $params = [], $keyBy = null): BaseModelCollection
    {
        $list = [];
        $res = static::getEntity()::getList($params);
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

    /**
     * Добавление элемента
     *
     * @param array $data
     * @return BaseModel
     */
    public static function add(array $data): BaseModel
    {
        $result = static::getEntity()::add([
            'fields' => $data,
        ]);

        if (!$result->isSuccess()) {
            throw new \Exception(implode(' ', $result->getErrorMessages()));
        }
        $data['ID'] = (int)$result;
        return new static($data);
    }

    /**
     * Обновление элемента
     *
     * @param integer $id
     * @param array $data
     * @return BaseModel
     */
    public static function update(int $id, array $data): BaseModel
    {
        $result = static::getEntity()::update($id, $data);
        if (!$result->isSuccess()) {
            throw new \Exception(implode(' ', $result->getErrorMessages()));
        }

        $data['ID'] = $result->getId();
        return new static($data);
    }

    /**
     * Удаление элемента по идентификатору
     *
     * @param integer $id
     * @return boolean
     */
    public static function delete(int $id): bool
    {
        $result = static::getEntity()::delete($id);
        return $result->isSuccess();
    }
}
