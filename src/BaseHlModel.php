<?php
namespace Alex19pov31\BitrixModel;

use Alex19pov31\BitrixModel\Exceptions\ExceptionAddElementHlBlock;
use Alex19pov31\BitrixModel\Exceptions\ExceptionUpdateElementHlBlock;
use Bitrix\Main\ORM\Data\AddResult;
use Bitrix\Main\EventManager;
use Bitrix\Main\Loader;
use Bitrix\Main\ORM\EventResult;

abstract class BaseHlModel extends BaseModel
{
    protected $props = [];
    protected static $entity;
    abstract protected static function getTableName(): string;
    abstract protected static function getCacheMinutes(): int;

    public static function listenEvents()
    {
        if (!static::$em) {
            static::$em = EventManager::getInstance();
        }

        static::$em->addEventHandler('main', 'OnBeforeProlog', function () {
            Loader::includeModule('hightloadblock');
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

    /**
     * @return DataManager|null
     */
    private static function getEntity()
    {
        if (!is_null(static::$entity[static::getTableName()])) {
            return static::$entity[static::getTableName()];
        }

        Loader::includeModule('hightloadblock');
        $hlBlock = HighloadBlockTable::getList([
            'filter' => [
                'TABLE_NAME' => static::getTableName(),
            ],
            'limit' => 1,
        ])->fetch();
        if (!$hlBlock) {
            return null;
        }

        return static::$entity[static::getTableName()] = HighloadBlockTable::compileEntity($hlBlock)->getDataClass();
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
        $data['ID'] = (int)$result;
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
        return (int)$this['ID'];
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
