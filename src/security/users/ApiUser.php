<?php
/**
 *
 * User class for api customers.
 *
 * @author Levon Naghashyan
 * @site http://naghashyan.com
 * @mail levon@naghashyan.com
 * @year 2009-2015
 * @package ngs.NgsAdminTools.security.users
 * @version 6.0
 *
 */

namespace ngs\NgsAdminTools\security\users;

use ngs\NgsAdminTools\managers\UserManager;
use ngs\NgsAdminTools\security\UserGroups;

class ApiUser
{

    private $accessToken = null;
    private $userId = null;

    /**
     * register api user
     *
     * @return int userId
     */
    public function register($apiKey, $uuid, $model)
    {
        return UserManager::getInstance()->registerDevice($apiKey, $this->getLevel(), $uuid, $model);
    }

    /**
     * login guest user
     *
     * @return int userId
     */
    public function login()
    {
        return UserManager::getInstance()->login($this->getUserId());
    }

    /**
     * Returns ADMIN level.
     *
     * @return int $API
     */
    public function getLevel()
    {
        return UserGroups::$API;
    }

    public function updateActivity($token = null, $tritonUUID = NULL)
    {
        return UserManager::getInstance()->updateActivity($this->getUserId(), $tritonUUID);
    }

    public function setAccessToken($accessToken)
    {
        $this->accessToken = $accessToken;
    }

    public function getAccessToken()
    {
        return $this->accessToken;
    }

    public function setUserId($userId)
    {
        $this->userId = $userId;
    }

    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Validates user credentials
     *
     * @return TRUE - if validation passed, and FALSE - otherwise
     */
    public function validate()
    {
        return true;
    }
}
