<?php

namespace Alex19pov31\BitrixModel\Traits\Iblock;

use DateTime;
use Alex19pov31\BitrixModel\InternalModels\IblockModel;
use Alex19pov31\BitrixModel\Models\IblockPropertyModel;
use Alex19pov31\BitrixModel\BaseModelCollection;

trait IblockTrait
{
    protected static $iblock;
    protected static $properties;

    abstract public static function getIblockId(): int;
    abstract public static function getPictureSrc(int $fileId, int $width = 0, int $height = 0): string;

    /**
     * Информация о инфоблоке
     *
     * @param array $select
     * @return IblockModel|null
     */
    public static function getIblock(array $select = [])
    {
        $id = static::getIblockId();
        if (empty($select) && static::$iblock[$id] instanceof IblockModel) {
            return static::$iblock[$id];
        }
        
        return static::$iblock[$id] = IblockModel::getById($id, $select);
    }

    /**
     * Информация о свойствах элемента инфоблока
     *
     * @param array $select
     * @return BaseModelCollection
     */
    public static function getPropertiesInfo(array $select = []): BaseModelCollection
    {
        $id = static::getIblockId();
        if (empty($select) && static::$properties[$id] instanceof BaseModelCollection) {
            return static::$properties[$id];
        }

        return static::$properties[$id] = IblockPropertyModel::getListCollection([
            'filter' => [
                '=IBLOCK_ID' => $id,
            ],
            'select' => $select,
        ]);
    }

    /**
     * Название элемента
     *
     * @return string
     */
    public function getName(): string
    {
        return (string)$this['NAME'];
    }

    /**
     * Код элемента
     *
     * @return string
     */
    public function getCode(): string
    {
        return (string)$this['CODE'];
    }

    /**
     * Детальное описание
     *
     * @return string
     */
    public function getDetailText(): string
    {
        return (string)$this['DETAIL_TEXT'];
    }

    /**
     * Описание для предпросмотра
     *
     * @return string
     */
    public function getPreviewText(): string
    {
        return (string)$this['PREVIEW_TEXT'];
    }

    /**
     * Картинка для предпросмотра
     *
     * @param integer $width
     * @param integer $height
     * @return string
     */
    public function getPreviewPictureSrc(int $width = 0, int $height = 0): string
    {
        return static::getPictureSrc((int)$this['PREVIEW_PICTURE'], $width, $height);
    }

    /**
     * Детальная картинка
     *
     * @param integer $width
     * @param integer $height
     * @return string
     */
    public function getDetailPictureSrc(int $width = 0, int $height = 0): string
    {
        return static::getPictureSrc((int)$this['DETAIL_PICTURE'], $width, $height);
    }

    /**
     * Активность элемента
     *
     * @return boolean
     */
    public function isActive(): bool
    {
        return $this['ACTIVE'];
    }

    /**
     * Идентификатор раздела
     *
     * @return integer
     */
    public function getSectionId(): int
    {
        return $this['IBLOCK_SECTION_ID'];
    }

    /**
     * Идентификатор автора
     *
     * @return integer
     */
    public function getAuthorId(): int
    {
        return (int)$this['CREATED_BY'];
    }

    /**
     * Идентификатор последнего редактора
     *
     * @return integer
     */
    public function getEditorId(): int
    {
        return (int)$this['MODIFIED_BY'];
    }

    /**
     * Дата создания
     *
     * @return DateTime|null
     */
    public function getDateCreate()
    {
        if (empty($this['DATE_CREATE'])) {
            return null;
        }

        return new DateTime($this['DATE_CREATE']);
    }
    
    /**
     * Дата обновления
     *
     * @return DateTime|null
     */
    public function getDateUpdate()
    {
        if (empty($this['TIMESTAMP_X'])) {
            return null;
        }

        return new DateTime($this['TIMESTAMP_X']);
    }

    /**
     * Активен с
     *
     * @return DateTime|null
     */
    public function getActiveFrom(): ?DateTime
    {
        if (empty($this['ACTIVE_FROM'])) {
            return null;
        }

        return new DateTime($this['ACTIVE_FROM']);
    }

    /**
     * Активен по
     *
     * @return DateTime|null
     */
    public function getActiveTo()
    {
        if (empty($this['ACTIVE_TO'])) {
            return null;
        }
        
        return new DateTime($this['ACTIVE_TO']);
    }

    /**
     * Идентификатор сортировки
     *
     * @return integer
     */
    public function getSort(): int
    {
        return (int)$this['SORT'];
    }

    public function getXmlId(): string
    {
        return (string)$this['XML_ID'];
    }

    /**
     * Теги
     *
     * @return string
     */
    public function getTags(): string
    {
        return (string)$this['TAGS'];
    }

    /**
     * Количество показов
     *
     * @return integer
     */
    public function getShowCounter(): int
    {
        return (int)$this['SHOW_COUNTER'];
    }

    /**
     * Дата начала показа
     *
     * @return DateTime|null
     */
    public function getShowCounterStart(): ?DateTime
    {
        if (empty($this['SHOW_COUNTER_START'])) {
            return null;
        }
        
        return new DateTime($this['SHOW_COUNTER_START']);
    }
}
