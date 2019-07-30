<?php

namespace Alex19pov31\BitrixModel\Entity;

use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Iblock\ElementTable;
use Bitrix\Iblock\PropertyEnumerationTable;
use Bitrix\Iblock\PropertyTable;
use Bitrix\Iblock\SectionTable;
use Bitrix\Main;
use Bitrix\Main\Error;
use Bitrix\Main\Loader;
use Bitrix\Main\ORM\Data\AddResult;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Data\DeleteResult;
use Bitrix\Main\ORM\Data\UpdateResult;
use Bitrix\Main\ORM\Entity;
use Bitrix\Main\ORM\Query\Query;
use Bitrix\Main\Result;
use CFile;

Loader::includeModule('iblock');

class IblockNewElementTable extends ElementTable
{
    private static $extEntity;
    private static $propertyList;
    private static $dataManager;

    public static function getExtEntity(int $iblockId, array $parameters): Entity
    {
        if (static::$extEntity[$iblockId]) {
            return static::$extEntity[$iblockId];
        }

        Loader::includeModule('iblock');
        $propertyList = PropertyTable::getList([
            'filter' => [
                '=IBLOCK_ID' => $iblockId,
            ],
        ])->fetchAll();

        $entity = static::getEntity();
        $dataManager = \Alex19pov31\BitrixORMHelper\DataManager::init('b_iblock_element_prop_s' . $iblockId);
        $entity->addField([
            'data_type' => $dataManager::getEntity(),
            'reference' => array('=this.ID' => 'ref.IBLOCK_ELEMENT_ID'),
        ], 'PROP_TABLE');

        foreach ($propertyList as $property) {
            $isMultiple = $property['MULTIPLE'] === 'Y';
            $code = 'PROPERTY_' . $property['CODE'];

            $type = 'string';
            if ($property['PROPERTY_TYPE'] == PropertyTable::TYPE_NUMBER) {
                $type = 'integer';
            }
            if ($property['PROPERTY_TYPE'] == PropertyTable::TYPE_FILE) {
                $type = 'integer';
            }

            if (!$isMultiple) {
                $entity->addField([
                    'data_type' => $type,
                    'expression' => [
                        '%s', 'PROP_TABLE.PROPERTY_' . $property['ID'],
                    ],
                ], $code);

                $entity->addField([
                    'data_type' => $type,
                    'expression' => [
                        '%s', 'PROP_TABLE.PROPERTY_' . $property['ID'],
                    ],
                ], 'EXT_' . $code)->addFetchDataModifier(function ($item) use ($property, $parameters) {
                    $ttl = (int) $parameters['cache']['ttl'];
                    return static::fetchFieldData($item, $property, $ttl);
                });
                continue;
            }

            $tableName = "b_iblock_element_prop_m{$iblockId}";
            $entity->addField([
                'data_type' => $type,
                'expression' => ["
                    (select GROUP_CONCAT(`" . $tableName . "`.`VALUE` SEPARATOR ';;')
                    from `" . $tableName . "`
                    where
                        `" . $tableName . "`.`IBLOCK_ELEMENT_ID` = %s and
                        `" . $tableName . "`.`IBLOCK_PROPERTY_ID` = '{$property['ID']}'
                    )", 'ID'],
            ], $code)->addFetchDataModifier(function ($item) {
                if (strpos($item, ';;') === false) {
                    return $item;
                }

                return explode(';;', $item);
            });

            $entity->addField([
                'data_type' => $type,
                'expression' => ["
                    (select GROUP_CONCAT(`" . $tableName . "`.`VALUE` SEPARATOR ';;')
                    from `" . $tableName . "`
                    where
                        `" . $tableName . "`.`IBLOCK_ELEMENT_ID` = %s and
                        `" . $tableName . "`.`IBLOCK_PROPERTY_ID` = '{$property['ID']}'
                    )", 'ID'],
            ], 'EXT_' . $code)->addFetchDataModifier(function ($item) use ($property, $parameters) {
                $ttl = (int) $parameters['cache']['ttl'];
                if (strpos($item, ';;') === false) {
                    return static::fetchFieldData($item, $property, $ttl);
                }

                $result = [];
                $list = explode(';;', $item);
                foreach ($list as $value) {
                    $result[] = static::fetchFieldData($value, $property, $ttl);
                }

                return $result;
            });
        }

        return $entity;
    }

    private static function getDataManager(string $tableName): DataManager
    {
        if (static::$dataManager[$tableName]) {
            return static::$dataManager[$tableName];
        }

        return static::$dataManager[$tableName] = \Alex19pov31\BitrixORMHelper\DataManager::init($tableName);
    }

    public static function update($primary, array $data): UpdateResult
    {
        $iblockId = (int) $data['IBLOCK_ID'];
        if (!$iblockId) {
            return (new UpdateResult())
                ->addError(new Error('Не указан инфоблок'));
        }

        $props = [];
        $fields = [];
        foreach ($data as $field => $value) {
            if (strpos($field, 'PROPERTY_') === 0) {
                $code = str_replace('PROPERTY_', '', $field);
                $props[$code] = $value;
                continue;
            }
            $fields[$field] = $value;
        }

        try {
            appInstance()->getConnection()->startTransaction();
            $mainResult = static::getDataManager('b_iblock_element')::update($primary, [
                'fields' => $fields,
            ]);
            if (!$mainResult->isSuccess()) {
                appInstance()->getConnection()->rollbackTransaction();
                return $mainResult;
            }

            $result = static::addProps($primary, $iblockId, $data, $props);
            if ($result !== null) {
                return $result;
            }
        } catch (\Exception $e) {
            appInstance()->getConnection()->rollbackTransaction();
            return (new AddResult())
                ->addError(new Error($e->getMessage(), $e->getCode()));
        }

        return $mainResult;
    }

    /**
     * @param mixed $primary
     * @param integer $iblockId
     * @param array $data
     * @param array $props
     * @return Result|null
     */
    private static function addProps($primary, int $iblockId, array $data, array $props)
    {
        $datamanagerSingleProp = static::getDataManager('b_iblock_element_prop_s' . $iblockId);
        $datamanagerMultiProp = static::getDataManager('b_iblock_element_prop_m' . $iblockId);

        $listProperty = static::getPropertyList($iblockId);
        $addSingleFields = [];
        foreach ($props as $code) {
            $property = $listProperty[$code];
            if (!$property) {
                continue;
            }

            if ($property['MULTIPLE'] === 'Y') {
                sql("
                        delete from `b_iblock_element_prop_m" . $iblockId . "`
                        where `IBLOCK_ELEMENT_ID` = '{$primary} and `IBLOCK_PROPERTY_ID` = " . $property['ID'] . "'
                    ");
                if (is_array($data['PROPERTY_' . $code])) {
                    foreach ($data['PROPERTY_' . $code] as $value) {
                        $result = $datamanagerMultiProp::add([
                            'fields' => [
                                'IBLOCK_ELEMENT_ID' => $primary,
                                'IBLOCK_PROPERTY_ID' => $property['ID'],
                                'VALUE' => $value,
                            ],
                        ]);

                        if (!$result->isSuccess()) {
                            appInstance()->getConnection()->rollbackTransaction();
                            return $result;
                        }
                    }
                } else {
                    $result = $datamanagerMultiProp::add([
                        'fields' => [
                            'IBLOCK_ELEMENT_ID' => $primary,
                            'IBLOCK_PROPERTY_ID' => $property['ID'],
                            'VALUE' => $data['PROPERTY_' . $code],
                        ],
                    ]);
                }
            } else {
                $addSingleFields['PROPERTY_' . $property['ID']] = $data['PROPERTY_' . $code];
            }
        }

        if (!empty($addSingleFields)) {
            $addSingleFields['IBLOCK_ELEMENT_ID'] = $primary;
            $result = $datamanagerSingleProp::add([
                'fields' => $addSingleFields,
            ]);

            if (!$result->isSuccess()) {
                appInstance()->getConnection()->rollbackTransaction();
                return $result;
            }
        }

        return null;
    }

    public static function add(array $data): AddResult
    {
        $iblockId = (int) $data['IBLOCK_ID'];
        if (!$iblockId) {
            return (new AddResult())
                ->addError(new Error('Не указан инфоблок'));
        }

        $props = [];
        $fields = [];
        foreach ($data as $field => $value) {
            if (strpos($field, 'PROPERTY_') === 0) {
                $code = str_replace('PROPERTY_', '', $field);
                $props[$code] = $value;
                continue;
            }
            $fields[$field] = $value;
        }

        try {
            appInstance()->getConnection()->startTransaction();
            $mainResult = static::getDataManager('b_iblock_element')::add([
                'fields' => $fields,
            ]
            );
            if (!$mainResult->isSuccess()) {
                appInstance()->getConnection()->rollbackTransaction();
                return $mainResult;
            }

            $id = $mainResult->getId();
            $result = static::addProps($id, $iblockId, $data, $props);
            if ($result !== null) {
                return $result;
            }
        } catch (\Exception $e) {
            appInstance()->getConnection()->rollbackTransaction();
            return (new AddResult())
                ->addError(new Error($e->getMessage(), $e->getCode()));
        }

        return $mainResult;
    }

    public static function delete($primary): DeleteResult
    {
        $data = ElementTable::getList([
            'filter' => [
                '=ID' => $primary,
            ],
            'select' => [
                'IBLOCK_ID',
            ],
            'limit' => 1,
        ])->fetch();
        $iblockId = (int) $data['IBLOCK_ID'];

        if (!$iblockId) {
            return (new DeleteResult())
                ->addError(new Error('Не найден инфоблок', 404));
        }

        try {
            appInstance()->getConnection()->startTransaction();
            sql("
                delete from `b_iblock_element_prop_s" . $iblockId . "`
                where `IBLOCK_ELEMENT_ID` = '{$primary}'
            ");

            sql("
                delete from `b_iblock_element_prop_m" . $iblockId . "`
                where `IBLOCK_ELEMENT_ID` = '{$primary}'
            ");

            $result = ElementTable::delete($primary);
            if (!$result->isSucces()) {
                appInstance()->getConnection()->rollbackTransaction();
            }

            appInstance()->getConnection()->commitTransaction();
        } catch (\Exception $e) {
            appInstance()->getConnection()->rollbackTransaction();

            return (new DeleteResult())
                ->addError(new Error($e->getMessage(), $e->getCode()));
        }

        return $result;
    }

    private static function getPropValues(int $id, int $iblockId, array $propsCode)
    {
        $listProperty = static::getPropertyList($iblockId);

        $singleValueFields = [];
        $multiValueFields = [];
        foreach ($propsCode as $code) {
            $property = $listProperty[$propsCode];
            if (!$property) {
                continue;
            }

            if ($property['MULTIPLE'] === 'Y') {
                $multiValueFields[] = $property['ID'];
            } else {
                $singleValueFields[$code] = 'PROPERTY_' . $property['ID'];
            }
        }

        $datamanager = static::getDataManager('b_iblock_element_prop_s' . $iblockId);
        $data = $datamanager::getList([
            'filter' => [
                '=IBLOCK_ELEMENT_ID' => $id,
            ],
            'select' => $singleValueFields,
            'limit' => 1,
        ])->fetchAll();

        $datamanager = static::getDataManager('b_iblock_element_prop_m' . $iblockId);
        $data = $datamanager::getList([
            'filter' => [
                '=IBLOCK_ELEMENT_ID' => $id,
                '=IBLOCK_PROPERTY_ID' => $multiValueFields,
            ],
            'select' => [
                'ID',
                'VALUE',
            ],
            'limit' => 1,
        ])->fetchAll();

        return [];
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

        $entity = static::getExtEntity($iblockId, $parameters);
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

        if ($property['PROPERTY_TYPE'] == PropertyTable::TYPE_LIST) {
            Loader::includeModule('iblock');
            return PropertyEnumerationTable::getList([
                'filter' => [
                    '=ID' => $value,
                    '=PROPERTY_ID' => $property['ID'],
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

            if (!$hlBlock) {
                return $value;
            }

            $hlBlockClass = HighloadBlockTable::compileEntity($hlBlock)->getDataClass();
            if (!$hlBlockClass) {
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

    public static function getPropertyList(int $iblockId): array
    {
        if (static::$propertyList[$iblockId]) {
            return static::$propertyList[$iblockId];
        }

        $list = [];
        $res = PropertyTable::getList([
            'filter' => [
                '=IBLOCK_ID' => $iblockId,
            ],
            'select' => [
                '*',
                'CODE',
            ],
        ]);

        while ($property = $res->fetch()) {
            $list[$property['CODE']] = $property;
        }

        return static::$propertyList[$iblockId] = $list;
    }

    private static function getPropertyIdByCode(int $iblockId, string $code): int
    {
        $listProperty = static::getPropertyList($iblockId);
        return (int) $listProperty[$code];
    }
}
