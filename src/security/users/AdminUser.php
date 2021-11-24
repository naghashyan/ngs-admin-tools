<?php
/**
 *
 * This class is a template for all authorized user classes.
 *
 * @author Levon Naghashyan
 * @site http://naghashyan.com
 * @mail levon@naghashyan.com
 * @year 2010-2014
 * @package security.users
 * @version 6.0
 *
 */

namespace ngs\NgsAdminTools\security\users;

use ngs\NgsAdminTools\exceptions\InvalidUserException;
use ngs\NgsAdminTools\managers\UserManager;
use ngs\NgsAdminTools\security\UserGroups;
use ngs\security\users\AbstractNgsUser;

class AdminUser extends AbstractNgsUser
{


    /**
     * @var - unique identifier per session
     */
    protected $uniqueId;

    /**
     * @var - user's invariant identifier
     */
    protected $id;

    protected $level;

    /**
     * @var - user's invariant identifier
     */
    protected $userDto = null;

    private $sessionUserId = 0;

    private $token = 0;

    private $tokenVersion = 1;


    /**
     * Set unique identifier
     *
     * @param object $uniqueId
     */
    public function setUniqueId($uniqueId)
    {
        $this->uniqueId = $uniqueId;
    }

    /**
     * Set permanent identifier
     *
     * @param object $id
     */
    public function setId(int $id)
    {
        $this->id = $id;
    }

    /**
     * Returns unique identifier
     *
     * @return
     */
    public function getUniqueId()
    {
        return $this->uniqueId;
    }

    /**
     * Returns permanent identifier
     *
     * @return
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set permanent identifier
     *
     * @param object $id
     *
     */
    public function setSessionUserId($sessionUserId)
    {

        $this->sessionUserId = $sessionUserId;
    }

    /**
     * Set permanent identifier
     *
     * @param object $id
     * @return
     */
    public function getSessionUserId()
    {
        return $this->sessionUserId;
    }


    public function getLevel()
    {
        return $this->level;
    }

    public function setLevel(int $level)
    {
        $this->level = $level;
    }

    /**
     * Validates user credentials
     *
     * @return bool
     * @throws InvalidUserException
     * @throws \ngs\exceptions\InvalidUserException
     */
    public function validate()
    {
        if ($userDto = UserManager::getInstance()->validate($this->getSessionUserId(),
            UserGroups::getNameByUserLevel($this->getLevel()))
        ) {
            $this->setUserDto($userDto);
            return true;
        }
        throw new InvalidUserException("wrong user");
    }

    /**
     * @param $apiKey
     * @param $UUID
     * @param $model
     * @return string
     * @throws \ngs\exceptions\NgsErrorException
     */
    public function registerDevice($apiKey, $UUID, $model, $expireTime)
    {
        return UserManager::getInstance()->registerDevice($apiKey, $this->getLevel(), $UUID, $model, $expireTime);
    }

    /**
     * @return string
     * @throws InvalidUserException
     * @throws \ngs\exceptions\NgsErrorException
     */
    public function login()
    {

        return UserManager::getInstance()->login($this->getSessionUserId(), $this->getId(), $this->getLevel());
    }

    public function updateActivity($token = null, $tritonUUID = null)
    {
        return UserManager::getInstance()->updateActivity($this->getSessionUserId(), $token, $tritonUUID);
    }

    public function setUserDto($userDto)
    {
        $this->userDto = $userDto;
    }

    public function getUserDto()
    {
        if ($this->userDto == null) {
            $this->userDto = UserManager::getInstance()->getUserById($this->getId());
        }
        return $this->userDto;
    }


    public function setToken($token)
    {
        $this->token = $token;
    }

    public function getToken()
    {
        return $this->token;
    }

    public function setTokenVersion($tokenVersion)
    {
        $this->tokenVersion = $tokenVersion;
    }

    public function getTokenVersion()
    {
        return $this->tokenVersion;
    }


}