<?php

namespace Alex19pov31\BitrixModel\Traits\Section;

use Bitrix\Iblock\InheritedProperty\SectionValues;
use Bitrix\Main\Loader;

trait SectionSeoTrait
{
    protected $ipropValues;
    protected $ipropValuesResult;

    abstract public static function getIblockId(): int;
    abstract public function getId(): int;

    /**
     * @return SectionValues
     */
    public function getIpropValues(): SectionValues
    {
        if ($this->ipropValues) {
            return $this->ipropValues;
        }

        Loader::includeModule('iblock');
        return $this->ipropValues = new SectionValues(static::getIblockId(), $this->getId());
    }

    public function getIpropValue(string $propName): string
    {
        return (string)$this->getIpropValues()->getValue('SECTION_META_TITLE');
    }

    /**
     * SEO заголовок раздела
     *
     * @return string
     */
    public function getTitle(): string
    {
        if ($this->ipropValuesResult['SECTION_PAGE_TITLE']) {
            return $this->ipropValuesResult['SECTION_PAGE_TITLE'];
        }

        return $this->ipropValuesResult['SECTION_PAGE_TITLE'] = $this->getIpropValue('SECTION_PAGE_TITLE');
    }

    /**
     * SEO meta title раздела
     *
     * @return string
     */
    public function getMetaTitle(): string
    {
        if ($this->ipropValuesResult['SECTION_META_TITLE']) {
            return $this->ipropValuesResult['SECTION_META_TITLE'];
        }

        return $this->ipropValuesResult['SECTION_META_TITLE'] = $this->getIpropValue('SECTION_META_TITLE');
    }

    /**
     * SEO meta keywords раздела
     *
     * @return string
     */
    public function getMetaKeyWords(): string
    {
        if ($this->ipropValuesResult['SECTION_META_KEYWORDS']) {
            return $this->ipropValuesResult['SECTION_META_KEYWORDS'];
        }

        return $this->ipropValuesResult['SECTION_META_KEYWORDS'] = $this->getIpropValue('SECTION_META_KEYWORDS');
    }

    /**
     * SEO description раздела
     *
     * @return string
     */
    public function getMetaDescription(): string
    {
        if ($this->ipropValuesResult['SECTION_META_DESCRIPTION']) {
            return $this->ipropValuesResult['SECTION_META_DESCRIPTION'];
        }

        return $this->ipropValuesResult['SECTION_META_DESCRIPTION'] = $this->getIpropValue('SECTION_META_DESCRIPTION');
    }

    /**
     * Инициализировать мета-теги
     *
     * @return void
     */
    public function initMeta()
    {
        bxApp()->SetDirProperty('title', $this->getMetaTitle());
        bxApp()->SetDirProperty('keywords', $this->getMetaKeyWords());
        bxApp()->SetDirProperty('description', $this->getMetaDescription());
    }

    /**
     * Атрибут alt для изображений анонса
     *
     * @return string
     */
    public function getAltPreviewImage(): string
    {
        if ($this->ipropValuesResult['SECTION_PREVIEW_PICTURE_FILE_ALT']) {
            return $this->ipropValuesResult['SECTION_PREVIEW_PICTURE_FILE_ALT'];
        }

        return $this->ipropValuesResult['SECTION_PREVIEW_PICTURE_FILE_ALT'] = $this->getIpropValue('SECTION_PREVIEW_PICTURE_FILE_ALT');
    }

    /**
     * Атрибут title для изображений анонса
     *
     * @return string
     */
    public function getTitlePreviewImage(): string
    {
        if ($this->ipropValuesResult['SECTION_PREVIEW_PICTURE_FILE_TITLE']) {
            return $this->ipropValuesResult['SECTION_PREVIEW_PICTURE_FILE_TITLE'];
        }

        return $this->ipropValuesResult['SECTION_PREVIEW_PICTURE_FILE_TITLE'] = $this->getIpropValue('SECTION_PREVIEW_PICTURE_FILE_TITLE');
    }

    /**
     * Атрибут alt для детальных изображений
     *
     * @return string
     */
    public function getAltDetailImage(): string
    {
        if ($this->ipropValuesResult['SECTION_DETAIL_PICTURE_FILE_ALT']) {
            return $this->ipropValuesResult['SECTION_DETAIL_PICTURE_FILE_ALT'];
        }

        return $this->ipropValuesResult['SECTION_DETAIL_PICTURE_FILE_ALT'] = $this->getIpropValue('SECTION_DETAIL_PICTURE_FILE_ALT');
    }

    /**
     * Атрибут title для детальных изображений
     *
     * @return string
     */
    public function getTitleDetailImage(): string
    {
        if ($this->ipropValuesResult['SECTION_DETAIL_PICTURE_FILE_TITLE']) {
            return $this->ipropValuesResult['SECTION_DETAIL_PICTURE_FILE_TITLE'];
        }

        return $this->ipropValuesResult['SECTION_DETAIL_PICTURE_FILE_TITLE'] = $this->getIpropValue('SECTION_DETAIL_PICTURE_FILE_TITLE');
    }
}
