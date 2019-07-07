<?php

namespace Alex19pov31\BitrixModel;

use CUser;
use Alex19pov31\BitrixModel\Traits\UserTrait;
use Alex19pov31\BitrixModel\Traits\UserEventTrait;

class UserModel extends BaseModel
{
    use UserTrait;
    use UserEventTrait;

    protected $props = [];
    protected static $ttl = 180;

    protected static function getCacheMinutes(): int
    {
        return (int) static::$ttl;
    }

    public function __construct(array $data)
    {
        $this->props = $data;
    }

    /**
     * Список пользователей по идентификатору группы
     *
     * @param integer $groupId
     * @return UserCollection
     */
    public static function getUserListByGroupId(int $groupId): UserCollection
    {
        return static::getList([
            'filter' => [
                'GROUPS_ID' => $groupId,
            ],
        ]);
    }

    /**
     * Список элементов
     *
     * @param array $params
     * @return BaseModelCollection
     */
    public static function getListCollection(array $param = [], $keyBy = null): BaseModelCollection
    {
        $by = is_array($param['order']) ? $param['order'] : ['sort' => 'asc'];
        $order = 'desc';
        $filter = is_array($param['filter']) ? $param['filter'] : [];
        $arParams = [];
        if ((int) $param['limit'] > 0) {
            $arParams['NAV_PARAMS']['nPageSize'] = $param['limit'];
        }

        if (!empty($param['select'])) {
            foreach ($param['select'] as $field) {
                if (strpos($field, 'UF_') === 0) {
                    $arParams['SELECT'][] = $field;
                    continue;
                }
                $arParams['FIELDS'][] = $field;
            }
        }

        $sort = $param['sort'];

        $key = md5(
            json_encode(
                [
                    'by' => $by,
                    'order' => $order,
                    'filter' => $filter,
                    'arParams' => $arParams,
                    'sort' => $sort,
                ]
            )
        );

        return cache(static::getCacheMinutes(), $key, '/user_model', 'cache', function () use ($by, $order, $filter, $arParams, $sort, $keyBy) {
            initTagCache([
                'user_model',
            ], '/user_model');

            $result = [];
            $res = CUser::GetList($by, $order, $filter, $arParams);
            while ($user = $res->Fetch()) {
                $key = $user[$keyBy];
                if (!$keyBy || !$key) {
                    $result[] = $user;
                    continue;
                }

                $result[$key] = $user;
            }
            if ($sort) {
                $tmp = [];
                foreach ($sort as $id) {
                    $tmp[$id] = $result[$id];
                }
                $result = $tmp;
            }

            return new BaseModelCollection($result, static::class);
        });
    }

    /**
     * Добавление пользователя
     *
     * @param array $data
     * @return BaseModel
     */
    public static function add(array $data): BaseModel
    {
        $cUser = new \CUser;
        $id = (int) $cUser->Add($data);
        if (!$id) {
            throw new \Exception($cUser->LAST_ERROR);
        }

        $data['ID'] = $id;
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

        $cUser = new \CUser;
        $isSuccess = (bool) $cUser->Update($id, $data);
        if (!$isSuccess) {
            throw new \Exception($cUser->LAST_ERROR);
        }

        $data['ID'] = $id;
        return new static($data);
    }

    /**
     * Удаление пользователя по идентификатору
     *
     * @param integer $id
     * @return boolean
     */
    public static function delete(int $id): bool
    {
        return (bool) \CUser::Delete($id);
    }
}
