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
 * @version 1.0
 *
 */

namespace ngs\AdminTools\dal\mappers;

use ngs\AdminTools\dal\dto\FilterDto;
use ngs\dal\mappers\AbstractMysqlMapper;

class FilterMapper extends AbstractMysqlMapper
{

    //! Private members.

    private static ?self $instance = null;
    private string $tableName = 'saved_filters';


    /**
     * Returns an singleton instance of this class
     *
     * @return FilterMapper
     */
    public static function getInstance(): FilterMapper
    {
        if (self::$instance === null) {
            self::$instance = new FilterMapper();
        }
        return self::$instance;
    }

    /**
     * @see AbstractMysqlMapper::createDto()
     */
    public function createDto(): FilterDto
    {
        return new FilterDto();
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

    private $GET_USER_SAVED_FILTER_BY_ID = 'SELECT * FROM `%s` WHERE `id` = :id';

    /**
     * get filter by id
     * @param $id
     *
     * @return FilterDto[]
     *
     * @throws \ngs\exceptions\DebugException
     */
    public function getSavedFilterById($id)
    {
        $sqlQuery = sprintf($this->GET_USER_SAVED_FILTER_BY_ID, $this->getTableName());
        return $this->fetchRow($sqlQuery, ['id' => $id]);
    }


    private $GET_USER_SAVED_FILTER_BY_TYPE = 'SELECT * FROM `%s` WHERE (`user_id` = :userId OR `user_id` IS NULL) AND `item_type`=:itemType ORDER BY `id` DESC';

    /**
     * get user saved filters by type
     *
     * @param int $userId
     * @param string $itemType
     *
     * @return FilterDto[]
     *
     * @throws \ngs\exceptions\DebugException
     */
    public function getUserSavedFiltersByType(int $userId, string $itemType)
    {
        $sqlQuery = sprintf($this->GET_USER_SAVED_FILTER_BY_TYPE, $this->getTableName());
        return $this->fetchRows($sqlQuery, ['userId' => $userId, 'itemType' => $itemType]);
    }


    private $GET_USER_PRESELECTED_FILTER = 'SELECT * FROM `%s` WHERE `user_id` = :userId AND `item_type`=:itemType AND preselected=1 ORDER BY `id` DESC';
    private $GET_GENERAL_PRESELECTED_FILTER = 'SELECT * FROM `%s` WHERE `user_id` IS NULL AND `item_type`=:itemType AND preselected=1 ORDER BY `id` DESC';

    public function getEntityPreselectedFilter(int $userId, string $itemType) {
        $sqlQuery = sprintf($this->GET_USER_PRESELECTED_FILTER, $this->getTableName());
        $preselectedFilter = $this->fetchRow($sqlQuery, ['userId' => $userId, 'itemType' => $itemType]);
        if($preselectedFilter) {
            return $preselectedFilter;
        }

        $sqlQuery = sprintf($this->GET_GENERAL_PRESELECTED_FILTER, $this->getTableName());
        $preselectedFilter = $this->fetchRow($sqlQuery, ['itemType' => $itemType]);
        if($preselectedFilter) {
            return $preselectedFilter;
        }

        return null;
    }


    private $GET_USER_SAVED_FILTER_BY_NAME = 'SELECT * FROM `%s` WHERE `user_id` = :userId AND `item_type` = :itemType AND `name`=:name';

    public function getUserSavedFilterByItemTypeAndName(int $userId, string $itemType, string $name)
    {
        $sqlQuery = sprintf($this->GET_USER_SAVED_FILTER_BY_NAME, $this->getTableName());
        return $this->fetchRow($sqlQuery, ['userId' => $userId, 'name' => $name, 'itemType' => $itemType]);
    }
    
    
    private $GET_SAVED_FILTER_BY_NAME = 'SELECT * FROM `%s` WHERE `name` = :name';

    /**
     * get filter by name
     * @param $nmae
     *
     * @return ExportTemplateDto[]
     *
     * @throws \ngs\exceptions\DebugException
     */
    public function getSavedFilterByName(string $name)
    {
        $sqlQuery = sprintf($this->GET_SAVED_FILTER_BY_NAME, $this->getTableName());
        return $this->fetchRow($sqlQuery, ['name' => $name]);
    }

}
