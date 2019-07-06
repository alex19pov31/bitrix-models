<?php

namespace Alex19pov31\BitrixModel\InternalModels;

use Alex19pov31\BitrixModel\BaseDataManagerModel;
use Bitrix\Iblock\IblockTable;
use DateTime;
use Bitrix\Main\Loader;

class IblockModel extends BaseDataManagerModel
{
    const TTL = 180;

    protected static function getCacheMinutes(): int
    {
        return static::TTL;
    }
    
    /**
     * @return BitrixDataManager
     */
    protected static function getEntity()
    {
        Loader::includeModule('iblock');
        return IblockTable::class;
    }

    public function getIblockType(): string
    {
        return $this['IBLOCK_TYPE_ID'];
    }

    public function getDateUpdate(): DateTime
    {
        return new DateTime($this['TIMESTAMP_X']);
    }

    public function getSiteId(): string
    {
        return (string)$this['LID'];
    }

    public function getCode(): string
    {
        return (string)$this['CODE'];
    }

    public function getName(): string
    {
        return (string)$this['NAME'];
    }

    public function isActive(): bool
    {
        return $this['ACTIVE'] == 'Y';
    }

    public function getSort(): int
    {
        return (int)$this['SORT'];
    }

    public function getXmlId(): string
    {
        return (string)$this['XML_ID'];
    }

    public function getTemplateDetailPageUrl(): string
    {
        return (string)$this['DETAIL_PAGE_URL'];
    }

    public function getTemplateSectionPageUrl(): string
    {
        return (string)$this['SECTION_PAGE_URL'];
    }

    public function getTemplateCanonicalPageUrl(): string
    {
        return (string)$this['CANONICAL_PAGE_URL'];
    }

    public function getPicture(): int
    {
        return (int)$this['PICTURE'];
    }

    public function getPictureSrc($width = null, $height = null): string
    {
        return static::getPictureSrc($this->getPicture(), $width, $height);
    }

    public function getDescription(): string
    {
        return (string)$this['DESCRIPTION'];
    }

    public function getVersion(): int
    {
        return (int)$this['VERSION'];
    }

    public function getSectionsName(): string
    {
        return (string)$this['SECTIONS_NAME'];
    }

    public function getSectionName(): string
    {
        return (string)$this['SECTION_NAME'];
    }

    public function getElementsName(): string
    {
        return (string)$this['ELEMENTS_NAME'];
    }

    public function getElementName(): string
    {
        return (string)$this['ELEMENT_NAME'];
    }
}