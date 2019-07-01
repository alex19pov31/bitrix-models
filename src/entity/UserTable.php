<?php

namespace Alex19pov31\BitrixModel\Entity;

use Bitrix\Main\UserTable as OUserTable;
use Bitrix\Main\Entity\ReferenceField;

class UserTable extends OUserTable
{

    public static function getMap()
    {
        $result = parent::getMap();
        $result['FIELDS'] = new ReferenceField(
            'FIELDS',
            '\Alex19pov31\BitrixModel\Entity\UserField',
            array('=this.ID' => 'ref.VALUE_ID'),
            array('join_type' => 'LEFT')
        );

        return $result;
    }
}
