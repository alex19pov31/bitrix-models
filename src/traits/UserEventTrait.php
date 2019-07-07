<?php

namespace Alex19pov31\BitrixModel\Traits;

use Bitrix\Main\EventManager;

trait UserEventTrait
{
    public static function listenEvents()
    {
        if (!static::$em) {
            static::$em = EventManager::getInstance();
        }

        static::$em->addEventHandler('main', 'OnBeforeUserAdd', [
            static::class,
            'onBeforeCreate',
        ]);
        static::$em->addEventHandler('iblock', 'OnAfterUserAdd', [
            static::class,
            'onAfterCreate',
        ]);
        static::$em->addEventHandler('iblock', 'OnBeforeUserUpdate', [
            static::class,
            'onBeforeUpdate',
        ]);
        static::$em->addEventHandler('iblock', 'OnAfterUserUpdate', [
            static::class,
            'onAfterUpdate',
        ]);
        static::$em->addEventHandler('iblock', 'OnUserDelete', [
            static::class,
            'onDelete',
        ]);
    }

    final public static function onBeforeCreate(&$arFields)
    {
        static::beforeCreate($arFields);
    }

    final public static function onAfterCreate(&$arFields)
    {
        static::afterCreate($arFields);
    }

    final public static function onBeforeUpdate(&$arParams)
    {
        static::beforeUpdate($arParams);
    }

    final public static function onAfterUpdate(&$arFields)
    {
        static::afterUpdate($arFields);
    }

    final public static function onDelete(&$arFields)
    {
        static::afterDelete($arFields);
    }

    protected static function beforeCreate(&$arFields)
    {}

    protected static function afterCreate(&$arFields)
    {}

    protected static function beforeUpdate(&$arParams)
    {}

    protected static function afterUpdate(&$arFields)
    {}

    protected static function afterDelete(&$arFields)
    {}
}
