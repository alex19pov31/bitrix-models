<?php

namespace Alex19pov31\BitrixModel;

abstract class BaseModel implements \ArrayAccess
{
    protected $props = [];

    public function __construct(array $data)
    {
        $this->props = $data;
    }

    /**
     * Список элементов
     *
     * @param array $params
     * @return BaseModelCollection
     */
    abstract public static function getListCollection(array $params = [], $keyBy = null): BaseModelCollection;

    /**
     * Добавление элемента
     *
     * @param array $data
     * @return BaseModel
     */
    abstract public static function add(array $data): BaseModel;

    /**
     * Обновление элемента
     *
     * @param integer $id
     * @param array $data
     * @return BaseModel
     */
    abstract public static function update(int $id, array $data): BaseModel;

    /**
     * Удаление элемента по идентификатору
     *
     * @param integer $id
     * @return boolean
     */
    abstract public static function delete(int $id): bool;

    /**
     * Удалить все записи
     *
     * @return void
     */
    public static function deleteAll()
    {
        static::deleteByFilter([]);
    }

    /**
     * Удвление элементов по фильтру
     *
     * @param array $filter
     * @return void
     */
    public static function deleteByFilter(array $filter)
    {
        $list = static::getListCollection([
            'filter' => $filter,
            'select' => ['ID'],
        ]);

        foreach ($list as $item) {
            static::delete($item->getId());
        }
    }

    /**
     * @param integer $id
     * @return BaseModel|null
     */
    public static function getById(int $id, array $select = [])
    {
        return static::getListCollection([
            'filter' => [
                '=ID' => $id,
            ],
            'select' => $select,
        ])->current();
    }


    /**
     * Путь к изображению
     *
     * @param integer $fileId
     * @param integer $width
     * @param integer $height
     * @return string
     */
    public static function getPictureSrc(int $fileId, int $width = 0, int $height = 0): string
    {
        if (!$fileId) {
            return '';
        }

        if (!$width && !$height) {
            return $this->getFileSrc($fileId);
        }

        $size = [];
        if ($width) {
            $size['width'] = $width;
        }
        if ($height) {
            $size['height'] = $height;
        }

        return (string) CFile::ResizeImageGet($fileId, $size)['src'];
    }

    /**
     * Путь к файлу
     *
     * @param integer $fileId
     * @return string
     */
    public function getFileSrc(int $fileId): string
    {
        if (!$fileId) {
            return '';
        }
        return (string) CFile::GetPath($fileId);
    }

    /**
     * Идентификатор элемента
     *
     * @return integer
     */
    public function getId(): int
    {
        return (int) $this['ID'];
    }

    public function toArray(): array
    {
        return (array) $this->props;
    }

    public function toJson(): string
    {
        return (string) json_encode($this->props);
    }

    public function offsetExists($offset): bool
    {
        return isset($this->props[$offset]);
    }
    public function offsetGet($offset)
    {
        return $this->props[$offset];
    }
    public function offsetSet($offset, $value)
    {
        $this->props[$offset] = $value;
    }
    public function offsetUnset($offset)
    {
        unset($this->props[$offset]);
    }

    public function getProps(): array
    {
        return $this->props;
    }

    public static function create(array $data): BaseModel
    {
        $model = new static($data);
        return $model->save();
    }

    /**
     * Сохранить данные
     *
     * @return BaseModel
     */
    public function save(): BaseModel
    {
        $id = $this->getId();
        $data = $this->props;
        unset($data['ID']);
        if ($id) {
            return static::update($this->getId(), $data);
        }

        return static::add($data);
    }
}
