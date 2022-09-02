<?php
/**
 * General parent load for all admin load classes
 *
 * @author Mikael Mkrtchyan
 * @site   http://naghashyan.com
 * @email  mikael.mkrtchyan@naghashyan.com
 * @year   2021
 * @package ngs.AdminTools.loads
 * @version 1.0
 *
 **/

namespace ngs\AdminTools\loads;

use ngs\AdminTools\dal\binparams\NgsCmsParamsBin;
use ngs\AdminTools\dal\dto\AbstractCmsDto;
use ngs\AdminTools\dal\dto\FilterDto;
use ngs\AdminTools\dal\dto\NgsRuleDto;
use ngs\AdminTools\managers\AbstractCmsManager;
use ngs\AdminTools\managers\ExportTemplatesManager;
use ngs\AdminTools\managers\FilterManager;
use ngs\AdminTools\managers\NgsRuleManager;

abstract class AbstractCmsListLoad extends AbstractCmsLoad
{

    protected $im_limit = 30;
    protected $im_pagesShowed = 5;

    /**
     * @var array
     */
    private $visibleFieldsMethods = ['getId' => ['type' => 'number'], 'display_name' => 'ID', 'data_field_name' => 'id'];

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
     * @return bool
     */
    public function hasDetailPage()
    {
        return true;
    }

    /**
     * @return array
     */
    public function getCmsActions(): array
    {
        return ['edit', 'delete'];
    }


    /**
     * @param AbstractCmsDto $cmsDto
     */
    private function initializeVisibleFieldsMethods($cmsDto): void
    {
        $visibleFieldsMethods = $cmsDto->getVisibleFieldsMethods();
        if (count($visibleFieldsMethods)) {
            $this->setVisibleFieldsMethods($visibleFieldsMethods);
        }
    }

    protected function getChildLoads()
    {
        return [];
    }


    /**
     * if pagination should be loaded via ajax, used for huge tables
     *
     * @return bool
     */
    protected function paginationIsAjax(): bool
    {
        return false;
    }


