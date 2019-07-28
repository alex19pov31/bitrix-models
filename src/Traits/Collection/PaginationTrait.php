<?php

namespace Alex19pov31\BitrixModel\Traits\Collection;

use Bitrix\Main\UI\PageNavigation;
use Alex19pov31\BitrixModel\BaseModel;

trait PaginationTrait
{
    /**
     * @return BaseModel
     */
    abstract protected function getClass();

    public function getPageNavigation(string $idNav, bool $allowAll = false, $limit = null): PageNavigation
    {
        /**
         * @var BaseModel $class
         */
        $class = $this->getClass();
        $filter = $this->params['filter'] ? $this->params['filter'] : [];
        $cnt = $class::getCount($filter);
        $nav = new PageNavigation($idNav);
        $nav->allowAllRecords($allowAll)
            ->setRecordCount($cnt)
            ->setPageSize($limit ? $limit : $this->count())
            ->initFromUri();

        return $nav;
    }

    /**
     * Показать постарничную навигацию
     *
     * @param string $idNav
     * @param string $template
     * @param boolean $sefMode
     * @param boolean $allowAll
     * @param int|null $limit
     * @param CBitrixComponent|null $component
     * @return void
     */
    public function showPageNavigation(string $idNav, string $template = '', bool $sefMode = false, bool $allowAll = false, $limit = null, $component = null)
    {
        bxApp()->IncludeComponent(
            'bitrix:main.pagenavigation',
            $template,
            [
                "NAV_OBJECT" => $this->getPageNavigation($idNav, $allowAll, $limit),
                "SEF_MODE" => $sefMode ? "Y" : "N",
            ],
            $component ? $component : false
        );
    }
}