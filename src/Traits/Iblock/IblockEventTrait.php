<?php

namespace Alex19pov31\BitrixModel\Traits\Iblock;

use Bitrix\Main\EventManager;
use Bitrix\Main\Loader;

trait IblockEventTrait
{
    abstract protected static function getIblockId(): int;

    public static function listenEvents()
    {
        if (!static::$em) {
            static::$em = EventManager::getInstance();
        }

        Loader::includeModule('iblock');
        static::$em->addEventHandler('iblock', 'OnBeforeIBlockElementAdd', [
            static::class,
            'onBeforeCreate',
        ]);
        static::$em->addEventHandler('iblock', 'OnAfterIBlockElementAdd', [
            static::class,
            'onAfterCreate',
        ]);
        static::$em->addEventHandler('iblock', 'OnBeforeIBlockElementUpdate', [
            static::class,
            'onBeforeUpdate',
        ]);
        static::$em->addEventHandler('iblock', 'OnAfterIBlockElementUpdate', [
            static::class,
            'onAfterUpdate',
        ]);
        static::$em->addEventHandler('iblock', 'OnAfterIBlockElementDelete', [
            static::class,
            'onAfterDelete',
        ]);
    }

    final public static function onBeforeCreate(&$arFields)
    {
        if (static::getIblockId() == $arFields['IBLOCK_ID']) {
            static::beforeCreate($arFields);
        }
    }

    final public static function onAfterCreate(&$arFields)
    {
        if (static::getIblockId() == $arFields['IBLOCK_ID']) {
            static::afterCreate($arFields);
        }
    }

    final public static function onBeforeUpdate(&$arParams)
    {
        if (static::getIblockId() == $arParams['IBLOCK_ID']) {
            static::beforeUpdate($arParams);
        }
    }

    final public static function onAfterUpdate(&$arFields)
    {
        if (static::getIblockId() == $arFields['IBLOCK_ID']) {
            static::afterUpdate($arFields);
        }
    }

    final public static function onAfterDelete(&$arFields)
    {
        if (static::getIblockId() == $arFields['IBLOCK_ID']) {
            static::afterDelete($arFields);
        }
    }

    protected static function beforeCreate(&$arFields)
    { }

    protected static function afterCreate(&$arFields)
    { }

    protected static function beforeUpdate(&$arParams)
    { }

    protected static function afterUpdate(&$arFields)
    { }

    protected static function afterDelete(&$arFields)
    { }
}