    /**
     * @throws \ngs\exceptions\DebugException
     */
    public final function load()
    {
        $this->getLogger()->info('load started', (array)$this->args());
        $this->beforeLoad();
        $manager = $this->getManager();
        $itemDto = $manager->createDto();

        $currentUserId = NGS()->getSessionManager()->getUser()->getId();
        $filterManager = FilterManager::getInstance();
        $itemType = $this->getManager()->getMapper()->getTableName();

        if(!$this->args()->filter && !$this->args()->cmsUUID) {
            $preselectedFilter = $this->getPreselectedFilter($currentUserId, $itemType);
            if(!$preselectedFilter) {
                /** @var FilterDto $preselectedFilter */
                $preselectedFilter = $filterManager->getEntityPreselectedFilter($currentUserId, $itemType);
                if($preselectedFilter) {
                    $this->args()->filter = json_decode($preselectedFilter->getFilter(), true);
                }
            }
        }

        $this->initializeVisibleFieldsMethods($itemDto);
        $paramsBin = $this->getNgsListBinParams();
        $ruleForFilter = $this->getFilterRule($paramsBin);
        if ($ruleForFilter) {
            $ruleForFilter = $this->modifyListRule($ruleForFilter, $itemDto);
            $itemDtos = $manager->getItemsByRule($ruleForFilter, $paramsBin);
            if (!$this->paginationIsAjax()) {
                $itemsCount = $manager->getItemsCountByRule($ruleForFilter);
            } else {
                $itemsCount = count($itemDtos) == $this->getLimit() && $this->args()->itemsCount ? $this->args()->itemsCount : count($itemDtos);
            }
        } else {
            $itemDtos = $manager->getList($paramsBin);
            if (!$this->paginationIsAjax()) {
                $itemsCount = $manager->getItemsCount($paramsBin);
            } else {
                $itemsCount = count($itemDtos) == $this->getLimit() && $this->args()->itemsCount ? $this->args()->itemsCount : count($itemDtos);
            }
        }

        $this->addParam('ajaxPagination', $this->paginationIsAjax());
        $this->addJsonParam('manager', get_class($manager));

        $itemDtos = $this->modifyList($itemDtos);
        
        $this->addParam('itemDtos', $itemDtos);
        $this->addAllowedActionsForListIcons($itemDto);
        $this->addParam('hasAddButton', $this->shouldHaveAddButton());
        $childLoads = $this->getChildLoads();
        if ($childLoads) {
            $this->addChildLoadsData($childLoads, $itemDtos);
        }
        $this->addParam('visibleFields', $this->getVisibleFieldsMethods());
        $this->addParam('actions', $this->getCmsActions());

        $this->addJsonParam('listLoad', $manager->getListLoad());
        $this->addJsonParam('editLoad', $manager->getEditLoad());
        $this->addJsonParam('addLoad', $manager->getAddLoad());
        $this->addJsonParam('bulkExcelExportAction', $manager->getBulkExcelExportAction());
        $this->addJsonParam('excelFileDownloadLoad', $manager->getExcelFileDownloadLoad());
        $this->addJsonParam('bulkExportContentGetAction', $manager->getBulkExcelExportContentAction());
        $this->addJsonParam('bulkDeleteAction', $manager->getBulkDeleteAction());
        $this->addJsonParam('rowClickLoad', $manager->getRowClickLoad());
        $this->addJsonParam('mainLoad', $manager->getMainLoad());
        $this->addJsonParam('exportLoad', $manager->getExportLoad());
        $this->addJsonParam('activeMenu', $this->getActiveMenu());
        $this->addJsonParam('deleteAction', $manager->getDeleteAction());
        $this->addJsonParam('bulkUpdateLoad', $this->getBulkUpdateLoad());
        $this->addSortingParams();
        if ($this->args()->parentId) {
            $this->addJsonParam('parentId', $this->args()->parentId);
        }
        $this->initPaging($itemsCount);

        $favoriteFilters = $filterManager->getUserSavedFilters($currentUserId, $itemType);
        $exportTemplateManager = ExportTemplatesManager::getInstance();
        $savedExportTemplates = $exportTemplateManager->getUserSavedTemplates($currentUserId, $itemType);
        $this->addParam('favoriteFilters', $favoriteFilters);
        $this->addJsonParam('savedExportTemplates', $exportTemplateManager->formatSavedTemplates($savedExportTemplates));
        $this->addParam('itemType', $itemType);
        $this->addJsonParam('itemType', $itemType);
        $this->afterCmsLoad($itemDtos, $itemsCount);
        $this->addJsonParam('cmsModal', $this->args()->cmsModal);
        $uuid = $this->args()->cmsUUID ? $this->args()->cmsUUID : uniqid('ngs-AdminTools-', false);
        $this->addParam('cmsUUID', $uuid);
        $this->addJsonParam('cmsUUID', $uuid);

        if ($this->args()->filter) {
            $this->addJsonParam('filter', $this->args()->filter);
        }

        $this->addParam('favoriteFilter', '');
        $this->addParam('hasDetailPage', $this->hasDetailPage());
        if ($this->args()->favoriteFilter) {
            $this->addParam('favoriteFilter', $this->args()->favoriteFilter);
        }

        $this->getLogger()->info('load finished, loaded items count ' . count($itemDtos));
    }


    /**
     * can return filter which will be applied if no filter selected
     *
     * @return null
     */
    protected function getPreselectedFilter() {
        return null;
    }

    /**
     * @param $items
     * @return mixed
     */
    protected function modifyList($items) {
        return $items;
    }


    /**
     * modify list rule
     *
     * @param NgsRuleDto $rule
     * @param AbstractCmsDto $dto
     *
     * @return NgsRuleDto
     */
    protected function modifyListRule(NgsRuleDto $rule, $dto)
    {
        return $rule;
    }

    /**
     * @return string
     */
    public function getTemplate(): string
    {
        return NGS()->getTemplateDir('ngs-AdminTools') . '/list.tpl';
    }


