<?php
/**
 * SaveAction Class
 * add new rule item
 *
 * @author Mikael Mkrtchyan
 * @site   http://naghashyan.com
 * @email  mikael.mkrtchyan@naghashyan.com
 * @package admin.AdminTools.actions.products.itemClass
 * @year   2020
 * @version 1.0.0
 **/

namespace ngs\AdminTools\actions\rules;

use ngs\AdminTools\actions\AbsctractCmsAction;
use ngs\AdminTools\managers\NgsRuleManager;

class SaveAction extends AbsctractCmsAction
{

    public final function service()
    {
        $ruleManager = NgsRuleManager::getInstance();
        try {
            $newRule = $ruleManager->createRule($this->args()->ruleName, $this->args()->name, $this->args()->filter, $this->args()->actions);
            $this->addParam('success', true);
            $this->addParam('rule_name', $this->args()->ruleName . ' - ' . $this->args()->name);
            if($newRule) {
                $this->addParam('rule_id', $newRule->getId());
            }
        }
        catch(\Exception $exp) {
            $this->addParam('success', false);
        }
    }
}