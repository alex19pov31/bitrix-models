<?php

namespace Alex19pov31\BitrixModel\InternalModels;

use Alex19pov31\BitrixModel\BaseDataManagerModel;
use Bitrix\Main\UserFieldTable;
use Alex19pov31\BitrixModel\BaseModelCollection;

class HlPropertyModel extends BaseDataManagerModel
{
    const TTL = 180;
    protected $enum;

    protected static function getCacheMinutes(): int
    {
        return static::TTL;
    }
    
    /**
     * @return BitrixDataManager
     */
    protected static function getEntity()
    {
        UserFieldTable::class;
    }

    public function getEntityId(): string
    {
        return (string)$this['ENTITY_ID'];
    }

    public function getFieldName(): string
    {
        return (string)$this['FIELD_NAME'];
    }

    public function getUserTypeId(): string
    {
        return (string)$this['USER_TYPE_ID'];
    }

    public function getSort(): int
    {
        return (int)$this['SORT'];
    }

    public function isMultiply(): bool
    {
        return $this['MULTIPLE'] == 'Y';
    }

    public function isMandatory(): bool
    {
        return $this['MANDATORY'] == 'Y';
    }

    public function showFilter(): bool
    {
        return $this['SHOW_FILTER'] == 'Y';
    }

    public function editInList(): bool
    {
        return $this['EDIT_IN_LIST'] == 'Y';
    }

    public function isSearchable(): bool
    {
        return $this['IS_SEARCHABLE'] == 'Y';
    }

    public function getSettings()
    {
        $settings = $this['SETTINGS'];
        if ($data = unserialize($settings)){
            return $data;
        }

        return $settings;
    }

    public function getEnumValues(): BaseModelCollection
    {
        if ($this->enum && $this->enum instanceof BaseModelCollection) {
            return $this->enum;
        }

        return $this->enum = HlPropertyEnumModel::getListCollection([
            'filter' => [
                '=USER_FIELD_ID' => $this->getId(),
            ],
        ]);
    }
}