<?php

namespace Alex19pov31\BitrixModel\Traits\Hl;

use CBitrixComponentTemplate;
use Alex19pov31\BitrixModel\InternalModels\IblockModel;

trait HlComponentTrait
{
    abstract protected static function getTableName(): string;

    abstract public function getId(): int;

    /**
     * Область редактирования элемента
     *
     * @param CBitrixComponentTemplate $tpl
     * @param string $description
     * @return string
     */
    public function getEditAreaId(CBitrixComponentTemplate $tpl, string $description = 'Редактировать элемент'): string
    {
        return initEditHLBlockElement($tpl, $this->getId(), static::getTableName(), $description);
    }
}