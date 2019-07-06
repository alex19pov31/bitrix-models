<?php

namespace Alex19pov31\BitrixModel\Entity;

use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\FloatField;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Iblock\ElementTable;
use Bitrix\Sale\ProductTable;
use Bitrix\Iblock\PropertyTable;
use Bitrix\Main\ORM\Fields\ExpressionField;
use Bitrix\Main\Application;

class IblockElementPropertyTable extends DataManager
{
    public static function getTableName()
    {
        return 'b_iblock_element_property';
    }

    public static function getMap()
    {

        return [
            'ID' => new IntegerField('ID', [
                'primary' => true,
            ]),
            'IBLOCK_PROPERTY_ID' => new IntegerField('IBLOCK_PROPERTY_ID'),
            'IBLOCK_ELEMENT_ID' => new IntegerField('IBLOCK_ELEMENT_ID'),
            'VALUE' => (new StringField('VALUE'))->addFetchDataModifier(
                function ($value) {
                    if (!is_string($value)) {
                        return $value;
                    }

                    $result = unserialize($value);
                    if ($result === false) {
                        return $value;
                    }

                    return $result;
                }
            ),
            'VALUE_TYPE' => new StringField('VALUE_TYPE'),
            'VALUE_ENUM' => new IntegerField('VALUE_ENUM'),
            'VALUE_NUM' => new FloatField('VALUE_NUM'),
            'DESCRIPTION' => new StringField('DESCRIPTION'),
            'FIELD' => (new Reference(
                'FIELD',
                \Bitrix\Iblock\Property::class,
                [
                    '=this.IBLOCK_PROPERTY_ID' => 'ref.ID'
                ]
            ))->configureJoinType('LEFT'),
            'ELEMENT' => (new Reference(
                'ELEMENT',
                \Bitrix\Iblock\Element::class,
                [
                    '=this.IBLOCK_ELEMENT_ID' => 'ref.ID'
                ]
            ))->configureJoinType('LEFT'),
            'CODE' => [
                'data_type' => 'string',
                'expression' => [
                    '%s', 'FIELD.CODE'
                ],
            ],
            'NAME' => [
                'data_type' => 'string',
                'expression' => [
                    '%s', 'FIELD.NAME'
                ],
            ],
            'IBLOCK_ID' => [
                'data_type' => 'integer',
                'expression' => [
                    '%s', 'FIELD.IBLOCK_ID'
                ],
            ],
        ];
    }
}
