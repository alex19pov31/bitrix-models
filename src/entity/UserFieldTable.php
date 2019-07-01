<?php

namespace Alex19pov31\BitrixModel\Entity;

use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\ORM\Fields\TextField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\FloatField;
use Bitrix\Main\ORM\Fields\BooleanField;

class UserFieldTable extends DataManager
{
    public static function getTableName()
    {
        return 'b_uts_user';
    }

    public static function getUfId()
    {
        return 'USER';
    }

    public static function getMap()
    {
        $result = [];
        $fields = sql('desc b_uts_user')->fetchAll();
        foreach ($fields as $field) {
            $type = preg_replace('/[\(\)\d]+/', '', $field['Type']);
            $name = $field['Field'];
            $isPrimary = $field['Key'];
            $defaultValue = $field['Default'];
            $params = [];
            if ($isPrimary) {
                $params['primary'] = true;
            }
            if ($defaultValue) {
                $params['default_value'] = $defaultValue;
            }

            switch ($type) {
                case 'text':
                    $result[] = new TextField($name, $params);
                    break;
                case 'string':
                    $result[] = new StringField($name, $params);
                    break;
                case 'int':
                    $result[] = new IntegerField($name, $params);
                    break;
                case 'float':
                    $result[] = new FloatField($name, $params);
                    break;
                case 'boolean':
                    $result[] = new BooleanField($name, $params);
                    break;
                case 'char':
                    $result[] = new StringField($name, $params);
                    break;
                default:
                    $result[] = new StringField($name, $params);
                    break;
            }
        }
        return $result;
    }
}