    protected function getFilterValues()
    {
        $manager = $this->getManager();
        $filterValues = $manager->getFilterValues();
        return $filterValues;
    }


    /**
     * add data about child loads
     *
     * @param array $childLoads
     * @param array $itemDtos
     */
    private function addChildLoadsData(array $childLoads, array $itemDtos)
    {
        $dataToAdd = [
            'itemDtos' => $itemDtos,
            'childs' => []
        ];

        foreach ($childLoads as $childLoadName => $params) {
            $childLoadDataToAdd = [
                'childLoad' => $childLoadName,
                'params' => $params
            ];
            $dataToAdd['childs'][] = $childLoadDataToAdd;
        }
        $this->addJsonParam('childLoads', $dataToAdd);

    }


    /**
     * set array of actions for showing edit and delete icons near each row in lists
     * @param $itemDto
     */
    private function addAllowedActionsForListIcons($itemDto)
    {
        $allowedActions = $this->getManager()->getAllowedActions();


        //remove 'add' from array to leave only 'edit' and 'delete' for ngsListFunctionPlugin
        if (in_array('add', $allowedActions)) {
            unset($allowedActions[array_search('add', $allowedActions)]);
        }
        if (!$itemDto->hasWriteAccess('id')) {
            unset($allowedActions[array_search('edit', $allowedActions)]);
            unset($allowedActions[array_search('delete', $allowedActions)]);
        }

        $this->addParam('allowedActions', $allowedActions);
    }


    /**
     * determines whether in list_load template there should be add button or no
     * @return bool
     */
    private function shouldHaveAddButton(): bool
    {
        return $this->getManager()->loadShouldHaveAddButton();

    }


    /**
     *
     * set default list params bin
     *
     * @return NgsCmsParamsBin
     */

    private function getNgsListBinParams(): NgsCmsParamsBin
    {
        $this->args()->ordering = $this->args()->ordering ? $this->args()->ordering : 'DESC';
        $this->args()->sorting = $this->args()->sorting ? $this->args()->sorting : 'id';
        $joinCondition = $this->getJoinCondition();
        $paramsBin = new NgsCmsParamsBin();
        $paramsBin->setSortBy($this->args()->sorting);
        $paramsBin->setOrderBy($this->args()->ordering);

        if ($this->args()->filter) {
            $paramsBin = $this->getManager()->setFilterForList($this->args()->filter, $paramsBin);
        }

        $paramsBin->setLimit($this->getLimit());
        $paramsBin->setOffset($this->getOffset());
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

    private function addSortingParams()
    {
        $this->addParam('sortingParam', [$this->args()->sorting => strtolower($this->args()->ordering)]);
    }

    public function getBulkUpdateLoad()
    {
        return '';
    }

    /**
     * returns load default manager
     *
     * @return AbstractCmsManager
     */
    public abstract function getManager();


    public function getWhereCondition(): string
    {
        return '';
    }

    public function getJoinCondition(): string
    {
        return '';
    }

    protected function afterCmsLoad($itemDtos, $itemsCount): void
    {


    }

    protected function beforeLoad(): void
    {

    }


    /**
     * get rule to filter items
     */
    private function getFilterRule(NgsCmsParamsBin $paramsBin = null)
    {
        $tableName = $this->getManager()->getMapper()->getTableName();
        $ngsRuleManager = NgsRuleManager::getInstance();
        $rule = new NgsRuleDto();
        $rule->setPriority(1);
        $rule->setName('list');
        $rule->setRuleName($tableName . '_filter');
        $filter = $paramsBin->getFilter();

        $rule->setConditions(json_encode($filter, JSON_UNESCAPED_UNICODE));

        try {
            $ngsRuleManager->getRuleClassInfo($rule);
            return $rule;
        } catch (\Exception $exp) {
            $this->getLogger()->info('no rule found for filter ' . $tableName . ': ' . $exp->getMessage());
            return null;
        }
    }
}
