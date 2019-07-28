<?php

namespace Alex19pov31\BitrixModel;

use Alex19pov31\BitrixModel\Traits\Iblock\SectionComponentTrait;
use Alex19pov31\BitrixModel\Traits\Iblock\SectionEventTrait;
use Alex19pov31\BitrixModel\Traits\Section\SectionSeoTrait;
use Alex19pov31\BitrixModel\Traits\Section\SectionTrait;
use CIBlockResult;
use CIBlockSection;
use Alex19pov31\BitrixModel\Traits\SefUrlTrait;
use Alex19pov31\BitrixModel\InternalModels\UserFieldModel;

abstract class BaseSectionModel extends BaseModel
{
    use SectionTrait;
    use SectionSeoTrait;
    use SectionEventTrait;
    use SectionComponentTrait;
    use SefUrlTrait;

    abstract protected static function getIblockId(): int;
    abstract protected static function getCacheMinutes(): int;

    protected function getPropertyCodeList(): array
    {
        $fields = appInstance()->getConnection()->getTableFields('b_iblock_section');
        $propertyList = array_keys($fields);
        $list = static::getPropertiesInfo([
            'FIELD_NAME',
        ])->column('FIELD_NAME');

        return array_merge($propertyList, $list);
    }

    /**
     * @return BaseIblockModel|null
     */
    protected static function getElementClass()
    {
        return null;
    }

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
            throw new ExceptionAddElementIblock((int) $fields['IBLOCK_ID'], $el->LAST_ERROR);
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
            throw new ExceptionAddElementIblock((int) $fields['IBLOCK_ID'], $el->LAST_ERROR);
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
        return (bool) CIBlockSection::Delete($id);
    }

    public static function getList(array $params = []): CIBlockResult
    {
        Loader::includeModule('iblock');
        $order = (array) $params['order'];
        $select = (array) $params['select'];

        $nav = false;
        if ((int) $params['limit'] > 0) {
            $nav['nPageSize'] = $params['limit'];
        }

        if ((int) $params['offset'] > 0) {
            $nav['iNumPage'] = (int) $params['offset'] ? ceil($params['offset'] / $params['limit']) : 1;
        }

        $filter = (array) $params['filter'];
        $filter['IBLOCK_ID'] = static::getIblockId();

        return CIBlockSection::GetList($order, $filter, false, $select, $nav);
    }

    public function getChildTree(): array
    {
        return [];
    }

    public static function getCount(array $filter = []): int
    {
        return (int) static::getList([
          'filter' => $filter,  
        ])->SelectedRowsCount();
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
     * @param array $params
     * @param string $keyBy
     * @return BaseModelCollection|null
     */
    public function getElementsList(array $params = [], $keyBy = null)
    {
        $class = static::getElementClass();
        if (!$class) {
            return null;
        }

        $params['SECTION_ID'] = $this->getId();
        return $class::getListCollection($params, $keyBy);
    }

    /**
     * @param array $params
     * @param string $keyBy
     * @param boolean $includeParent
     * @return BaseModelCollection|null
     */
    public function getChildList($params = [], $keyBy = null, bool $includeParent = false)
    {
        if (!$includeParent) {
            $params['filter']['>LEFT_MARGIN'] = $this->getLeftMargin();
            $params['filter']['<RIGHT_MARGIN'] = $this->getRightMargin();
        } else {
            $params['filter']['>=LEFT_MARGIN'] = $this->getLeftMargin();
            $params['filter']['<=RIGHT_MARGIN'] = $this->getRightMargin();
        }

        return static::getListCollection($params, $keyBy);
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
