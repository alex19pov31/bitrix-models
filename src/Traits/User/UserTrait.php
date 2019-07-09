<?php

namespace Alex19pov31\BitrixModel\Traits\User;

trait UserTrait
{
    /**
     * @param integer $id
     * @param array $select
     * @return BaseModel|null
     */
    abstract public static function getById(int $id, array $select = []);

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

        $id = (int) $USER->GetID();
        return static::getById($id, $select);
    }


    /**
     * Имя пользователя
     *
     * @return string
     */
    public function getName(): string
    {
        return (string) $this['NAME'];
    }

    /**
     * Фамилия пользователя
     *
     * @return string
     */
    public function getLastName(): string
    {
        return (string) $this['LAST_NAME'];
    }

    /**
     * Отчество пользователя
     *
     * @return string
     */
    public function getSecondName(): string
    {
        return (string) $this['SECOND_NAME'];
    }

    /**
     * ФИО пользователя
     *
     * @return string
     */
    public function getFullName(): string
    {
        return implode(' ', [
            (string) $this['LAST_NAME'],
            (string) $this['NAME'],
            (string) $this['SECOND_NAME'],
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
            (string) $this['NAME'],
            (string) $this['LAST_NAME'],
        ]);
    }
    /**
     * Email
     *
     * @return string
     */
    public function getEmail(): string
    {
        return (string) $this['EMAIL'];
    }

    /**
     * Логин
     *
     * @return string
     */
    public function getLogin(): string
    {
        return (string) $this['LOGIN'];
    }

    public function getWorkPosition(): string
    {
        return (string) $this['WORK_POSITION'];
    }

    public function getPersonalProfession(): string
    {
        return (string) $this['PERSONAL_PROFESSION'];
    }

    public function getPersonalPhoto(int $width = 0, int $height = 0): string
    {
        $fileId = (int) $this['PERSONAL_PHOTO'];
        if (!$fileId) {
            return '';
        }
        return $this->getPictureSrc($fileId, $width, $height);
    }
}
