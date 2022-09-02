<?php

/**
 * GetAction Class
 * return user saved filters for this item type
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


class GetAction extends AbsctractCmsAction {

  public function service() {
      $itemType = $this->args()->item_type;
      if(!$itemType) {
          $this->addParam('items', []);
          return;
      }
      $currentUserId = NGS()->getSessionManager()->getUser()->getId();
      $exportTemplateManager = ExportTemplatesManager::getInstance();
      $savedExportTemplates = $exportTemplateManager->getUserSavedTemplates($currentUserId, $itemType);
      $this->addParam('items', $exportTemplateManager->formatSavedTemplates($savedExportTemplates));
      $this->addParam('saveAction', 'ngs.AdminTools.actions.exportTemplates.save');
      $this->addParam('deleteAction', 'ngs.AdminTools.actions.exportTemplates.delete');
      return;
  }

}