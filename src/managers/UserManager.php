<?php
/**
 * UserManager mapper class
 *
 * @author Levon Naghashyan
 * @site http://naghashyan.com
 * @mail levon@naghashyan.com
 * @year 2011-2016
 * @package managers
 * @version 6.0
 *
 */

namespace ngs\AdminTools\managers;

use Lcobucci\JWT\Configuration;
use ngs\AdminTools\dal\dto\UserDto;
use ngs\AdminTools\dal\mappers\ApiKeysMapper;
use ngs\AdminTools\dal\mappers\UserSessionsMapper;
use ngs\AdminTools\dal\mappers\UserMapper;
use ngs\AdminTools\util\StringUtil;
use ngs\exceptions\NgsErrorException;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key;

class UserManager extends \ngs\AbstractManager
{

    /**
     * @var UserManager instance of class
     */
    private static $instance = null;


    /**
     * Returns an singleton instance of this class
     *
     * @return UserManager
     */
    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new UserManager();
        }
        return self::$instance;
    }

    /**
     * get user by id
     *
     * @param int $id
     *
     * @return object userDto
     */
    public function getUserById($id)
    {
        return UserMapper::getInstance()->selectByPK($id);
    }


    /**
     * returns list of unique values by field
     * 
     * @param array $users
     * @param string $field
     * @return array
     */
    public function getUniqueValuesFromList(array $users, string $field) {
        $result = [];
        foreach($users as $user) {
            $getter = StringUtil::getGetterByDbName($field);
            if($user->$getter() && !in_array($user->$getter(), $result)) {
                $result[] = $user->$getter();
            }
        }

        return $result;
    }


    /**
     * returns users by group id
     *
     * @param int $groupId
     * @return UserDto[]
     */
    public function getUsersByGroup(int $groupId) :array
    {
        return UserMapper::getInstance()->getUsersByGroup($groupId);
    }

    /**
     * returns users by group ids
     *
     * @param array $groupIds
     * @return UserDto[]
     */
    public function getUsersByGroups(array $groupIds) :array
    {
        return UserMapper::getInstance()->getUsersByGroups($groupIds);
    }


    /**
     * get user by username
     *
     * @param string $userName
     *
     * @return object userDto
     */
    public function getUserByUserName($userName)
    {
        return UserMapper::getInstance()->getUserByUserName($userName);
    }


    private ?UserDto $systemUser = null;
    public function getSystemUser() {
        if($this->systemUser) {
            return $this->systemUser;
        }

        $this->systemUser = $this->getUserByUserName('System');
        return $this->systemUser;
    }

    /**
     * Checks if username exists
     *
     * @param string $username
     *
     * @return TRUE - if was exists , and FALSE otherwise
     */
    public function checkUser($username)
    {
        if (UserMapper::getInstance()->getUserByUserName($username)) {
            return true;
        }
        return false;
    }

    /**
     * get user by email
     *
     * @param string $email
     *
     * @return object userDto
     */
    public function getUserByEmail($email)
    {
        return UserMapper::getInstance()->getUserByEmail($email);
    }

    public function getUserByLostHash($hash)
    {
        return UserMapper::getInstance()->getUserByLostHash($hash);
    }

    public function getUserByLostHashAndUserId($userId, $hash)
    {
        return UserMapper::getInstance()->getUserByLostHashAndUserId($userId, $hash);
    }

    public function getUserByFbId($fbId)
    {
        return UserMapper::getInstance()->getUserByFbId($fbId);
    }

    public function getUserByNameAndPass($userName, $pass)
    {
        if ($userDto = UserMapper::getInstance()->getUserByNameAndPass($userName, $pass)) {
            return $userDto;
        }
        return false;
    }


    public function getUserByNameOREmailAndPass(string $user, string $pass)
    {
        $userDto = UserMapper::getInstance()->getUserByNameOREmailAndPass($user);

        if (!$userDto) {
            return false;
        }
        if (password_verify($pass, $userDto->getPassword())) {
            return $userDto;
        }
        if (md5($pass) === $userDto->getPassword()) {
            $password = password_hash($pass, PASSWORD_DEFAULT);
            $tmpUserDto = UserMapper::getInstance()->createDto();
            $tmpUserDto->setId($userDto->getId());
            $tmpUserDto->setPassword($password);
            UserMapper::getInstance()->updateByPK($tmpUserDto);
            return $userDto;
        }
    }

    /**
     * set to user verify hash, which need to update profile
     *
     * @param $userId
     * @param $verifyHash
     * @return bool|int
     * @throws \ngs\exceptions\DebugException
     */
    public function updateUserVerifyHash($userId, $verifyHash)
    {
        $userDto = $this->getUserById($userId);
        if (!$userDto) {
            return false;
        }
        $userDto->setVerifyHash($verifyHash);
        return UserMapper::getInstance()->updateByPK($userDto);
    }

    /**
     * Checks VIP customers credentials.
     *
     * @param object $adminId
     * @param object $hashcode
     * @return TRUE - if was successfully validated, and FALSE otherwise
     */
    public function registerUser($displayName, $email, $password, $userStatus = "user", $userType = "site")
    {

        $userDto = UserMapper::getInstance()->createDto();
        if (!$userDto->getUserStatusByName($userStatus)) {
            return false;
        }
        $userDto->setPassword($password, true);
        $userDto->setEmail(strtolower($email));
        $userDto->setDisplayName($displayName);
        $userDto->setUserStatus($userStatus);
        $userDto->setUserType($userType);
        if (isset($_COOKIE["theme"])) {
            $userDto->setTheme($_COOKIE["theme"]);
        }
        $userDto->setHashcode(md5($displayName . time()));
        $userId = UserMapper::getInstance()->insertDto($userDto);
        if ($userId) {
            $userDto->setId($userId);
            $sessionUserId = NGS()->getSessionManager()->getSessionUserId();
            if ($sessionUserId) {
                RegistrationManager::getInstance()->createRegistration($sessionUserId, $userId);
            }
            return $userDto;
        }
        return false;
    }


    public function login($sessionUserId, $userId, $level)
    {

        $sessionUserDto = UserSessionsMapper::getInstance()->selectByPK($sessionUserId);
        if (!$sessionUserDto) {
            throw new NgsErrorException("wrong user");
        }

        $newSessionUserDto = UserSessionsMapper::getInstance()->createDto();
        $newSessionUserDto->setId($sessionUserId);
        $newSessionUserDto->setLastLoginDate("NOW()");
        $newSessionUserDto->setUserId($userId);
        $expireTime = 60;
        if (NGS()->get("IM_MODE") == "api") {
            $expireTime = 400;
        }

        $params = ['userId' => $userId, 'sid' => $sessionUserDto->getId(), 'expireDate' => $expireTime,
            'userLevel' => $level, 'platform' => $sessionUserDto->getOs(),
            'ip' => $_SERVER['REMOTE_ADDR'], 'apiKeyId' => $sessionUserDto->getApiKeyId(),
            'host' => $sessionUserDto->getHost(), 'uuid' => $sessionUserDto->getUuid()];

        $newToken = $this->createToken($params);
        $newSessionUserDto->setAccessToken($newToken);
        UserSessionsMapper::getInstance()->updateByPK($newSessionUserDto);
        return $newToken;
    }

    public function logout($sessionUserId, $level)
    {
        UserSessionsMapper::getInstance()->deleteByPK($sessionUserId);
    }

    public function getSessionUser($sessionId)
    {
        return UserSessionsMapper::getInstance()->selectByPK($sessionId);
    }

    public function setTritonUUID($sessionUserId, $uuid)
    {
        $newSessionUserDto = UserSessionsMapper::getInstance()->createDto();
        $newSessionUserDto->setId($sessionUserId);
        $newSessionUserDto->setTritonUuid($uuid);
        return UserSessionsMapper::getInstance()->updateByPK($newSessionUserDto);
    }

    public function updateUserProfileById($userId, $updatesFields)
    {
        if (count($updatesFields) <= 0 || !$userId) {
            return false;
        }
        $userDto = UserMapper::getInstance()->createDto();
        $userDto->fillDtoFromArray($updatesFields);
        $userDto->setId($userId);
        $userDto->setPasswordVerificationHash('NULL');
        return UserMapper::getInstance()->updateByPK($userDto);
    }


    public function updateUserHash($uId)
    {
        $hash = $this->generateHash($uId);
        $userDto = UserMapper::getInstance()->createDto();
        $userDto->setId($uId);
        $userDto->setHashcode($hash);
        UserMapper::getInstance()->setCurrentTimestamp($uId, "last_login_date");
        UserMapper::getInstance()->updateByPK($userDto);
        return $hash;
    }

    public function validateCustomer($userId, $hash)
    {
        return UserMapper::getInstance()->selectUserWithIdAndHash($userId, $hash);
    }

    public function getAllUsers($offset, $limit)
    {
        return UserMapper::getInstance()->getAllUsers($offset, $limit);
    }

    public function getUsersCount()
    {
        return UserMapper::getInstance()->getUsersCount();
    }

    public function removeUserById($userId)
    {
        return UserMapper::getInstance()->deleteByPK($userId);
    }

    public function getPasswordByUserId($userId)
    {
        $userDto = $this->getUserById($userId);
        if ($userDto) {
            return $userDto->getPassword();
        }
        return false;
    }


    public function updateSessionUser($id)
    {
        $userSessionDto = UserSessionsMapper::getInstance()->createDto();
        $userSessionDto->setId($id);
        $userSessionDto->setLastLoginDate("NOW()");
        $userSessionDto->setLastActivityDate("NOW()");
        $userSessionDto->setHashcode(NGS()->getNgsUtils()->getUniqueId());
        UserSessionsMapper::getInstance()->updateByPK($userSessionDto);
        return $userSessionDto;
    }

    public function getUserByEmailAndType($email, $userType = "site")
    {
        return UserMapper::getInstance()->getUserByEmailAndType($email, $userType);
    }


    /**
     * Registers new api user.
     *
     * @param $apiKey
     * @param $userLevel
     * @param null $uuid
     * @param null $model
     * @param int $expireTime
     * @param null $tritonUUID
     * @return string
     * @throws NgsErrorException
     * @throws \ngs\exceptions\DebugException
     */
    public function registerDevice($apiKey, $userLevel, $uuid = null, $model = null, $expireTime = 60)
    {
        $apiUserDto = ApiKeysMapper::getInstance()->getApiUserByKey($apiKey);

        if (!$apiUserDto) {
            throw new NgsErrorException("wrong api key");
        }
        if ($uuid && $model) {
            $hashCode = md5($uuid . $model);
            if ($userSessionDto = UserSessionsMapper::getInstance()->getUserSessionByHash($hashCode)) {
                $this->updateActivity($userSessionDto->getId());
                return $userSessionDto->getAccessToken();
            }
        }

        if ($uuid && $model) {
            $hashCode = md5($uuid . $model);
        } else {
            $hashCode = NGS()->getNgsUtils()->getUniqueId();
        }
        $platform = $apiUserDto->getMode();


        $userSessionDto = UserSessionsMapper::getInstance()->createDto();
        $userSessionDto->setHashcode($hashCode);
        $userSessionDto->setUuid($uuid);
        $userSessionDto->setIp($_SERVER["REMOTE_ADDR"]);
        $userSessionDto->setHost(gethostbyaddr($_SERVER['REMOTE_ADDR']));

        //set user location information
        /* if (function_exists('geoip_record_by_name')){
           $record = \geoip_record_by_name($_SERVER["REMOTE_ADDR"]);
           $userSessionDto->setCountry($record["country_name"]);
           // $userSessionDto->setCity($record["city"]);
         }*/
        $apiUserDto = ApiKeysMapper::getInstance()->getApiUserByKey($apiKey);
        $userSessionDto->setApiKeyId($apiUserDto->getId());
        $userSessionDto->setOs($platform);
        $userSessionDto->setModel($model);
        $userSessionDto->setLastLoginDate("NOW()");
        $httpReferer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : null;
        if ($httpReferer) {
            $userSessionDto->setReffer($httpReferer);
        }

        $userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : null;
        if ($userAgent) {
            $userSessionDto->setUserAgent($userAgent);
        }
        $userId = UserSessionsMapper::getInstance()->insertDto($userSessionDto);
        $userSessionDto = UserSessionsMapper::getInstance()->createDto();

        $params = ["userId" => -1, "sid" => $userId, "expireDate" => $expireTime,
            "userLevel" => $userLevel, "platform" => $platform, "ip" => $_SERVER["REMOTE_ADDR"],
            "apiKeyId" => $apiUserDto->getId(), "host" => $userSessionDto->getHost(), "uuid" => $uuid];
        $token = $this->createToken($params);
        $userSessionDto->setAccessToken($token);
        $userSessionDto->setId($userId);
        UserSessionsMapper::getInstance()->updateByPK($userSessionDto);
        return $token;
    }

    /**
     * update api user activity
     * @param int $authorizationUserId
     * @param array $tokenArr
     * @return string $hash
     * @return bool
     * @throws \ngs\exceptions\DebugException
     */
    public function updateActivity($authorizationUserId, $tokenArr = [], $tritonUUID = null)
    {
        $userSessionDto = UserSessionsMapper::getInstance()->createDto();
        $userSessionDto->setId($authorizationUserId);
        $userSessionDto->setLastActivityDate("NOW()");
        if ($tritonUUID) {
            $userSessionDto->setTritonUuid($tritonUUID);
        }
        $httpReferer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : null;
        if ($httpReferer) {
            $userSessionDto->setReffer($httpReferer);
        }
        if (count($tokenArr) > 0) {
            if (isset($tokenArr["token"])) {
                $userSessionDto->setAccessToken($tokenArr["token"]);
            }
        }
        UserSessionsMapper::getInstance()->updateByPK($userSessionDto);
        return true;
    }

    public function getApiKeyByAuthorizationUserId($authorizationUserId)
    {
        return UserSessionsMapper::getInstance()->getApiKeyByAuthorizationUserId($authorizationUserId);
    }

    /**
     * Checks admins hashcode.
     *
     * @param integer $id
     * @param object $type
     *
     * @return TRUE - if was successfully validated, and FALSE otherwise
     */
    public function validate($id, $type)
    {

        if ($sessionUserDto = UserSessionsMapper::getInstance()->getSessionUserByIdAndTokenVersion($id)) {

            if (!$sessionUserDto->getUserId()) {
                return null;
            }
            $userDto = $this->getUserById($sessionUserDto->getUserId());
            //todo change it
            $userDto->setUserType('admin');
            if (!$userDto) {
                return null;
            }
            if ($userDto->getUserType() == $type) {
                return $userDto;
            }
        }
        return null;
    }

    /**
     * Checks admins hashcode.
     *
     * @param int $id
     * @param string $token
     * @param string $tokenVersion
     *
     * @return boolean
     */
    public function validateSessionUser($id, $token)
    {
        if ($sessionUserDto = UserSessionsMapper::getInstance()->getSessionUserByIdAndTokenVersion($id)) {
            if ($sessionUserDto->getAccessToken() == $token) {
                return true;
            }
        }
        return false;
    }

    /**
     * return user following users dtos
     *
     * @param integer $userId
     * @param integer $offset
     * @param integer $limit
     *
     * @return array $dtos
     */

    public function getUserFollowings($userId, $offset = 0, $limit = 12)
    {
        return UserMapper::getInstance()->getUserFollowings($userId, $offset, $limit);
    }

    /**
     * return user following users count
     *
     * @param integer $userId
     *
     * @return int count
     */

    public function getUserFollowingsCount($userId)
    {
        return UserMapper::getInstance()->getUserFollowingsCount($userId);
    }


    /**
     * return user followers users dtos
     *
     * @param integer $userId
     * @param integer $offset
     * @param integer $limit
     *
     * @return array $dtos
     */
    public function getUserFollowers($userId, $offset = 0, $limit = 12)
    {
        return UserMapper::getInstance()->getUserFollowers($userId, $offset, $limit);
    }

    /**
     * rreturn user followers users count
     *
     * @param integer $userId
     *
     * @return int count
     */

    public function getUserFollowersCount($userId)
    {
        return UserMapper::getInstance()->getUserFollowersCount($userId);
    }


    public function followUser($userId, $followingId)
    {
        $userFollowersMapper = UserFollowersMapper::getInstance();
        if ($userFollowersMapper->getFollowingByUserAndFollowingId($userId, $followingId)) {
            throw new NgsErrorException("user already in your followers list");
        }
        $userFollowersDto = $userFollowersMapper->createDto();
        $userFollowersDto->setUserId($userId);
        $userFollowersDto->setFollowingId($followingId);
        return $userFollowersMapper->insertDto($userFollowersDto);
    }

    public function unfollowUser($userId, $followingId)
    {
        $userFollowersMapper = UserFollowersMapper::getInstance();
        $userFollowersDto = $userFollowersMapper->getFollowingByUserAndFollowingId($userId, $followingId);
        if (!$userFollowersDto) {
            throw new NgsErrorException("user not in your followers list");
        }
        return $userFollowersMapper->deleteByPK($userFollowersDto->getId());
    }

    /**
     * Returns user follower by user and follower id
     *
     * @param int $userId
     * @param int $followerId
     *
     * @return UserFollowersDto $dto
     */
    public function getFollowingByUserAndFollowingId($userId, $followerId)
    {
        return UserFollowersMapper::getInstance()->getFollowingByUserAndFollowingId($userId, $followerId);
    }

    /**
     * Returns user followers count by user id
     *
     * @param int $userId
     *
     * @return integer count
     */

    public function getFollowerCountByUserAndFollowerId($userId)
    {
        return UserFollowersMapper::getInstance()->getFollowerCountByUserAndFollowerId($userId);
    }

    /**
     * Returns user following count by user id
     *
     * @param int $userId
     *
     * @return integer count
     */

    public function getFollowingCountByUserAndFollowerId($userId)
    {
        return UserFollowersMapper::getInstance()->getFollowingCountByUserAndFollowerId($userId);
    }


    public function createToken($params)
    {
        $key = Key\InMemory::plainText('ngs-admin-tools');
        $config = Configuration::forSymmetricSigner(new Sha256(), $key);

        $now = new \DateTimeImmutable();
        $token = $config->builder()->issuedBy('https://naghashyan.com')
            ->permittedFor('https://naghashyan.com')
            ->identifiedBy($params['sid'])
            ->issuedAt($now)
            ->canOnlyBeUsedAfter($now->modify('+1 minute'))
            ->expiresAt($now->modify('+3600 hour'))
            ->withClaim('uid', $params['userId'])
            ->withClaim('sid', $params['sid'])
            ->withClaim('level', $params['userLevel'])
            ->withClaim('os', $params['platform'])
            ->withClaim('ip', $params['ip'])
            ->withClaim('apiKeyId', $params['apiKeyId'])
            ->withClaim('host', $params['host'])
            ->withClaim('uuid', $params['uuid'])
            ->getToken($config->signer(), $config->signingKey());
        return $token->toString();
    }

    public function deleteNotActiveSessions()
    {
        UserSessionsMapper::getInstance()->deleteNotActiveSessions();
    }

    /**
     * returns relative users of logged in user
     *
     * @param $userId
     * @param int $offset
     * @param int $limit
     * @return array|null
     */
    public function getRelativeUsers($userId, $offset = 0, $limit = 1000)
    {
        return UserMapper::getInstance()->getRelativeUsers($userId, $offset, $limit);
    }

    public function setUserPasswordVerificationHash($userId)
    {
        $hash = md5(time());
        UserMapper::getInstance()->setUserPasswordVerificationHash($userId, $hash);
        return $hash;
    }


    public function removeUserPasswordVerificationHash($userId)
    {
        return UserMapper::getInstance()->setUserPasswordVerificationHash($userId, null);
    }

    /**
     * @param int $sessionUserId
     * @param string $advertisingId
     * @return int
     * @throws \ngs\exceptions\DebugException
     */
    public function setAdvertisingId(int $sessionUserId, string $advertisingId)
    {
        $sessionUserDto = UserSessionsMapper::getInstance()->createDto();
        $sessionUserDto->setId($sessionUserId);
        $sessionUserDto->setTritonUuid($advertisingId);
        return UserSessionsMapper::getInstance()->updateByPK($sessionUserDto);
    }
}
