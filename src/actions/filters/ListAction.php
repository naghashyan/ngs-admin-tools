<?php
/**
 * ListAction Class
 * returns list of filter values
 *
 * @author Mikael Mkrtchyan
 * @site   http://naghashyan.com
 * @email  mikael.mkrtchyan@naghashyan.com
 * @package NGS.NgsAdminTools.actions.filters
 * @year   2020
 * @version 1.0.0
 **/

namespace ngs\NgsAdminTools\actions\filters;

use ngs\NgsAdminTools\actions\AbsctractCmsAction;
use ngs\NgsAdminTools\dal\dto\NgsRuleDto;
use ngs\NgsAdminTools\managers\AbstractCmsManager;
use ngs\NgsAdminTools\managers\NgsRuleManager;

class ListAction extends AbsctractCmsAction
{

    /**
     * executor function
     */
    public final function service()
    {

        $managerClass = $this->args()->manager;
        /** @var AbstractCmsManager $manager */
        $manager = $managerClass::getInstance();
        $tableName = $manager->getMapper()->getTableName();
        $ruleInfo = $this->getRuleInfo($tableName);
        if($ruleInfo) {
            $filterValues = $this->getFilterValuesByRule($tableName . '_filter');
        }
        else {
            $filterValues = $manager->getFilterValues();
        }

        $this->addParam('filterValues', $filterValues);
        $this->addParam('exportableFields', $manager->getExportableFields());
    }


    /**
     * get rule name by table name
     *
     * @param string $tableName
     * @return NgsRuleDto|null
     */
    private function getRuleInfo(string $tableName) {
        $ngsRuleManager = NgsRuleManager::getInstance();
        $rule = new NgsRuleDto();
        $rule->setPriority(1);
        $rule->setName('list');
        $rule->setRuleName($tableName . '_filter');
        $filter = [];

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


    /**
     * returns filters by rule name
     *
     * @param string $ruleName
     * @return array
     */
    private function getFilterValuesByRule(string $ruleName) {
        $ngsRuleManager = NgsRuleManager::getInstance();
        $ruleDto = new NgsRuleDto();
        $ruleDto->setRuleName($ruleName);
        $filterValues = $ngsRuleManager->getFilterValues($ruleDto);

        return $filterValues;
    }
}