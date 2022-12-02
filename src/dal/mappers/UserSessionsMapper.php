<?php
/**
 *
 * UserMapper class is extended class from AbstractMysqlMapper.
 * It contatins all read and write functions which are working with ilyov_users table.
 *
 * @author Levon Naghashyan
 * @site http://naghashyan.com
 * @mail levon@naghashyan.com
 * @year 2016-2017
 * @package dal.mappers.user
 * @version 6.5.0
 *
 */

namespace ngs\AdminTools\dal\mappers;

use ngs\AdminTools\dal\dto\UserSessionsDto;
use ngs\dal\mappers\AbstractMysqlMapper;

class UserSessionsMapper extends AbstractMysqlMapper
{

    //! Private members.

    private static ?self $instance = null;
    private string $tableName = 'user_sessions';


    /**
     * Returns an singleton instance of this class
     *
     * @return self
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * @see AbstractMysqlMapper::createDto()
     */
    public function createDto(): UserSessionsDto
    {
        return new UserSessionsDto();
    }

    /**
     * @see AbstractMysqlMapper::getPKFieldName()
     */
    public function getPKFieldName(): string
    {
        return "id";
    }

    /**
     * @see AbstractMysqlMapper::getTableName()
     */
    public function getTableName(): string
    {
        return $this->tableName;
    }

    /**
     * get by id, hash and level
     *
     * @param int $id
     * @param string $hash
     *
     * @return int session user dto
     */
    private $GET_USER_SESSION_BY_USER_ID = "SELECT * FROM `%s` WHERE `user_id` = :userId";

    public function getUserByIdAndHash($userId, $userHash)
    {
        $sqlQuery = sprintf($this->GET_USER_BY_ID_AND_HASH_AND_LEVEL, $this->getTableName());
        return $this->fetchRow($sqlQuery, array("userId" => $userId));
    }

    /**
     * get by id, hash and level
     *
     * @param int $id
     * @param string $hash
     *
     * @return int session user dto
     */
    private $GET_USER_SESSION_BY_USER_ID_AND_SESSION_HASH = "SELECT * FROM `%s` WHERE `user_id` = :userId AND `access_token`=:sessionHash";

    public function getUserSessionByUserIdAndSessionHash($userId, $sessionHash)
    {
        $sqlQuery = sprintf($this->GET_USER_SESSION_BY_USER_ID_AND_SESSION_HASH, $this->getTableName());
        return $this->fetchRow($sqlQuery, array("userId" => $userId, "sessionHash" => $sessionHash));
    }

    /**
     * get by id, hash and level
     *
     * @param int $id
     * @param string $hash
     *
     * @return int session user dto
     */
    private $GET_USER_BY_ID_AND_HASH_AND_LEVEL = "SELECT * FROM `%s` WHERE `user_id` = :userId AND `hashcode`=:userHash";

    public function getUserSessionByUserIdAndHash($userId, $userHash)
    {
        $sqlQuery = sprintf($this->GET_USER_BY_ID_AND_HASH_AND_LEVEL, $this->getTableName());
        return $this->fetchRow($sqlQuery, array("userId" => $userId, "userHash" => $userHash));
    }


    /**
     * get by hash
     *
     * @param string $hash
     *
     * @return UserSessionsDto
     */
    private $GET_USER_BY_HASH = "SELECT * FROM `%s` WHERE `hashcode`=:userHash";

    public function getUserSessionByHash($userHash)
    {
        $sqlQuery = sprintf($this->GET_USER_BY_HASH, $this->getTableName());
        return $this->fetchRow($sqlQuery, array("userHash" => $userHash));
    }

    /**
     * get by id, hash and level
     *
     * @param int $id
     * @param string $hash
     *
     * @return string api_key
     */
    private $GET_USER_API_KEY_BY_SESSION_ID = "SELECT `ilyov_api_keys`.`key` FROM `ilyov_user_sessions` INNER JOIN `ilyov_api_keys` ON `ilyov_user_sessions`.id=:authorizationUserId AND `ilyov_user_sessions`.`api_key_id`=`ilyov_api_keys`.`id` ";

    public function getApiKeyByAuthorizationUserId($authorizationUserId)
    {
        $dto = $this->fetchRow($this->GET_USER_API_KEY_BY_SESSION_ID, array("authorizationUserId" => $authorizationUserId));
        if ($dto) {
            return $dto->getKey();
        }
        return null;
    }

    /**
     * get by id and tokenVersion
     *
     * @param int $id
     * @param int $tokenVersion
     *
     * @return UserSessionsDto
     */
    private $GET_SESSION_USER_BY_ID_AND_TOKEN_VERSION = "SELECT * FROM `%s` WHERE `id`=:id";

    public function getSessionUserByIdAndTokenVersion($id)
    {
        $sqlQuery = sprintf($this->GET_SESSION_USER_BY_ID_AND_TOKEN_VERSION, $this->getTableName());
        return $this->fetchRow($sqlQuery, ["id" => $id]);
    }

    public function deleteNotActiveSessions()
    {
        $sqlQuery = "DELETE FROM `ilyov_user_sessions` WHERE `last_activity_date` < NOW() - INTERVAL 30 DAY";
        $res = $this->executeUpdate($sqlQuery);
        if (is_numeric($res)) {
            return true;
        }
        return false;
    }

}
