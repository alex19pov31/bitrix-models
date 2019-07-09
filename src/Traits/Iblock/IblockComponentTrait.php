<?php

namespace Alex19pov31\BitrixModel\Traits\Iblock;

use CBitrixComponentTemplate;
use Alex19pov31\BitrixModel\InternalModels\IblockModel;

trait IblockComponentTrait
{
    protected static $iblock;
    protected static $properties;

    abstract public static function getIblockId(): int;

    abstract public function getId(): int;
    /**
     * Undocumented function
     *
     * @param array $select
     * @return IblockModel|null
     */
    abstract public static function getIblock(array $select = []);

    /**
     * Область редактирования элемента
     *
     * @param CBitrixComponentTemplate $tpl
     * @param string $description
     * @return string
     */
    public function getEditAreaId(CBitrixComponentTemplate $tpl, string $description = ''): string
    {
        /**
         * @var IblockModel $iblock
         */
        $iblock = static::getIblock();
        if (!$iblock) {
            return '';
        }

        $iblockType = $iblock->getIblockType();
        if (empty($description)) {
            $description = 'Редактировать ' . strtolower($iblock->getElementName());
        }
        initEditIblockElement($tpl, $this->getId(), static::getIblockId(), $iblockType, $description);
    }
}