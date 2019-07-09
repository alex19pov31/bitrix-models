<?php

namespace Alex19pov31\BitrixModel\Traits\Hl;

use Bitrix\Main\EventManager;
use Bitrix\Main\Loader;

trait HlEventTrait
{
    abstract protected static function getHlBlock();

    public static function listenEvents()
    {
        if (!static::$em) {
            static::$em = EventManager::getInstance();
        }

        static::$em->addEventHandler('main', 'OnBeforeProlog', function () {
            Loader::includeModule('highloadblock');
            $hlBlock = static::getHlBlock();
            static::$em->addEventHandler('', $hlBlock['NAME'] . 'OnBeforeAdd', [
                static::class,
                'onBeforeCreate',
            ]);
            static::$em->addEventHandler('', $hlBlock['NAME'] . 'OnAfterAdd', [
                static::class,
                'onAfterCreate',
            ]);
            static::$em->addEventHandler('', $hlBlock['NAME'] . 'OnBeforeUpdate', [
                static::class,
                'onBeforeUpdate',
            ]);
            static::$em->addEventHandler('', $hlBlock['NAME'] . 'OnAfterUpdate', [
                static::class,
                'onAfterUpdate',
            ]);
            static::$em->addEventHandler('', $hlBlock['NAME'] . 'OnAfterDelete', [
                static::class,
                'onAfterDelete',
            ]);
        });
    }

    final public static function onBeforeCreate(Event $event)
    {
        $params = $event->getParameters();
        static::beforeCreate($params);
        $result = new EventResult;
        $result->modifyFields($params['fields']);
        return $result;
    }

    final public static function onAfterCreate(Event $event)
    {
        $params = $event->getParameters();
        static::afterCreate($params);
        $result = new EventResult;
        $result->modifyFields($params['fields']);
        return $result;
    }

    final public static function onBeforeUpdate(Event $event)
    {
        $params = $event->getParameters();
        static::beforeUpdate($params);
        $result = new EventResult;
        $result->modifyFields($params['fields']);
        return $result;
    }

    final public static function onAfterUpdate(Event $event)
    {
        $params = $event->getParameters();
        static::afterUpdate($params);
        $event->setParameters($params);
    }

    final public static function onAfterDelete(Event $event)
    {
        $params = $event->getParameters();
        static::afterDelete($params);
        $event->setParameters($params);
    }

    protected static function beforeCreate(&$arFields)
    { }

    protected static function afterCreate(&$arFields)
    { }

    protected static function beforeUpdate(&$arParams)
    { }

    protected static function afterUpdate(&$arFields)
    { }

    protected static function afterDelete($arFields)
    { }

    public function __construct(array $data)
    {
        $this->props = $data;
    }
}