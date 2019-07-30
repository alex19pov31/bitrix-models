<?php


namespace Alex19pov31\BitrixModel;


class Query extends \Bitrix\Main\ORM\Query\Query
{
    /**
     * @var BaseModel
     */
    private $modelClass;
    private $defaultFilter;

    /**
     * Query constructor.
     * @param BaseModel $modelClass
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\SystemException
     */
    public function __construct($source, $modelClass, array $defaultFilter = [])
    {
        parent::__construct($source);
        $this->modelClass = $modelClass;
        $this->defaultFilter = $defaultFilter;
    }

    public function fetch(Main\Text\Converter $converter = null)
    {
        $this->updateFilter();
        return parent::fetch($converter);
    }

    public function fetchAll(Main\Text\Converter $converter = null)
    {
        $this->updateFilter();
        return parent::fetchAll($converter);
    }

    /**
     * @return BaseModel|null
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function fetchObject()
    {
        $this->updateFilter();
        $data = parent::fetch();
        if(!$data) {
            return null;
        }

        return new $this->modelClass($data);
    }

    private function updateFilter()
    {
        $filter = $this->getFilter();
        $this->setFilter(array_merge($filter, $this->defaultFilter));
    }

    /**
     * @return BaseModelCollection
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function fetchCollection()
    {
        $this->updateFilter();
        $data = parent::fetchAll();
        return new BaseModelCollection(
            $data,
            $this->modelClass,
            [
                'filter' => $this->getFilter(),
                'select' => $this->getSelect(),
                'limit' => $this->getLimit(),
                'offset' => $this->getOffset(),
                'group' => $this->getGroup(),
            ]
        );
    }
}