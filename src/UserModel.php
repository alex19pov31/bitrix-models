<?php
namespace Alex19pov31\BitrixModel;

use CIBlockSection;
use CIBlockResult;
use DateTime;
use Bitrix\Main\UserTable;

class UserModel extends BaseModel
{
    protected $props = [];

    public function __construct(array $data)
    {
        $this->props = $data;
    }

    /**
     * Текущий пользователь
     *
     * @param array $select
     * @return UserModel|null
     */
    public static function current(array $select = [])
    {
        /**
         * @var \CUser $USER
         */
        global $USER;
        if (!$USER->IsAuthorized()) {
            return null;
        }

        $id = (int)$USER->GetID();
        return static::getById($id, $select);
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
    public static function getListCollection(array $param = [], $keyBy = null)
    {
        $by = is_array($param['order']) ? $param['order'] : ['sort' => 'asc'];
        $order = 'desc';
        $filter = is_array($param['filter']) ? $param['filter'] : [];
        $arParams = [];
        if ((int)$param['limit'] > 0) {
            $arParams['NAV_PARAMS']['nPageSize'] = $param['limit'];
        }

        if (!empty($param['select'])) {
            $arParams['SELECT'] = $param['select'];
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
        $id = (int)$cUser->Add($data);
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
        $isSuccess = (bool)$cUser->Update($id, $data);
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
        return (bool)\CUser::Delete($id);
    }

    /**
     * Имя пользователя
     *
     * @return string
     */
    public function getName(): string
    {
        return (string)$this['NAME'];
    }

    /**
     * Фамилия пользователя
     *
     * @return string
     */
    public function getLastName(): string
    {
        return (string)$this['LAST_NAME'];
    }

    /**
     * Отчество пользователя
     *
     * @return string
     */
    public function getSecondName(): string
    {
        return (string)$this['SECOND_NAME'];
    }

    /**
     * ФИО пользователя
     *
     * @return string
     */
    public function getFullName(): string
    {
        return implode(' ', [
            (string)$this['LAST_NAME'],
            (string)$this['NAME'],
            (string)$this['SECOND_NAME'],
        ]);
    }

    /**
     * Имя и фамилия пользователя
     *
     * @return string
     */
    public function getShortName(): string
    {
        return implode(' ', [
            (string)$this['NAME'],
            (string)$this['LAST_NAME'],
        ]);
    }
    /**
     * Email
     *
     * @return string
     */
    public function getEmail(): string
    {
        return (string)$this['EMAIL'];
    }

    /**
     * Логин
     *
     * @return string
     */
    public function getLogin(): string
    {
        return (string)$this['LOGIN'];
    }

    public function getWorkPosition(): string
    {
        return (string)$this['WORK_POSITION'];
    }

    public function getPersonalProfession(): string
    {
        return (string)$this['PERSONAL_PROFESSION'];
    }

    public function getPersonalPhoto(int $width = 0, int $height = 0): string
    {
        $fileId = (int)$this['PERSONAL_PHOTO'];
        if (!$fileId) {
            return '';
        }
        return $this->getPictureSrc($fileId, $width, $height);
    }
}