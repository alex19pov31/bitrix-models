<?php
namespace Alex19pov31\BitrixModel;

use CIBlockElement;
use CIBlockResult;
use Alex19pov31\BitrixModel\Exceptions\ExceptionAddElementIblock;
use Alex19pov31\BitrixModel\Exceptions\ExceptionUpdateElementIblock;
use Bitrix\Main\Loader;
use Bitrix\Main\EventManager;
use Alex19pov31\BitrixModel\Traits\IblockTrait;
use Alex19pov31\BitrixModel\Traits\IblockSeoTrait;
use Alex19pov31\BitrixModel\Traits\IblockFeatureTrait;

abstract class BaseIblockModel extends BaseModel
{
    use IblockTrait;
    use IblockSeoTrait;
    use IblockFeatureTrait;

    abstract protected static function getIblockId(): int;
    abstract protected static function getCacheMinutes(): int;


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

    public static function getList(array $params = []): CIBlockResult
    {
        Loader::includeModule('iblock');
        $order = (array)$params['order'];
        $select = (array)$params['select'];

        $nav = false;
        if ((int)$params['limit'] > 0) {
            $nav['nPageSize'] = $params['limit'];
        }

        if ((int)$params['offset'] > 0) {
            $nav['iNumPage'] = (int)$params['offset'] ? ceil($params['offset'] / $params['limit']) : 1;
        }

        $filter = (array)$params['filter'];
        $filter['IBLOCK_ID'] = static::getIblockId();

        return CIBlockElement::GetList($order, $filter, false, $nav, $select);
    }

    public static function getListCollection(array $params = [], $keyBy = null): BaseModelCollection
    {
        $key = static::class . '_' . md5(json_encode($params));
        $list = cache(
            static::getCacheMinutes(),
            $key,
            '/cache_model',
            'cache',
            function () use ($params) {
                initTagCache([
                    'iblock_id_' . static::getIblockId(),
                ]);

                $data = [];
                $result = static::getList($params);
                while ($item = $result->Fetch()) {
                    $data[] = $item;
                }

                return $data;
            }
        );

        if ($keyBy === null) {
            return new BaseModelCollection($list, static::class);
        }

        $newList = [];
        foreach ($list as $item) {
            if (!isset($item[$keyBy])) {
                $newList[] = $item;
                continue;
            }
            $key = $item[$keyBy];
            $newList[$key] = $item;
        }

        return new BaseModelCollection($newList, static::class);
    }

    private static function prepareDataIblockElement(array $data): array
    {
        Loader::includeModule('iblock');

        $fields = [];
        $props = [];
        $iblock = static::getIblockId();
        foreach ($data as $name => $value) {
            if (strpos($name, 'PROPERTY_') !== false) {
                $propertyName = str_replace(['PROPERTY_', '_VALUE'], '', $name);
                $props[$propertyName] = $value;
                continue;
            }

            $fields[$name] = $value;
        }

        $fields['IBLOCK_ID'] = $iblock;
        $fields['PROPERTY_VALUES'] = $props;

        return $fields;
    }

    /**
     * Добавление элемента
     *
     * @param array $data
     * @return BaseModel
     */
    public static function add(array $data): BaseModel
    {
        Loader::includeModule('iblock');

        $fields = static::prepareDataIblockElement($data);
        $el = new CIBlockElement;
        $data['ID'] = $el->Add($fields);

        if (!empty($el->LAST_ERROR)) {
            throw new ExceptionAddElementIblock((int)$fields['IBLOCK_ID'], $el->LAST_ERROR);
        }

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
        Loader::includeModule('iblock');

        $fields = static::prepareDataIblockElement($data);
        $el = new CIBlockElement;
        $el->Update($id, $fields);

        if (!empty($el->LAST_ERROR)) {
            throw new ExceptionUpdateElementIblock((int)$fields['IBLOCK_ID'], $el->LAST_ERROR);
        }

        return new static([]);
    }

    /**
     * Удаление элемента по идентификатору
     *
     * @param integer $id
     * @return boolean
     */
    public static function delete(int $id): bool
    {
        Loader::includeModule('iblock');
        return (bool)CIBlockElement::Delete($id);
    }

    public static function deactive(array $filter)
    {
        $list = static::getListCollection([
            'filter' => $filter,
        ]);

        /**
         * @var BaseIblockModel $sportUser
         */
        foreach ($list as $sportUser) {
            $sportUser['ACTIVE'] = 'N';
            $sportUser->save();
        }
    }

    public static function deactiveAll()
    {
        static::deactive([]);
    }
}
