<?php
/**
 *
 * UserMapper class is extended class from AbstractMysqlMapper.
 * It contatins all read and write functions which are working with ngs_user_groups table.
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


use ngs\AdminTools\dal\dto\UserGroupDto;
use ngs\dal\mappers\AbstractMysqlMapper;

class UserGroupMapper extends AbstractMysqlMapper
{

    //! Private members.

    private static ?self $instance = null;
    private string $tableName = 'ngs_user_groups';

    /**
     * Returns an singleton instance of this class
     *
     * @return UserGroupMapper
     */
    public static function getInstance(): UserGroupMapper
    {
        if (self::$instance === null) {
            self::$instance = new UserGroupMapper();
        }
        return self::$instance;
    }

    /**
     * @see AbstractMysqlMapper::createDto()
     */
    public function createDto(): UserGroupDto
    {
        return new UserGroupDto();
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


    /**
     * get user by id
     *
     * @param string $userName
     *
     * @return UserGroupDto|null
     */
    private $GET_USER_GROUP_BY_ID = 'SELECT * FROM `%s` WHERE `id` = :id';

    public function getUserGroupById(int $id)
    {
        return $this->fetchRow(sprintf($this->GET_USER_GROUP_BY_ID, $this->getTableName()), ['id' => $id]);
    }



    /**
     * get user by id
     *
     * @param string $userName
     *
     * @return UserGroupDto|null
     */
    private $GET_USER_GROUPS_BY_PARENT_ID = 'SELECT * FROM `%s` WHERE `parent_id` = :parentId';

    public function getUserGroupByParentId(int $parentId)
    {
        return $this->fetchRows(sprintf($this->GET_USER_GROUPS_BY_PARENT_ID, $this->getTableName()), ['parentId' => $parentId]);
    }


    /**
     * get user by id
     *
     * @param string $userName
     *
     * @return UserGroupDto|null
     */
    private $GET_USER_GROUP_BY_NAME = 'SELECT * FROM `%s` WHERE `name` = :groupName';

    public function getUserGroupByName(string $name)
    {
        return $this->fetchRow(sprintf($this->GET_USER_GROUP_BY_NAME, $this->getTableName()), ['groupName' => $name]);
    }


    /**
     * get user by id
     *
     * @param string $userName
     *
     * @return UserGroupDto|null
     */
    private $GET_USER_GROUP_BY_OTHER_NAME = 'SELECT * FROM `%s` WHERE `name` != :groupName';

    public function getUserGroupByOtherName(string $name)
    {
        return $this->fetchRows(sprintf($this->GET_USER_GROUP_BY_OTHER_NAME, $this->getTableName()), ['groupName' => $name]);
    }

    /**
     * get user by level
     *
     * @param string $userName
     *
     * @return UserGroupDto|null
     */
    private $GET_USER_GROUP_BY_LEVEL = 'SELECT * FROM `%s` WHERE `level` = :groupLevel';

    public function getUserGroupByLevel(int $level)
    {
        return $this->fetchRow(sprintf($this->GET_USER_GROUP_BY_LEVEL, $this->getTableName()), ['groupLevel' => $level]);
    }
}
