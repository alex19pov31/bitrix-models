<?php

namespace Alex19pov31\BitrixModel;

use Alex19pov31\BitrixModel\Traits\QueryTrait;
use Bitrix\Main\ORM\Data\DataManager as BitrixDataManager;
use Alex19pov31\BitrixModel\Traits\SefUrlTrait;
use Bitrix\Main\ORM\Entity;

abstract class BaseDataManagerModel extends BaseModel
{
    use SefUrlTrait;
    use QueryTrait;

    abstract protected static function getCacheMinutes(): int;

    /**
     * @return BitrixDataManager
     */
    abstract protected static function getDataManager();

    protected function getPropertyCodeList(): array
    {
        $fields = appInstance()->getConnection()->getTableFields(static::getDataManager()->getTableName());
        return array_keys($fields);
    }

    protected static function getEntity(): Entity
    {
        return static::getDataManager()::getEntity();
    }

    public static function getCount(array $filter = []): int
    {
        return (int) static::getDataManager()::getCount($filter);
    }

    public static function getListCollection(array $params = [], $keyBy = null): BaseModelCollection
    {
        $list = [];
        $res = static::getDataManager()::getList($params);
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

    /**
     * Добавление элемента
     *
     * @param array $data
     * @return BaseModel
     */
    public static function add(array $data): BaseModel
    {
        $result = static::getDataManager()::add([
            'fields' => $data,
        ]);

        if (!$result->isSuccess()) {
            throw new \Exception(implode(' ', $result->getErrorMessages()));
        }
        $data['ID'] = (int) $result;
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
        $result = static::getDataManager()::update($id, $data);
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
        $result = static::getDataManager()::delete($id);
        return $result->isSuccess();
    }
}
