<?php
/**
 *
 * handle all admin session requests
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

use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Token;
use ngs\AdminTools\security\UserGroups;
use ngs\AdminTools\security\users\AdminUser;
use ngs\AdminTools\security\users\GuestUser;
use ngs\AdminTools\exceptions\InvalidUserException;
use ngs\AdminTools\dal\dto\UserDto;
use ngs\AdminTools\dal\mappers\UserGroupMapper;
use ngs\exceptions\DebugException;
use ngs\session\AbstractSessionManager;
use ngs\util\NgsDynamic;
use Jaybizzle\CrawlerDetect\CrawlerDetect;

class SessionManager extends AbstractSessionManager {

    private const IM_TOKEN_COOKIE_KEY = '_im_token';

    private $user = null;
    private $sessionUser = null;

    private $isCrawler = false;
    private $token = null;
    private $decodedToken = null;

    /**
     * set user data into cookie
     *
     * @return void
     */

    public function setUser(string $token): void{

        $sessionTimeout = strtotime(date('Y-m-d', time()) . ' + 5 year');
        $_COOKIE[self::IM_TOKEN_COOKIE_KEY] = $token;
        setcookie(self::IM_TOKEN_COOKIE_KEY, $token,
            [
                'expires' => $sessionTimeout,
                'path' => '/',
                'secure' => false,
                'samesite' => 'Strict',
                'domain' => '.' . NGS()->getHttpUtils()->getMainDomain(),
                'httponly' => false
            ]);

        $this->token = $token;
        $userManager = UserManager::getInstance();
        $userId = $this->getUser() ? $this->getUser()->getId() : null;
        $userData = [];

        if ($userId) {
            $user = $userManager->getUserById($this->getUser()->getId());
            $userData = [
                'id' => $userId,
                'firstName' => $user->getFirstName(),
                'lastName' => $user->getLastName(),
                'userName' => $user->getUserName(),
            ];
        }

        setcookie('user_data', json_encode($userData, JSON_UNESCAPED_UNICODE),
            [
                'expires' => $sessionTimeout,
                'path' => '/',
                'secure' => false,
                'samesite' => 'Strict',
                'domain' => '.' . NGS()->getHttpUtils()->getMainDomain(),
                'httponly' => false
            ]);
    }

    /**
     * delete user info from cookie
     *
     * @return void
     */
    public function deleteUser() {
        $sessionTimeout = time() - 42000;
        setcookie(self::IM_TOKEN_COOKIE_KEY, '', [
            'expires' => $sessionTimeout,
            'path' => '/',
            'secure' => false,
            'samesite' => 'Strict',
            'domain' => '.' . NGS()->getHttpUtils()->getMainDomain(),
            'httponly' => false
        ]);
    }

    /**
     * return admin currently logged in or not
     * user object
     *
     * @return mixed
     * @throws
     */
    public function getUser($force = false) {

        //if user pre defined it will return user object
        if ($this->user != null && $force == false){
            return $this->user;
        }


        $sessionUser = $this->getSessionUser();
        if ($sessionUser == null){
            $user = new GuestUser();
            $user->setLevel(UserGroups::$GUEST);
            $this->user = $user;
            return $this->user;
        }
        $this->user = $this->getUserGroupById($sessionUser->getUserLevel());
        $this->user->setId($sessionUser->getUserId());
        $this->user->setSessionUserId($sessionUser->getSessionUserId());
        $this->user->setToken($sessionUser->getToken());
        return $this->user;
    }

    public function getSessionUser() {
        if ($this->sessionUser){
            return $this->sessionUser;
        }

        $token = $this->getUserToken();
        if ($token == null){
            return null;
        }

        $decodedToken = $this->getDecodedToken();
        $sessionUser = new NgsDynamic();
        $sessionUser->setToken($token);
        $userId = $decodedToken->claims()->get('uid');
        if ($userId == -1){
            $userId = null;
        }
        $sessionUser->setUserId($userId);

        $sessionUser->setUserLevel($decodedToken->claims()->get('level'));
        $sessionUser->setSessionUserId($decodedToken->claims()->get('sid'));
        $sessionUser->setApiKeyId($decodedToken->claims()->get('apiKeyId'));
        $now = new \DateTimeImmutable();
        $sessionUser->setIsExpired($decodedToken->isExpired($now));
        return $sessionUser;

    }

    public function getUserToken() {


        if ($this->token){
            return $this->token;
        }


        if (NGS()->args()->headers()->imtoken){
            $this->token = NGS()->args()->headers()->imtoken;
            return $this->token;
        }
        if (NGS()->get('IM_MODE') !== 'api'){
            if (isset($_COOKIE['_im_token'])){
                $this->token = $_COOKIE['_im_token'];
            }
        }

        return $this->token;
    }

    /**
     * @param $apiKey
     * @param $uuid
     * @param $model
     * @param null $tritonUUID
     * @return string
     */
    public function registerDevice($apiKey, $uuid, $model, $tritonUUID = null) {
        if ($sessionUser = $this->getSessionUser()){
            $user = $this->getUser();
            $refreshedToken = ['token' => $this->token];
            if ($sessionUser->getIsExpired()){
                $refreshedToken = $this->refreshToken();
            }
            $status = $user->updateActivity($refreshedToken, $tritonUUID);
            if ($status){
                $this->token = $refreshedToken['token'];
                if ($sessionUser->getUserId() > 0){
                    $this->setUser($this->token);
                }
                return $this->token;
            }
        }

        if (NGS()->get('IM_MODE') != 'api'){
            return false;
        }
        $user = new GuestUser();
        $expireTime = 4320;
        if (NGS()->get('IM_MODE') == 'api'){
            $expireTime = 8640;
        }
        $token = $user->register($apiKey, $uuid, $model, $expireTime, $tritonUUID);
        //TODO remove this part
        $this->getUser(true);
        $this->token = $token;
        return $token;
    }

    public function refreshToken() {
        $decodedToken = $this->getDecodedToken();
        $params = [
            'userId' => $decodedToken->claims()->get('uid'),
            'sid' => $decodedToken->claims()->get('sid'),
            'expireDate' => 60,
            'userLevel' => $decodedToken->claims()->get('level'),
            'platform' => $decodedToken->claims()->get('os'),
            'ip' => $_SERVER['REMOTE_ADDR'],
            'apiKeyId' => $decodedToken->claims()->get('apiKeyId'),
            'host' => $decodedToken->claims()->get('host'),
            'uuid' => $decodedToken->claims()->get('uuid')
        ];
        $token = UserManager::getInstance()->createToken($params);
        return ['token' => $token];
    }

    /**
     * login customer to system
     * set user hash and update user cookies
     *
     * @param userDto
     * @return boolean
     *
     * @throws InvalidUserException|DebugException
     *
     */

    public function doLogin(UserDto $userDto) {

        $user = $this->getUserGroupById($userDto->getUserType());
        $sessionUser = $this->getSessionUser();

        if ($sessionUser && !$sessionUser->getIsExpired()){
            $this->setUser($sessionUser->getToken());
            if (!$sessionUser->getUserId()){
                return $sessionUser->getToken();
            }
            if ($sessionUser->getUserId() === $userDto->getId()){
                return $sessionUser->getToken();
            }
        }


        $this->token = $user->registerDevice($userDto->getApiKey(), $userDto->getUuid(), $userDto->getModel(), 17280);


        $sessionUser = $this->getSessionUser();
        $user->setId($userDto->getId());
        $user->setSessionUserId($sessionUser->getSessionUserId());
        $user->setToken($sessionUser->getToken());
        $token = $user->login();

        $this->setUser($token);
        $this->getUser(true);
        return $token;
    }

    public function logout() {
        $sessionUserId = $this->getUser()->getSessionUserId();
        UserManager::getInstance()->logout($sessionUserId, $this->getUser()->getLevel());
        $sessionTimeout = strtotime(date('Y-m-d', time()));
        $this->deleteUser();
        return true;
    }


    public function getUserId() {
        if ($this->getUser() == null){
            return null;
        }
        return $this->getUser()->getId();
    }

    public function getSessionUserId() {
        return $this->getUser()->getSessionUserId();
    }

    /**
     * validate all request
     * by user
     *
     * @param object $request
     *
     * @return bool
     */

    public function validateRequest($request, $user = null): bool {

        if ($user == null){
            $user = $this->getUser();
        }
        $userLevel = $user->getLevel();
        $allowedGroupsInfo = $request->getRequestAllowedGroups();

        return $this->userIsAllowed($allowedGroupsInfo, $userLevel);
    }


    /**
     * @param array $allowedGroupsInfo
     * @param int $userLevel
     * @return bool
     */
    public function userIsAllowed(array $allowedGroupsInfo, int $userLevel) {
        if(isset($allowedGroupsInfo['not_allowed']) && in_array($userLevel, $allowedGroupsInfo['not_allowed'])) {
            return false;
        }

        if(!isset($allowedGroupsInfo['allowed']) || !$allowedGroupsInfo['allowed']) {
            return true;
        }

        return $this->userAllowedToDoRequest($allowedGroupsInfo['allowed'], $userLevel);
    }


    /**
     * @param $allowedUserGroups
     * @param $userLevel
     * @return bool
     */
    private function userAllowedToDoRequest($allowedUserGroups, $userLevel) {
        if($allowedUserGroups[0] === "all") {
            return true;
        }
        if(in_array($userLevel, $allowedUserGroups)) {
            return true;
        }

        $userGroupMapper = UserGroupMapper::getInstance();
        $userGroup = $userGroupMapper->getUserGroupById($userLevel);

        if(!$userGroup || !$userGroup->getParentId()) {
            return false;
        }

        return $this->userAllowedToDoRequest($allowedUserGroups, $userGroup->getParentId());
    }


    /**
     * @param $userLevel
     * @return array
     */
    public function getGroupsStructure($userLevel) {
        $groupIds = [$userLevel];
        $this->getChildGroups($userLevel, $groupIds);
        return $groupIds;
    }


    /**
     * @param int $groupId
     * @param $result
     */
    public function getChildGroups(int $groupId, &$result) {
        if(!in_array($groupId, $result)) {
            $result[] = $groupId;
        }
        $userGroupMapper = UserGroupMapper::getInstance();
        $childGroups = $userGroupMapper->getUserGroupByParentId($groupId);
        if($childGroups) {
            foreach($childGroups as $childGroup) {
                $this->getChildGroups($childGroup->getId(), $result);
            }
        }
    }


    public function getCustomerId() {
        if ($this->getUser()->getId() > 0){
            return $this->getUser()->getId();
        }
        return null;
    }

    public function getCustomerDto() {
        if ($this->getUser()->getId() != 0){
            return $this->getUser()->getUserDto();
        }
        return null;
    }

    public function isCrawler() {
        if ($this->isCrawler != null){
            return $this->isCrawler;
        }
        $CrawlerDetect = new CrawlerDetect;
        $this->isCrawler = $CrawlerDetect->isCrawler();
        return $this->isCrawler;
    }

    /**
     * @param bool $forceDecode
     *
     * @return \Lcobucci\JWT\Token|null
     *
     * @throws InvalidUserException
     */
    private function getDecodedToken($forceDecode = false) : Token
    {
        if ($this->decodedToken && !$forceDecode){
            return $this->decodedToken;
        }
        try{
            if ($this->getUserToken() === null) {
                throw new InvalidUserException('invalid token');
            }
            $config = Configuration::forUnsecuredSigner();
            $this->decodedToken = $config->parser()->parse($this->getUserToken());
            return $this->decodedToken;
        } catch (\Exception $e){
            throw new InvalidUserException('invalid token');
        }
    }


    /**
     * returns user group by id
     *
     * @param int|null $groupId
     *
     * @return AdminUser
     *
     * @throws DebugException
     * @throws InvalidUserException
     */
    public function getUserGroupById(?int $groupId)
    {
        if($groupId === null) {
            throw new InvalidUserException("level not provided");
        }
        $userGroupMapper = UserGroupMapper::getInstance();
        $userGroupById = $userGroupMapper->getUserGroupById($groupId);
        if(!$userGroupById) {
            throw new InvalidUserException("user group not found");
        }
        $user = new AdminUser();
        $user->setLevel($userGroupById->getId());
        return $user;
    }


    /**
     * @param string $groupName
     * @return mixed
     * @throws InvalidUserException
     */
    public function getUserGroupByName(string $groupName)
    {
        if(!$groupName) {
            throw new InvalidUserException("user group not provided");
        }
        $userGroupMapper = UserGroupMapper::getInstance();
        $userGroupByName = $userGroupMapper->getUserGroupByName($groupName);

        if(!$userGroupByName) {
            throw new InvalidUserException("user group not found");
        }

        return $userGroupByName;
    }


    /**
     * returns user groups by other name
     *
     * @param string $groupName
     *
     *
     * @throws DebugException
     * @throws InvalidUserException
     */
    public function getUserGroupByOtherName(string $groupName)
    {
        if(!$groupName) {
            throw new InvalidUserException("user group not provided");
        }
        $userGroupMapper = UserGroupMapper::getInstance();
        $userGroupsByNames = $userGroupMapper->getUserGroupByOtherName($groupName);

        return $userGroupsByNames;
    }
}
