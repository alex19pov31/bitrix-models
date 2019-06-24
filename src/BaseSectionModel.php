<?php
namespace Alex19pov31\BitrixModel;

use CIBlockSection;
use CIBlockResult;
use DateTime;

abstract class BaseSectionModel extends BaseModel
{
    abstract protected static function getIblockId(): int;

    /**
     * @return BaseIblockModel
     */
    abstract protected static function getElementClass();

    /**
     * Добавление раздела
     *
     * @param array $data
     * @return BaseModel
     */
    public static function add(array $data): BaseModel
    {
        Loader::includeModule('iblock');

        $fields = static::prepareDataIblockElement($data);
        $el = new CIBlockSection;
        $data['ID'] = $el->Add($fields);

        if (!empty($el->LAST_ERROR)) {
            throw new ExceptionAddElementIblock((int)$fields['IBLOCK_ID'], $el->LAST_ERROR);
        }

        return new static($data);
    }

    /**
     * Обновление раздела
     *
     * @param integer $id
     * @param array $data
     * @return BaseModel
     */
    public static function update(int $id, array $data): BaseModel
    {
        Loader::includeModule('iblock');

        $fields = static::prepareDataIblockElement($data);
        $el = new CIBlockSection;
        $el->Update($id, $fields);

        if (!empty($el->LAST_ERROR)) {
            throw new ExceptionAddElementIblock((int)$fields['IBLOCK_ID'], $el->LAST_ERROR);
        }

        return new static([]);
    }

    /**
     * Удаление раздела по идентификатору
     *
     * @param integer $id
     * @return boolean
     */
    public static function delete(int $id): bool
    {
        Loader::includeModule('iblock');
        return (bool)CIBlockSection::Delete($id);
    }

    public static function getList(array $params = []): CIBlockResult
    {
        Loader::includeModule('iblock');
        $order = (array)$params['order'];
        $select = (array)$params['select'];

        $nav = false;
        if ((int)$params['limit'] > 0) {
            $nav['nPageSize'] = $params['limit'];
        }

        if ((int)$params['offset'] > 0) {
            $nav['iNumPage'] = (int)$params['offset'] ? ceil($params['offset'] / $params['limit']) : 1;
        }

        $filter = (array)$params['filter'];
        $filter['IBLOCK_ID'] = static::getIblockId();

        return CIBlockSection::GetList($order, $filter, false, $select, $nav);
    }

    public function getChildTree(): array
    {
        return [];
    }

    public static function getListCollection(array $params = [], $keyBy = null): BaseModelCollection
    {
        $key = static::class . '_' . md5(json_encode($params));
        $list = cache(
            static::getCacheMinutes(),
            $key,
            '/cache_model',
            'cache',
            function () use ($params) {
                initTagCache([
                    'iblock_id_' . static::getIblockId(),
                ]);

                $data = [];
                $result = static::getList($params);
                while ($item = $result->Fetch()) {
                    $data[] = $item;
                }

                return $data;
            }
        );

        if ($keyBy === null) {
            return new BaseModelCollection($list, static::class);
        }

        $newList = [];
        foreach ($list as $item) {
            if (!isset($item[$keyBy])) {
                $newList[] = $item;
                continue;
            }
            $key = $item[$keyBy];
            $newList[$key] = $item;
        }

        return new BaseModelCollection($newList, static::class);
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

    public function getExternalId(): string
    {
        return (string)$this['XML_ID'];
    }

    /**
     * Детальное описание
     *
     * @return string
     */
    public function getDescription(): string
    {
        return (string)$this['DESCRIPTION'];
    }

    /**
     * Уровень вложенности
     *
     * @return integer
     */
    public function getDepthLevel(): int
    {
        return (int)$this['DEPTH_LEVEL'];
    }

    /**
     * Детальная картинка
     *
     * @return string
     */
    public function getDetailPicture(): int
    {
        return (int)$this['DETAIL_PICTURE'];
    }

    public function getDetailPictureSrc($width = null, $height = null): string
    {
        return $this->getPictureSrc($this->getDetailPicture(), $width, $height);
    }

    public function getPicture(): int
    {
        return (int)$this['PICTURE'];
    }

    public function getPictureSrc($width = null, $height = null): string
    {
        return $this->getPictureSrc($this->getPicture(), $width, $height);
    }

    public function getModifiedBy(): int
    {
        return (int)$this['MODIFIED_BY'];
    }

    public function getCreatedBy(): int
    {
        return (int)$this['MODIFIED_BY'];
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
        return (int)$this['SORT'];
    }

    public function getDateCreate(): DateTime
    {
        return new DateTime($this['DATE_CREATE']);
    }

    public function getTimestamp(): DateTime
    {
        return new DateTime($this['TIMESTAMP_X']);
    }

    public function getLeftMargin(): int
    {
        return (int)$this['LEFT_MARGIN'];
    }

    public function getRightMargin(): int
    {
        return (int)$this['RIGHT_MARGIN'];
    }

    public function getElementsList(array $params = [], $keyBy = null): BaseModelCollection
    {
        $params['SECTION_ID'] = $this->getId();
        return static::getElementClass()::getListCollection($params, $keyBy);
    }

    public function getChildList($params = [], $keyBy = null, bool $includeParent = false): BaseModelCollection
    {
        if (!$includeParent) {
            $params['filter']['>EFT_MARGIN'] = $this->getLeftMargin();
            $params['filter']['<RIGHT_MARGIN'] = $this->getRightMargin();
        } else {
            $params['filter']['>=EFT_MARGIN'] = $this->getLeftMargin();
            $params['filter']['<=RIGHT_MARGIN'] = $this->getRightMargin();
        }

        return static::getElementClass()::getListCollection($params, $keyBy);
    }

    public function getParentId(): int
    {
        return (int)$this['SECTION_ID'];
    }

    /**
     * @return BaseSectionModel|null
     */
    public function getParent(array $select = [])
    {
        $id = $this->getParentId();
        return $id ? static::getById($id, $select) : null;
    }

    public function getParentList($params = [], $keyBy = null): BaseModelCollection
    {
        $params['filter']['<LEFT_MARGIN'] = $this->getLeftMargin();
        $params['filter']['>RIGHT_MARGIN'] = $this->getRightMargin();
        return static::getListCollection($params, $keyBy);
    }
}
