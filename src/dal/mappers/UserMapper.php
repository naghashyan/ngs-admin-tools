<?php
/**
 *
 * UserMapper class is extended class from AbstractMysqlMapper.
 * It contatins all read and write functions which are working with users table.
 *
 * @author Levon Naghashyan
 * @site https://naghashyan.com
 * @mail levon@naghashyan.com
 * @year 2020
 * @package dal.mappers.user
 * @version 1.0.0
 *
 */

namespace ngs\AdminTools\dal\mappers;

use ngs\AdminTools\dal\dto\UserDto;
use ngs\dal\mappers\AbstractMysqlMapper;

class UserMapper extends AbstractMysqlMapper
{

    //! Private members.

    private static $instance;
    private $tableName = 'users';

    //! A constructor.
    /*!	\brief	Brief description.
     *			Initializes DBMC pointers and table name private
     *			class member.
     */
    function __construct()
    {
        // Initialize the dbmc pointer.
        AbstractMysqlMapper::__construct();
    }

    /**
     * Returns an singleton instance of this class
     *
     * @return UserMapper
     */
    public static function getInstance(): UserMapper
    {
        if (self::$instance === null) {
            self::$instance = new UserMapper();
        }
        return self::$instance;
    }

    /**
     * @see AbstractMysqlMapper::createDto()
     */
    public function createDto(): UserDto
    {
        return new UserDto();
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
     * get user by user_name
     *
     * @param string $userName
     *
     * @return userDto|null
     */
    private $GET_USER_BY_USER_NAME = 'SELECT * FROM `%s` WHERE `user_name` = :username';

    public function getUserByUserName(string $userName): ?UserDto
    {
        return $this->fetchRow(sprintf($this->GET_USER_BY_USER_NAME, $this->getTableName()), ['username' => $userName]);
    }

    /**
     * get user by user_name
     *
     * @param string $userName
     *
     * @return UserDto|null
     */
    private $GET_USER_BY_NAME_AND_EMAIL_QUERY = 'SELECT * FROM `%s` WHERE `user_name` = :username OR `email`=:email';

    public function getUserByNameOREmailAndPass(string $user): ?UserDto
    {
        return $this->fetchRow(sprintf($this->GET_USER_BY_NAME_AND_EMAIL_QUERY, $this->getTableName()), ['username' => $user, 'email' => $user]);
    }

    /**
     * get user by email
     *
     * @param string $email
     *
     * @return userDto|null
     */
    private $GET_USER_BY_EMAIL_QUERY = 'SELECT * FROM `%s` WHERE `email` = :email';

    public function getUserByEmail(string $email): ?UserDto
    {
        return $this->fetchRow(sprintf($this->GET_USER_BY_EMAIL_QUERY, $this->getTableName()), ['email' => $email]);
    }


    private $GET_USER_BY_EMAIL_AND_TYPE_QUERY = 'SELECT * FROM `%s` WHERE `email` = :email && `user_type`=:userType';

    /**
     * @param string $email
     * @param string $userType
     * @return userDto|null
     */
    public function getUserByEmailAndType(string $email, string $userType = 'site'): ?UserDto
    {
        $sqlQuery = sprintf($this->GET_USER_BY_EMAIL_AND_TYPE_QUERY, $this->getTableName());
        return $this->fetchRow($sqlQuery, ['email' => $email, 'user_type' => $userType]);
    }


    private $GET_USERS_BY_GROUP = 'SELECT * FROM `%s` WHERE `user_type`=:userGroup';

    /**
     * @param int $userType
     * @return UserDto[]
     */
    public function getUsersByGroup(int $groupId): array
    {
        $sqlQuery = sprintf($this->GET_USERS_BY_GROUP, $this->getTableName());
        return $this->fetchRows($sqlQuery, ['userGroup' => $groupId]);
    }


    private $GET_USERS_BY_GROUPS = 'SELECT * FROM `%s` WHERE `user_type` IN %s';

    /**
     * @param array $groupIds
     * @return UserDto[]
     */
    public function getUsersByGroups(array $groupIds): array
    {
        if(!$groupIds) {
            return [];
        }

        $inCondition = '(' . implode(',', $groupIds) . ')';
        $sqlQuery = sprintf($this->GET_USERS_BY_GROUPS, $this->getTableName(), $inCondition);
        return $this->fetchRows($sqlQuery, []);
    }


    /**
     * get by lost hash
     *
     * @param string $hash
     *
     * @return int count
     */
    private $GET_USER_BY_LOST_HASH_QUERY = 'SELECT * FROM `%s` WHERE `lost_pass` = :hash';

    public function getUserByLostHash($hash)
    {
        $sqlQuery = sprintf($this->GET_USER_BY_LOST_HASH_QUERY, $this->getTableName());
        return $this->fetchRow($sqlQuery, ['hash' => $hash]);
    }

    /**
     * get by lost hash
     *
     * @param string $hash
     *
     * @return int count
     */
    private $GET_USER_BY_LOST_HASH_AND_USER_ID_QUERY = 'SELECT * FROM `%s` WHERE `lost_pass` = :hash AND `id` = :userId';

    public function getUserByLostHashAndUserId($userId, $hash)
    {
        $sqlQuery = sprintf($this->GET_USER_BY_LOST_HASH_AND_USER_ID_QUERY, $this->getTableName());
        return $this->fetchRow($sqlQuery, ['hash' => $hash, 'userId' => $userId]);
    }

    /**
     * get by id, hash and level
     *
     * @param int $id
     * @param string $hash
     * @param string $level
     *
     * @return int count
     */
    private $GET_USER_BY_ID_AND_HASH_AND_LEVEL = 'SELECT * FROM `ilyov_users` WHERE `id` = :userId AND `hashcode`=:userHash AND `user_level`=:userLevel';

    public function getUserByIdAndHash(int $userId, string $userHash, string $level): ?UserDto
    {
        return $this->fetchRow($this->GET_USER_BY_ID_AND_HASH_AND_LEVEL, ['userId' => $userId, 'userHash' => $userHash, 'userLevel' => $level]);
    }
}
