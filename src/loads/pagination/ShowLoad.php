<?php
/**
 * ShowLoad Class
 * used to load pagination via ajax
 *
 * @author Mikael Mkrtchyan
 * @site   http://naghashyan.com
 * @email  mikael.mkrtchyan@naghashyan.com
 * @package ngs.NgsAdminTools.loads.pagination
 * @year   2021
 * @version 1.0
 **/

namespace ngs\NgsAdminTools\loads\pagination;

use ngs\NgsAdminTools\dal\binparams\NgsCmsParamsBin;
use ngs\NgsAdminTools\dal\dto\NgsRuleDto;
use ngs\NgsAdminTools\loads\AbstractCmsLoad;
use ngs\NgsAdminTools\managers\AbstractCmsManager;
use ngs\NgsAdminTools\managers\NgsRuleManager;

class ShowLoad extends AbstractCmsLoad
{

    protected $im_pagesShowed = 12;

    public final function load() {
        $managerClass = $this->args()->manager;
        $manager = $managerClass::getInstance();
        $itemDto = $manager->createDto();
        $paramsBin = $this->getNgsListBinParams($manager);
        $tableName = $manager->getMapper()->getTableName();
        $ruleForFilter = $this->getFilterRule($tableName, $paramsBin);


        if($ruleForFilter) {
            $ruleForFilter = $this->modifyListRule($ruleForFilter, $itemDto);
            $itemsCount = $manager->getItemsCountByRule($ruleForFilter);
        }
        else {
            $itemDtos = $manager->getList($paramsBin);
            $itemsCount = $manager->getItemsCount($paramsBin);
        }

        $this->addJsonParam('parentContainer', $this->args()->parentContainer);
        $this->initPaging($itemsCount);
    }


    /**
     * @return string
     */
    public function getTemplate(): string
    {
        return NGS()->getTemplateDir('ngs-cms') . '/pagination.tpl';
    }


    /**
     * @param NgsRuleDto $rule
     * @param \ngs\cms\dal\dto\AbstractCmsDto $dto
     *
     * @return NgsRuleDto|void
     *
     * @throws \Exception
     */
    protected function modifyListRule(NgsRuleDto $rule, $dto) {
        return $rule;
    }


    /**
     *
     * set default list params bin
     *
     * @param AbstractCmsManager $manager
     * @return NgsCmsParamsBin
     */

    private function getNgsListBinParams($manager): NgsCmsParamsBin
    {
        $this->args()->ordering = $this->args()->ordering ? $this->args()->ordering : 'DESC';
        $this->args()->sorting = $this->args()->sorting ? $this->args()->sorting : 'id';
        $joinCondition = $manager->getJoinConditionForCategories($this->args()->categoryId);
        $paramsBin = new NgsCmsParamsBin();
        $paramsBin->setSortBy($this->args()->sorting);
        $paramsBin->setOrderBy($this->args()->ordering);

        if ($this->args()->filter) {
            $paramsBin = $manager->setFilterForList($this->args()->filter, $paramsBin);
        }

        $paramsBin->setLimit($this->getLimit());
        $paramsBin->setOffset($this->getOffset());
        $paramsBin->setJoinCondition($joinCondition);
        return $paramsBin;
    }


    /**
     * @param AbstractCmsManager $manager
     * @param NgsCmsParamsBin|null $paramsBin
     * @return NgsRuleDto|null
     */
    private function getFilterRule(string $tableName, NgsCmsParamsBin $paramsBin = null) {
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
        }
        catch(\Exception $exp) {
            $this->getLogger()->error('no rule found for filter ' . $tableName);
            return null;
        }
    }

}
