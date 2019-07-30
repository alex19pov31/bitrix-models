<?php


namespace Alex19pov31\BitrixModel;


class Query extends \Bitrix\Main\ORM\Query\Query
{
    /**
     * @var BaseModel
     */
    private $modelClass;

    /**
     * Query constructor.
     * @param BaseModel $modelClass
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\SystemException
     */
    public function __construct($source, $modelClass)
    {
        parent::__construct($source);
        $this->modelClass = $modelClass;
    }

    /**
     * @return BaseModel|null
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function fetchObject()
    {
        $data = parent::fetch();
        if(!$data) {
            return null;
        }

        return new $this->modelClass($data);
    }

    /**
     * @return BaseModelCollection
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function fetchCollection()
    {
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