<?php

namespace Alex19pov31\BitrixModel\Entity;

use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Iblock\ElementTable;
use Bitrix\Iblock\PropertyTable;
use Bitrix\Main\ORM\Query\Query;
use Bitrix\Main\ORM\Entity;
use Bitrix\Main;
use Bitrix\Main\Loader;
use Bitrix\Main\ORM\Data\AddResult;
use Bitrix\Main\ORM\Data\UpdateResult;
use CFile;
use Bitrix\Iblock\SectionTable;
use Bitrix\Highloadblock\HighloadBlockTable;

class IblockElementTable extends ElementTable
{
    private static $propertyList;

    public static function getMap()
    {
        $result = parent::getMap();
        $result['PROPERTIES'] = (new Reference(
            'PROPERTIES',
            IblockElementPropertyTable::class,
            [
                '=this.ID' => 'ref.IBLOCK_ELEMENT_ID'
            ]
        ))->configureJoinType('LEFT');

        return $result;
    }

    public static function add(array $data)
    {
        $props = [];
        $fields = [];
        foreach($data as $field => $value) {
            if (strpos($field, 'PROPERTY_') === 0) {
                $code = str_replace('PROPERTY_', '', $field);
                $props[$code] = $value;
                continue;
            }
            $fields[$field] = $value;
        }

        appInstance()->getConnection()->startTransaction();
        /**
         * @var AddResult $result
         * @var AddResult $mainResult
         */
        $mainResult = parent::add($fields);
        if (!$mainResult->isSuccess()) {
            appInstance()->getConnection()->rollbackTransaction();
            return $mainResult;
        }

        $iblockId = (int)$data['IBLOCK_ID'];
        foreach($props as $code => $value) {
            $propertyId = static::getPropertyIdByCode($iblockId, $code);
            if (!$propertyId) {
                continue;
            }

            $result = IblockElementPropertyTable::add([
                'IBLOCK_PROPERTY_ID' => $propertyId,
                'IBLOCK_ELEMENT_ID' => $result->getId(),
                'VALUE' => $value,
                'VALUE_TYPE' => '',
            ]);
            if (!$result->isSuccess()) {
                appInstance()->getConnection()->rollbackTransaction();
                return $result;
            }
        }
        appInstance()->getConnection()->commitTransaction();
        return $mainResult;
    }

    public static function update($primary, array $data)
    {
        $props = [];
        $fields = [];
        foreach($data as $field => $value) {
            if (strpos($field, 'PROPERTY_') === 0) {
                $code = str_replace('PROPERTY_', '', $field);
                $props[$code] = $value;
                continue;
            }
            $fields[$field] = $value;
        }
        
        /**
         * @var UpdateResult $mainResult
         * @var UpdateResult $result
         */
        appInstance()->getConnection()->startTransaction();
        $mainResult = parent::update($primary, $fields);
        if (!$mainResult->isSuccess()) {
            appInstance()->getConnection()->rollbackTransaction();
            return $mainResult;
        }

        $propertyValues = static::getPropValues((int)$primary, array_keys($props));
        $iblockId = (int)$data['IBLOCK_ID'];
        foreach($props as $code => $value) {
            $valueId = (int)$propertyValues[$code]['ID'];
            if ($valueId) {
                if (!$iblockId) {
                    continue;
                }

                $propertyId = static::getPropertyIdByCode($iblockId, $code);
                if (!$propertyId) {
                    continue;
                }

                $result = IblockElementPropertyTable::add([
                    'IBLOCK_PROPERTY_ID' => $propertyId,
                    'IBLOCK_ELEMENT_ID' => $primary,
                    'VALUE' => $value,
                    'VALUE_TYPE' => '',
                ]);

                if (!$result->isSuccess()) {
                    appInstance()->getConnection()->rollbackTransaction();
                    return $result;
                }

                continue;
            }

            $result = IblockElementPropertyTable::update($valueId, [
                'IBLOCK_ELEMENT_ID' => $primary,
                'VALUE' => $value,
            ]);

            if (!$result->isSuccess()) {
                appInstance()->getConnection()->rollbackTransaction();
                return $result;
            }
        }
        appInstance()->getConnection()->commitTransaction();
        return $mainResult;
    }

