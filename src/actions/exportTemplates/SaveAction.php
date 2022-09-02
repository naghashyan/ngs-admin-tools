<?php

/**
 * SaveAction Class
 * filter save action
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
use ngs\AdminTools\managers\FilterManager;


class SaveAction extends AbsctractCmsAction {

  public function service() {
      $name = $this->args()->name;
      $fields = json_encode($this->args()->fields);
      $itemType = $this->args()->item_type;
      $userId = NGS()->getSessionManager()->getUser()->getId();

      if(!$name) {
          $this->addParam('error', 1);
          $this->addParam('message', 'name can not be empty');
          return;
      }

      if(!$itemType) {
          $this->addParam('error', 1);
          $this->addParam('message', 'item type can not be empty');
          return;
      }

      $exportTemplatesManager = ExportTemplatesManager::getInstance();
      $existingFilter = $exportTemplatesManager->getUserSavedTemplateByItemTypeAndName($userId, $itemType, $name);

      if($existingFilter && (string)$existingFilter->getId()!==$this->args()->itemId) {
          $this->addParam('error', 1);
          $this->addParam('message', 'Template with name ' . $name . ' already exists');
          return;
      }

      $id = $exportTemplatesManager->saveUserTemplate($userId, $itemType, $name, $fields, $this->args()->itemId);

      $createdTemplate = $exportTemplatesManager->getSavedTemplateById($id);
      $formattedTemplate = $exportTemplatesManager->formatSavedTemplates([$createdTemplate]);

      $this->addParam('error', 0);
      $this->addParam('id', $id);
      $this->addParam('item', $formattedTemplate[0]);

  }

}