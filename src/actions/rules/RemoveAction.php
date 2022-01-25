<?php
/**
 * RemoveAction Class
 * removes rule item
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

class RemoveAction extends AbsctractCmsAction
{

    public final function service()
    {
        $ruleManager = NgsRuleManager::getInstance();
        $result = $ruleManager->deleteRule($this->args()->id);
        $this->addParam('success', $result);
    }
}