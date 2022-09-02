<?php

/**
 * DeleteAction Class
 * template remove action
 *
 * @author Mikael Mkrtchyan
 * @site   http://naghashyan.com
 * @email  mikael.mkrtchyan@naghashyan.com
 * @package admin.actions.exportTemplates
 * @year   2021
 * @version 1.0.0
 **/

namespace ngs\AdminTools\actions\exportTemplates;

use ngs\AdminTools\actions\AbsctractCmsAction;
use ngs\AdminTools\managers\ExportTemplatesManager;


class DeleteAction extends AbsctractCmsAction
{

    public function service()
    {
        $templateId = $this->args()->template_id;
        $userId = NGS()->getSessionManager()->getUser()->getId();

        $exportTemplatesManager = ExportTemplatesManager::getInstance();
        $success = $exportTemplatesManager->deleteUserSavedTemplate($userId, $templateId);
        $this->addParam('error', !$success);
    }

}