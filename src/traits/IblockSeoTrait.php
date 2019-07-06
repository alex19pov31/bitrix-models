<?php

namespace Alex19pov31\BitrixModel\Traits;

use Bitrix\Iblock\InheritedProperty\ElementValues;
use Bitrix\Main\Loader;

trait IblockSeoTrait
{
    protected $ipropValues;
    protected $ipropValuesResult;

    abstract public static function getIblockId(): int;
    abstract public function getId(): int;

    /**
     * @return ElementValues
     */
    public function getIpropValues(): ElementValues
    {
        if ($this->ipropValues) {
            return $this->ipropValues;
        }

        Loader::includeModule('iblock');
        return $this->ipropValues = new ElementValues(static::getIblockId(), $this->getId());
    }

    public function getIpropValue(string $propName): string
    {
        return (string)$this->getIpropValues()->getValue('ELEMENT_META_TITLE');
    }

    /**
     * SEO заголовок раздела
     *
     * @return string
     */
    public function getTitle(): string
    {
        if ($this->ipropValuesResult['ELEMENT_PAGE_TITLE']) {
            return $this->ipropValuesResult['ELEMENT_PAGE_TITLE'];
        }

        return $this->ipropValuesResult['ELEMENT_PAGE_TITLE'] = $this->getIpropValue('ELEMENT_PAGE_TITLE');
    }

    /**
     * SEO meta title раздела
     *
     * @return string
     */
    public function getMetaTitle(): string
    {
        if ($this->ipropValuesResult['ELEMENT_META_TITLE']) {
            return $this->ipropValuesResult['ELEMENT_META_TITLE'];
        }

        return $this->ipropValuesResult['ELEMENT_META_TITLE'] = $this->getIpropValue('ELEMENT_META_TITLE');
    }

    /**
     * SEO meta keywords раздела
     *
     * @return string
     */
    public function getMetaKeyWords(): string
    {
        if ($this->ipropValuesResult['ELEMENT_META_KEYWORDS']) {
            return $this->ipropValuesResult['ELEMENT_META_KEYWORDS'];
        }

        return $this->ipropValuesResult['ELEMENT_META_KEYWORDS'] = $this->getIpropValue('ELEMENT_META_KEYWORDS');
    }

    /**
     * SEO description раздела
     *
     * @return string
     */
    public function getMetaDescription(): string
    {
        if ($this->ipropValuesResult['ELEMENT_META_DESCRIPTION']) {
            return $this->ipropValuesResult['ELEMENT_META_DESCRIPTION'];
        }

        return $this->ipropValuesResult['ELEMENT_META_DESCRIPTION'] = $this->getIpropValue('ELEMENT_META_DESCRIPTION');
    }
    /**
     * Инициализировать мета-теги
     *
     * @return void
     */
    public function initMeta()
    {
        bxApp()->SetPageProperty('title', $this->getMetaTitle());
        bxApp()->SetPageProperty('keywords', $this->getMetaKeyWords());
        bxApp()->SetPageProperty('description', $this->getMetaDescription());
    }

    /**
     * Атрибут alt для изображений анонса
     *
     * @return string
     */
    public function getAltPreviewImage(): string
    {
        if ($this->ipropValuesResult['ELEMENT_PREVIEW_PICTURE_FILE_ALT']) {
            return $this->ipropValuesResult['ELEMENT_PREVIEW_PICTURE_FILE_ALT'];
        }

        return $this->ipropValuesResult['ELEMENT_PREVIEW_PICTURE_FILE_ALT'] = $this->getIpropValue('ELEMENT_PREVIEW_PICTURE_FILE_ALT');
    }

    /**
     * Атрибут title для изображений анонса
     *
     * @return string
     */
    public function getTitlePreviewImage(): string
    {
        if ($this->ipropValuesResult['ELEMENT_PREVIEW_PICTURE_FILE_TITLE']) {
            return $this->ipropValuesResult['ELEMENT_PREVIEW_PICTURE_FILE_TITLE'];
        }

        return $this->ipropValuesResult['ELEMENT_PREVIEW_PICTURE_FILE_TITLE'] = $this->getIpropValue('ELEMENT_PREVIEW_PICTURE_FILE_TITLE');
    }

    /**
     * Атрибут alt для детальных изображений
     *
     * @return string
     */
    public function getAltDetailImage(): string
    {
        if ($this->ipropValuesResult['ELEMENT_DETAIL_PICTURE_FILE_ALT']) {
            return $this->ipropValuesResult['ELEMENT_DETAIL_PICTURE_FILE_ALT'];
        }

        return $this->ipropValuesResult['ELEMENT_DETAIL_PICTURE_FILE_ALT'] = $this->getIpropValue('ELEMENT_DETAIL_PICTURE_FILE_ALT');
    }

    /**
     * Атрибут title для детальных изображений
     *
     * @return string
     */
    public function getTitleDetailImage(): string
    {
        if ($this->ipropValuesResult['ELEMENT_DETAIL_PICTURE_FILE_TITLE']) {
            return $this->ipropValuesResult['ELEMENT_DETAIL_PICTURE_FILE_TITLE'];
        }

        return $this->ipropValuesResult['ELEMENT_DETAIL_PICTURE_FILE_TITLE'] = $this->getIpropValue('ELEMENT_DETAIL_PICTURE_FILE_TITLE');
    }
}
