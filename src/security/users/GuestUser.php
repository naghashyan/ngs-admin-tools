<?php
/**
 *
 * User object for non authorized users.
 *
 * @author Levon Naghashyan
 * @site http://naghashyan.com
 * @mail levon@naghashyan.com
 * @year 2009-2014
 * @package ngs.NgsAdminTools.security.users
 * @version 6.0
 *
 */

namespace ngs\NgsAdminTools\security\users;

use ngs\NgsAdminTools\exceptions\InvalidUserException;
use ngs\NgsAdminTools\managers\UserManager;

class GuestUser extends AdminUser
{

    /**
     * @param $apiKey
     * @param $UUID
     * @param $model
     * @return string
     * @throws \ngs\exceptions\NgsErrorException
     */
    public function register($apiKey, $UUID, $model, $expireTime, $tritonUUID) {
        return UserManager::getInstance()->registerDevice($apiKey, $this->getLevel(), $UUID, $model, $expireTime, $tritonUUID);
    }


    /**
     */
    public function getLevel() {
        return \ngs\NgsAdminTools\security\UserGroups::$GUEST;
    }

    /**
     * @param null $token
     * @return bool
     * @throws \ngs\exceptions\DebugException
     */
    public function updateActivity($token = null, $tritonUUID = NULL) {
        return UserManager::getInstance()->updateActivity($this->getSessionUserId(), $token, $tritonUUID);
    }

    /**
     * @return bool|TRUE
     * @throws InvalidUserException
     */
    public function validate() {
        return true;
    }
}
