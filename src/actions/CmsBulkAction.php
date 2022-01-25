<?php

/**
 * General parent cms bulk update action.
 *
 *
 * @author Mikael Mkrtcyan
 * @site http://naghashyan.com
 * @mail mikael.mkrtchyan@naghashyan.com
 * @year 2019
 * @package admin.actions
 * @version 7.0.0
 *
 */

namespace ngs\AdminTools\actions;

use ngs\AdminTools\dal\binparams\NgsCmsParamsBin;
use ngs\AdminTools\dal\dto\AbstractCmsDto;
use ngs\AdminTools\managers\AbstractCmsManager;

abstract class CmsBulkAction extends AbsctractCmsAction
{

    /**
     * @return int
     */
    public function getLimit(): int
    {
        if (is_numeric($this->args()->limit) && $this->args()->limit > 0) {
            return $this->args()->limit;
        }
        return 500;
    }


    /**
     * @return int
     */
    public function getOffset(): int
    {
        if (is_numeric($this->args()->offset) && $this->args()->offset > 0) {
            return $this->args()->offset;
        }

        return 0;
    }

    /**
     * @var array
     */
    private $visibleFieldsMethods = [];

    /**
     * @param array $visibleFieldsMethods
     */
    public function setVisibleFieldsMethods(array $visibleFieldsMethods): void
    {
        $this->visibleFieldsMethods = $visibleFieldsMethods;
    }

    /**
     * @return array
     */
    public function getVisibleFieldsMethods(): array
    {
        return $this->visibleFieldsMethods;
    }


    /**
     * @param AbstractCmsDto $cmsDto
     */
    protected function initializeVisibleFieldsMethods($cmsDto): void
    {
        $visibleFieldsMethods = $cmsDto->getVisibleFieldsMethods();
        if (count($visibleFieldsMethods)) {
            $this->setVisibleFieldsMethods($visibleFieldsMethods);
        }
    }


    /**
     *
     * set default list params bin
     *
     * @return NgsCmsParamsBin
     */

    protected function getNgsListBinParams(): ?NgsCmsParamsBin
    {
        $this->args()->ordering = $this->args()->ordering ? $this->args()->ordering : 'DESC';
        $this->args()->sorting = $this->args()->sorting ? $this->args()->sorting : 'id';
        $joinCondition = $this->getJoinCondition();
        $paramsBin = new NgsCmsParamsBin();
        $paramsBin->setSortBy($this->args()->sorting);
        $paramsBin->setOrderBy($this->args()->ordering);

        $this->args()->filter = $this->args()->filter ? json_decode($this->args()->filter, true) : [];

        $searchData = null;
        $searchableFields = $this->getManager()->getSearchableFields();
        if (isset($this->args()->filter['search'])) {
            $searchData = [
                'searchKeys' => $this->args()->filter['search'],
                'searchableFields' => $searchableFields
            ];
        }

        $filter = [];

        foreach ($this->args()->filter as $key => $value) {
            if ($key == 'search') {
                continue;
            }
            $filter[$key] = $value;
        }

        if (!isset($filter['and'])) {
            $filter['and'] = [];
        }

        if (($this->args()->totalSelection === true || $this->args()->totalSelection === 'true') && $this->args()->unCheckedElements) {
            $filter['and'][] = ['fieldName' => 'id', 'conditionType' => 'not_in', 'searchValue' => explode(',', $this->args()->unCheckedElements)];
        } else if (($this->args()->totalSelection === true || $this->args()->totalSelection === 'true') && !$this->args()->unCheckedElements) {
            $filter['and'][] = ['fieldName' => 'id', 'conditionType' => 'not_in', 'searchValue' => [-1]];
        } else if ($this->args()->totalSelection === 'false' || !$this->args()->totalSelection) {
            $inCondition = $this->args()->checkedElements ? explode(',', $this->args()->checkedElements) : [-1];
            $filter['and'][] = ['fieldName' => 'id', 'conditionType' => 'in', 'searchValue' => $inCondition];
        }

        if (!$filter['and']) {
            return null;
        }


        if ($searchableFields || $filter) {
            $paramsBin->setVersion(2);
            $paramsBin->setFilter(['filter' => $filter, 'search' => $searchData, 'table' => $this->getManager()->getMapper()->getTableName()]);
        }

        //TODO: need to be unlimited
        $paramsBin->setLimit(1000);
        $paramsBin->setOffset(0);
        $paramsBin->setJoinCondition($joinCondition);
        $paramsBin = $this->modifyNgsListBinParams($paramsBin);
        return $paramsBin;
    }

    /**
     *
     * modify already set params
     *
     * @param NgsCmsParamsBin $paramsBin
     * @return NgsCmsParamsBin
     */

    protected function modifyNgsListBinParams(NgsCmsParamsBin $paramsBin): NgsCmsParamsBin
    {
        return $paramsBin;
    }

    /**
     * returns load default manager
     *
     * @return AbstractCmsManager
     */
    public abstract function getManager();


    public function getJoinCondition(): string
    {
        return '';
    }

    protected function afterCmsAction(): void
    {
    }

    protected function beforeCmsAction(): void
    {

    }


}
