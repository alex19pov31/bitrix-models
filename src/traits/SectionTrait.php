<?php

namespace Alex19pov31\BitrixModel\Traits;

use DateTime;

trait SectionTrait
{

    /**
     * Название элемента
     *
     * @return string
     */
    public function getName(): string
    {
        return (string) $this['NAME'];
    }

    /**
     * Код элемента
     *
     * @return string
     */
    public function getCode(): string
    {
        return (string) $this['CODE'];
    }

    public function getExternalId(): string
    {
        return (string) $this['XML_ID'];
    }

    /**
     * Детальное описание
     *
     * @return string
     */
    public function getDescription(): string
    {
        return (string) $this['DESCRIPTION'];
    }

    /**
     * Уровень вложенности
     *
     * @return integer
     */
    public function getDepthLevel(): int
    {
        return (int) $this['DEPTH_LEVEL'];
    }

    /**
     * Детальная картинка
     *
     * @return string
     */
    public function getDetailPicture(): int
    {
        return (int) $this['DETAIL_PICTURE'];
    }

    public function getDetailPictureSrc($width = null, $height = null): string
    {
        return $this->getPictureSrc($this->getDetailPicture(), $width, $height);
    }

    public function getPicture(): int
    {
        return (int) $this['PICTURE'];
    }

    public function getPictureSrc($width = null, $height = null): string
    {
        return $this->getPictureSrc($this->getPicture(), $width, $height);
    }

    public function getModifiedBy(): int
    {
        return (int) $this['MODIFIED_BY'];
    }

    public function getCreatedBy(): int
    {
        return (int) $this['MODIFIED_BY'];
    }

    public function isActive(): bool
    {
        return $this['ACTIVE'] === 'Y';
    }

    public function isGlobalActive(): bool
    {
        return $this['ACTIVE'] === 'Y';
    }

    public function getSort(): int
    {
        return (int) $this['SORT'];
    }

    public function getDateCreate(): DateTime
    {
        return new DateTime($this['DATE_CREATE']);
    }

    public function getDateUpdate(): DateTime
    {
        return new DateTime($this['TIMESTAMP_X']);
    }

    public function getLeftMargin(): int
    {
        return (int) $this['LEFT_MARGIN'];
    }

    public function getRightMargin(): int
    {
        return (int) $this['RIGHT_MARGIN'];
    }

    public function getParentId(): int
    {
        return (int) $this['SECTION_ID'];
    }
}