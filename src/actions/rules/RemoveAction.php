<?php
/**
 * RemoveAction Class
 * removes rule item
 *
 * @author Mikael Mkrtchyan
 * @site   http://naghashyan.com
 * @email  mikael.mkrtchyan@naghashyan.com
 * @package admin.NgsAdminTools.actions.products.itemClass
 * @year   2020
 * @version 1.0.0
 **/

namespace ngs\NgsAdminTools\actions\rules;

use ngs\NgsAdminTools\actions\AbsctractCmsAction;
use ngs\NgsAdminTools\managers\NgsRuleManager;

class RemoveAction extends AbsctractCmsAction
{

    public final function service()
    {
        $ruleManager = NgsRuleManager::getInstance();
        $result = $ruleManager->deleteRule($this->args()->id);
        $this->addParam('success', $result);
    }
}