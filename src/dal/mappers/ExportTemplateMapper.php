<?php
/**
 *
 * FilterMapper class is extended class from AbstractMysqlMapper.
 * It contatins all read and write functions which are working with saved_filters table.
 *
 * @author Mikael Mkrtchyan
 * @site https://naghashyan.com
 * @mail mikael.mkrtchyan@naghashyan.com
 * @year 2020
 * @package ngs.AdminTools.dal.mappers
 * @version 1.0.0
 *
 */

namespace ngs\AdminTools\dal\mappers;

use ngs\AdminTools\dal\dto\ExportTemplateDto;
use ngs\dal\mappers\AbstractMysqlMapper;

class ExportTemplateMapper extends AbstractMysqlMapper
{

    //! Private members.

    private static ?self $instance=null;
    private string $tableName = 'ngs_saved_export_templates';


    /**
     * Returns an singleton instance of this class
     *
     * @return ExportTemplateMapper
     */
    public static function getInstance(): ExportTemplateMapper
    {
        if (self::$instance === null) {
            self::$instance = new ExportTemplateMapper();
        }
        return self::$instance;
    }

    /**
     * @see AbstractMysqlMapper::createDto()
     */
    public function createDto(): ExportTemplateDto
    {
        return new ExportTemplateDto();
    }

    /**
     * @see AbstractMysqlMapper::getPKFieldName()
     */
    public function getPKFieldName(): string
    {
        return 'id';
    }

    /**
     * @see AbstractMysqlMapper::getTableName()
     */
    public function getTableName(): string
    {
        return $this->tableName;
    }

    private string $GET_USER_SAVED_TEMPLATE_BY_ID = 'SELECT * FROM `%s` WHERE `id` = :id';

    /**
     * get template by id
     * @param $id
     *
     * @return ExportTemplateDto[]
     *
     * @throws \ngs\exceptions\DebugException
     */
    public function getSavedTemplateById($id)
    {
        $sqlQuery = sprintf($this->GET_USER_SAVED_TEMPLATE_BY_ID, $this->getTableName());
        return $this->fetchRow($sqlQuery, ['id' => $id]);
    }


    private string $GET_USER_SAVED_TEMPLATES_BY_TYPE = 'SELECT * FROM `%s` WHERE `user_id` = :userId AND `item_type`=:itemType ORDER BY `id` DESC';

    /**
     * get user saved templates by type
     *
     * @param int $userId
     * @param string $itemType
     *
     * @return ExportTemplateDto[]
     *
     * @throws \ngs\exceptions\DebugException
     */
    public function getUserSavedTemplatesByType(int $userId, string $itemType)
    {
        $sqlQuery = sprintf($this->GET_USER_SAVED_TEMPLATES_BY_TYPE, $this->getTableName());
        return $this->fetchRows($sqlQuery, ['userId' => $userId, 'itemType' => $itemType]);
    }


    private string $GET_USER_SAVED_TEMPLATE_BY_NAME = 'SELECT * FROM `%s` WHERE `user_id` = :userId AND `item_type` = :itemType AND `name`=:name';

    public function getUserSavedTemplateByItemTypeAndName(int $userId, string $itemType, string $name)
    {
        $sqlQuery = sprintf($this->GET_USER_SAVED_TEMPLATE_BY_NAME, $this->getTableName());
        return $this->fetchRow($sqlQuery, ['userId' => $userId, 'name' => $name, 'itemType' => $itemType]);
    }
    
    private string $GET_SAVED_TEMPLATE_BY_NAME = 'SELECT * FROM `%s` WHERE `name` = :name';

    /**
     * get template by name
     * @param $id
     *
     * @return ExportTemplateDto[]
     *
     * @throws \ngs\exceptions\DebugException
     */
    public function getSavedTemplateByName(string $name)
    {
        $sqlQuery = sprintf($this->GET_SAVED_TEMPLATE_BY_NAME, $this->getTableName());
        return $this->fetchRow($sqlQuery, ['name' => $name]);
    }

}
