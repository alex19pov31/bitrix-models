<?php
namespace Alex19pov31\BitrixModel;

use Alex19pov31\BitrixModel\Exceptions\ExceptionAddElementIblock;
use Alex19pov31\BitrixModel\Exceptions\ExceptionUpdateElementIblock;
use Alex19pov31\BitrixModel\Traits\Iblock\IblockComponentTrait;
use Alex19pov31\BitrixModel\Traits\Iblock\IblockEventTrait;
use Alex19pov31\BitrixModel\Traits\Iblock\IblockFeatureTrait;
use Alex19pov31\BitrixModel\Traits\Iblock\IblockSeoTrait;
use Alex19pov31\BitrixModel\Traits\Iblock\IblockTrait;
use Bitrix\Main\Loader;
use CIBlockElement;
use CIBlockResult;
use Alex19pov31\BitrixModel\Traits\SefUrlTrait;
use Alex19pov31\BitrixModel\InternalModels\IblockPropertyModel;

abstract class BaseIblockModel extends BaseModel
{
    use IblockTrait;
    use IblockSeoTrait;
    use IblockFeatureTrait;
    use IblockEventTrait;
    use IblockComponentTrait;
    use SefUrlTrait;

    abstract protected static function getIblockId(): int;
    abstract protected static function getCacheMinutes(): int;
    
    protected function getPropertyCodeList(): array
    {
        $fields = appInstance()->getConnection()->getTableFields('b_iblock_element');
        $propertyList = array_keys($fields);
        $list = static::getPropertiesInfo([
            'CODE',
        ])->addField('PROPERTY', function (IblockPropertyModel $prop) {
            return 'PROPERTY_' . $prop->getCode();
        })->column('PROPERTY');

        return array_merge($propertyList, $list);
    }

    protected static function getList(array $params = []): CIBlockResult
    {
        Loader::includeModule('iblock');
        $order = (array) $params['order'];
        $select = (array) $params['select'];

        $nav = false;
        if ((int) $params['limit'] > 0) {
            $nav['nPageSize'] = $params['limit'];
        }

        if ((int) $params['offset'] > 0) {
            $nav['iNumPage'] = (int) $params['offset'] ? ceil($params['offset'] / $params['limit']) : 1;
        }

        $filter = (array) $params['filter'];
        $filter['IBLOCK_ID'] = static::getIblockId();

        return CIBlockElement::GetList($order, $filter, false, $nav, $select);
    }

    public static function getCount(array $filter = []): int
    {
        return (int) static::getList([
          'filter' => $filter,  
        ])->SelectedRowsCount();
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
            throw new ExceptionAddElementIblock((int) $fields['IBLOCK_ID'], $el->LAST_ERROR);
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
            throw new ExceptionUpdateElementIblock((int) $fields['IBLOCK_ID'], $el->LAST_ERROR);
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
        return (bool) CIBlockElement::Delete($id);
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
