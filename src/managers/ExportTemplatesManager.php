<?php

/**
 * ExcelExportTemplatesManager manager class
 * used to handle functional related with users saved tempaltes for excel export
 *
 * @author Mikael Mkrtchyan
 * @site http://naghashyan.com
 * @mail mikael.mkrtchyan@naghashyan.com
 * @year 2021
 * @package ngs.AdminTools.managers
 * @version 1.0
 *
 */

namespace ngs\AdminTools\managers;

use ngs\AbstractManager;
use ngs\AdminTools\dal\mappers\ExportTemplateMapper;
use ngs\dal\dto\AbstractDto;

class ExportTemplatesManager extends AbstractManager
{

    /**
     * @var ExportTemplatesManager instance of class
     */
    private static $instance = null;


    /**
     * Returns an singleton instance of this class
     *
     * @return ExportTemplatesManager
     */
    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new ExportTemplatesManager();
        }
        return self::$instance;
    }




    /**
     * get user all saved templates for given type
     *
     * @param $userId
     * @param $itemType
     *
     * @return array|\ngs\dal\dto\AbstractDto[]|null
     *
     * @throws \Exception
     */
    public function getUserSavedTemplates($userId, $itemType) {
        $mapper = ExportTemplateMapper::getInstance();
        $templates = $mapper->getUserSavedTemplatesByType($userId, $itemType);
        return $templates;
    }


    /**
     * get user all saved template for given type
     *
     * @param $userId
     * @param $itemType
     * @param $name
     *
     * @return AbstractDto|null
     *
     * @throws \Exception
     */
    public function getUserSavedTemplateByItemTypeAndName($userId, $itemType, $name) {
        $mapper = ExportTemplateMapper::getInstance();
        $template = $mapper->getUserSavedTemplateByItemTypeAndName($userId, $itemType, $name);
        return $template;
    }


    /**
     * @param $savedTemplates
     * @return array
     */
    public function formatSavedTemplates($savedTemplates) {
        $result = [];
        foreach($savedTemplates as $savedTemplate) {
            $result[] = [
                'id' => $savedTemplate->getId(),
                'data' => $savedTemplate->getFields(),
                'name' => $savedTemplate->getName()
            ];
        }

        return $result;
    }


    /**
     * save template for given user
     *
     * @param $userId
     * @param $itemType
     * @param $name
     * @param $fields
     *
     * @return int|null
     *
     * @throws \ngs\exceptions\DebugException
     */
    public function saveUserTemplate($userId, $itemType, $name, $fields) {
        $mapper = ExportTemplateMapper::getInstance();
        $templateDto = $mapper->createDto();
        $templateDto->setItemType($itemType);
        $templateDto->setName($name);
        $templateDto->setUserId($userId);
        $templateDto->setFields($fields);

        $id = $mapper->insertDto($templateDto);

        return $id;
    }


    /**
     * @param $id
     * @return \ngs\AdminTools\dal\dto\ExportTemplateDto[]
     * @throws \ngs\exceptions\DebugException
     */
    public function getSavedTemplateById($id) {
        $mapper = ExportTemplateMapper::getInstance();
        $template = $mapper->getSavedTemplateById($id);
        return $template;
    }


    /**
     * delete user saved template
     *
     * @param int $userId
     * @param int $templateId
     *
     * @return bool
     *
     * @throws \ngs\exceptions\DebugException
     */
    public function deleteUserSavedTemplate($userId, $templateId) {
        $mapper = ExportTemplateMapper::getInstance();
        $template = $mapper->getSavedTemplateById($templateId);
        if(!$template) {
            return false;
        }

        if($template->getUserId() != $userId) {
            return false;
        }

        $result = $mapper->deleteByPK($templateId);

        return $result !== null;
    }


}