    public static function delete($primary)
    {
        $propList = IblockElementPropertyTable::getList([
            'filter' => [
                'IBLOCK_ELEMENT_ID' => $primary,
            ],
            'select' => [
                'ID',
            ],
        ])->fetchAll();

        appInstance()->getConnection()->startTransaction();
        foreach($propList as $prop) {
            $result = IblockElementPropertyTable::delete($prop['ID']);
            if (!$result->isSuccess()) {
                appInstance()->getConnection()->rollbackTransaction();
                return $result;
            }
        }

        $result = parent::delete($primary);
        if (!$result->isSuccess()) {
            appInstance()->getConnection()->rollbackTransaction();
            return $result;
        }

        appInstance()->getConnection()->commitTransaction();
        return $result;
    }

    private static function getPropValues(int $id, array $propsCode): array
    {
        $result = [];
        $res = IblockElementPropertyTable::getList([
            'filter' => [
                '=IBLOCK_ELEMENT_ID' => $id,
                'CODE' => $propsCode
            ],
            'select' => [
                'ID',
                'IBLOCK_PROPERTY_ID',
                'CODE',
            ]
        ]);

        while($prop = $res->fetch()){
            $result[$prop['CODE']] = $prop;
        }

        return $result;
    }

    private static function prepareFilter(array $filter): array
    {
        if (empty($filter) || !is_array($filter)) {
            return [];
        }

        $result = ['LOGIC' => 'AND'];
        $simpleFilter = [];
        $propFilter = [];

        foreach ($filter as $field => $value) {
            if (strpos($field, 'PROPERTY_') !== false) {
                $arField = explode('PROPERTY_', $field);
                $operator = $arField[0];
                $code = $arField[1];
                $propFilter[] = [
                    $operator . 'PROPERTIES.VALUE' => $value,
                    'PROPERTIES.CODE' => $code,
                ];
                continue;
            }

            $simpleFilter[$field] = $value;
        }

        if (empty($propFilter)) {
            return $filter;
        }

        foreach ($propFilter as $filter) {
            $result[] = array_merge($filter, $simpleFilter);
        }

        return $result;
    }

    public static function getPropertyList(int $iblockId): array
    {
        if (static::$propertyList[$iblockId]) {
            return static::$propertyList[$iblockId];
        }

        $list = [];
        $res = PropertyTable::getList([
            'filter' => [
                '=IBLOCK_ID' => $iblockId
            ],
            'select' => [
                '*',
                'CODE',
            ],
        ]);

        while($property = $res->fetch()) {
            $list[$property['CODE']] = $property;
        }

        return static::$propertyList[$iblockId] = $list;
    }

    private static function getPropertyIdByCode(int $iblockId, string $code): int
    {
        $listProperty = static::getPropertyList($iblockId);
        return (int)$listProperty[$code];
    }

    private static function fetchFieldData($value, array $property, int $ttl = 0)
    {
        if (!$value) {
            return $value;
        }

        if ($property['PROPERTY_TYPE'] == PropertyTable::TYPE_FILE) {
            return CFile::GetPath($value);
        }

        if ($property['PROPERTY_TYPE'] == PropertyTable::TYPE_ELEMENT) {
            Loader::includeModule('iblock');
            return ElementTable::getList([
                'filter' => [
                    '=ID' => $value,
                    '=IBLOCK_ID' => $property['LINK_IBLOCK_ID'],
                ],
                'limit' => 1,
                'cache' => [
                    'ttl' => $ttl,
                ],
            ])->fetch();
        }

        if ($property['PROPERTY_TYPE'] == PropertyTable::TYPE_SECTION) {
            Loader::includeModule('iblock');
            return SectionTable::getList([
                'filter' => [
                    '=ID' => $value,
                    '=IBLOCK_ID' => $property['LINK_IBLOCK_ID'],
                ],
                'limit' => 1,
                'cache' => [
                    'ttl' => $ttl,
                ],
            ])->fetch();
        }

        if ($property['PROPERTY_TYPE'] == PropertyTable::TYPE_STRING && 
            $property['USER_TYPE'] == 'directory') {
            Loader::includeModule('highloadblock');
            $table = $property['USER_TYPE_SETTINGS']['TABLE_NAME'];
            $hlBlock = HighloadBlockTable::getList([
                'filter' => [
                    '=TABLE_NAME' => $table,
                ],
                'limit' => 1,
                'cache' => [
                    'ttl' => $ttl,
                ],
            ])->fetch();
            
            if(!$hlBlock) {
                return $value;
            }

            $hlBlockClass = HighloadBlockTable::compileEntity($hlBlock)->getDataClass();
            if(!$hlBlockClass) {
                return $value;
            }

            return $hlBlockClass::getList([
                'filter' => [
                    '=ID' => $value,
                ],
                'limit' => 1,
                'cache' => [
                    'ttl' => $ttl,
                ],
            ])->fetch();
        }

        return $value;
    }

    public static function getList(array $parameters = array())
    {
        $filter = $parameters['filter'];
        $iblockId = (int) $filter['IBLOCK_ID'];
        if (!$iblockId) {
            return parent::getList($parameters);
        }

        $parameters['filter'] = static::prepareFilter($parameters['filter']);
        $parameters['group'] = $parameters['group'] ? array_merge(['ID'], $parameters['group']) : ['ID'];
        Loader::includeModule('iblock');
        $propertyList = PropertyTable::getList([
            'filter' => [
                '=IBLOCK_ID' => $iblockId
            ],
        ])->fetchAll();

        /**
         * @var Entity $entity
         */
        $entity = static::getEntity();
        foreach ($propertyList as $property) {
            $code = 'PROPERTY_' . $property['CODE'];

            $type = 'string';
            if ($property['PROPERTY_TYPE'] == PropertyTable::TYPE_NUMBER) {
                $type = 'integer';
            }
            if ($property['PROPERTY_TYPE'] == PropertyTable::TYPE_FILE) {
                $type = 'integer';
            }

            $entity->addField([
                'data_type' => $type,
                'expression' => ["
                    (select GROUP_CONCAT(`b_iblock_element_property`.`VALUE` SEPARATOR ';;')
                    from `b_iblock_element_property` 
                    where 
                        `b_iblock_element_property`.`IBLOCK_ELEMENT_ID` = %s and
                        `b_iblock_element_property`.`IBLOCK_PROPERTY_ID` = '{$property['ID']}'
                    )", 'ID']
            ], $code)->addFetchDataModifier(function ($item) {
                if (strpos($item, ';;') === false) {
                    return $item;
                }

                return explode(';;', $item);
            });

            $entity->addField([
                'data_type' => $type,
                'expression' => ["
                    (select GROUP_CONCAT(`b_iblock_element_property`.`VALUE` SEPARATOR ';;')
                    from `b_iblock_element_property` 
                    where 
                        `b_iblock_element_property`.`IBLOCK_ELEMENT_ID` = %s and
                        `b_iblock_element_property`.`IBLOCK_PROPERTY_ID` = '{$property['ID']}'
                    )", 'ID']
            ], 'EXT_'.$code)->addFetchDataModifier(function ($item) use ($property, $parameters) {
                $ttl = (int)$parameters['cache']['ttl'];
                if (strpos($item, ';;') === false) {
                    return static::fetchFieldData($item, $property, $ttl);
                }

                $result = [];
                $list = explode(';;', $item);
                foreach($list as $value) {
                    $result[] = static::fetchFieldData($value, $property, $ttl);
                }

                return $result;
            });
        }

        $query = new Query($entity);
        if (!isset($parameters['select'])) {
            $query->setSelect(array('*'));
        }

        foreach ($parameters as $param => $value) {
            switch ($param) {
                case 'select':
                    $query->setSelect($value);
                    break;
                case 'filter':
                    $value instanceof Filter ? $query->where($value) : $query->setFilter($value);
                    break;
                case 'group':
                    $query->setGroup($value);
                    break;
                case 'order';
                    $query->setOrder($value);
                    break;
                case 'limit':
                    $query->setLimit($value);
                    break;
                case 'offset':
                    $query->setOffset($value);
                    break;
                case 'count_total':
                    $query->countTotal($value);
                    break;
                case 'runtime':
                    foreach ($value as $name => $fieldInfo) {
                        $query->registerRuntimeField($name, $fieldInfo);
                    }
                    break;
                case 'data_doubling':
                    if ($value) {
                        $query->enableDataDoubling();
                    } else {
                        $query->disableDataDoubling();
                    }
                    break;
                case 'cache':
                    $query->setCacheTtl($value["ttl"]);
                    if (isset($value["cache_joins"])) {
                        $query->cacheJoins($value["cache_joins"]);
                    }
                    break;
                default:
                    throw new Main\ArgumentException("Unknown parameter: " . $param, $param);
            }
        }

        return $query->exec();
    }
}
