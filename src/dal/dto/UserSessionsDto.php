<?php
/**
 * UserSessionsDto Dto class
 * setter and getter generator
 * for ilyov_user_sessions table
 * provides constants for user types and status
 *
 * @author Levon Naghashyan
 * @site https://naghashyan.com
 * @mail levon@naghashyan.com
 * @year 2010-2020
 * @package ngs.NgsAdminTools.dal.dto
 * @version 9.0.0
 *
 */

namespace ngs\NgsAdminTools\dal\dto;

use ngs\dal\dto\AbstractDto;

/**
 * @OA\Schema(
 *     title="UserSession",
 * )
 *
 */
class UserSessionsDto extends AbstractDto
{

    /**
     * @OA\Property(
     *   property="id"
     * )
     * @ORM id
     * @var int|null
     */
    protected ?int $id;
    /**
     * @OA\Property(
     *   property="user_id"
     * )
     * @ORM user_id
     * @var int|null
     */
    protected ?int $userId;

    /**
     * @OA\Property(
     *   property="hashcode"
     * )
     * @ORM hashcode
     * @var string|null
     */
    protected ?string $hashcode;
    /**
     * @OA\Property(
     *   property="api_key_id"
     * )
     * @ORM api_key_id
     * @var int|null
     */
    protected ?int $apiKeyId;

    /**
     * @OA\Property(
     *   property="notification_token"
     * )
     * @ORM notification_token
     * @var string|null
     */
    protected ?string $notificationToken;

    /**
     * @OA\Property(
     *   property="access_token"
     * )
     * @ORM access_token
     * @var string|null
     */
    protected ?string $accessToken;

    /**
     * @OA\Property(
     *   property="token_version"
     * )
     * @ORM token_version
     * @var int|null
     */
    protected ?int $tokenVersion;

    /**
     * @OA\Property(
     *   property="triton_uuid"
     * )
     * @ORM triton_uuid
     * @var string|null
     */
    protected ?string $tritonUuid;

    /**
     * @OA\Property(
     *   property="uuid"
     * )
     * @ORM uuid
     * @var string|null
     */
    protected ?string $uuid;

    /**
     * @OA\Property(
     *   property="ip"
     * )
     * @ORM ip
     * @var string|null
     */
    protected ?string $ip;

    /**
     * @OA\Property(
     *   property="host"
     * )
     * O@RM host
     * @var string|null
     */
    protected ?string $host = null;

    /**
     * @OA\Property(
     *   property="country"
     * )
     * @ORM country
     * @var string|null
     */
    protected ?string $country;

    /**
     * @OA\Property(
     *   property="city"
     * )
     * @ORM city
     * @var string|null
     */
    protected ?string $city;

    /**
     * @OA\Property(
     *   property="model"
     * )
     * @ORM model
     * @var string|null
     */
    protected ?string $model;

    /**
     * @OA\Property(
     *   property="version"
     * )
     * @ORM version
     * @var string|null
     */
    protected ?string $version;

    /**
     * @OA\Property(
     *   property="os"
     * )
     * @ORM os
     * @var string|null
     */
    protected ?string $os;

    /**
     * @OA\Property(
     *   property="platform"
     * )
     * @ORM platform
     * @var string|null
     */
    protected ?string $platform;

    /**
     * @OA\Property(
     *   property="reffer"
     * )
     * @ORM reffer
     * @var string|null
     */
    protected ?string $reffer;

    /**
     * @OA\Property(
     *   property="user_agent"
     * )
     * @ORM user_agent
     * @var string|null
     */
    protected ?string $userAgent;

    /**
     * @OA\Property(
     *   property="is_cookie_enabled"
     * )
     * @ORM is_cookie_enabled
     * @var string|null
     */
    protected ?string $isCookieEnabled;

    /**
     * @OA\Property(
     *   property="last_activity_date"
     * )
     * @ORM last_activity_date
     * @var string|null
     */
    protected ?string $lastActivityDate;

    /**
     * @OA\Property(
     *   property="last_logout_date"
     * )
     * @ORM last_logout_date
     * @var string|null
     */
    protected ?string $lastLogoutDate;

    /**
     * @OA\Property(
     *   property="create_date"
     * )
     * @ORM create_date
     * @var string|null
     */
    protected ?string $createDate;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     */
    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return int|null
     */
    public function getUserId(): ?int
    {
        return $this->userId;
    }

    /**
     * @param int|null $userId
     */
    public function setUserId(?int $userId): void
    {
        $this->userId = $userId;
    }

    /**
     * @return string|null
     */
    public function getHashcode(): ?string
    {
        return $this->hashcode;
    }

    /**
     * @param string|null $hashcode
     */
    public function setHashcode(?string $hashcode): void
    {
        $this->hashcode = $hashcode;
    }

    /**
     * @return int|null
     */
    public function getApiKeyId(): ?int
    {
        return $this->apiKeyId;
    }

    /**
     * @param int|null $apiKeyId
     */
    public function setApiKeyId(?int $apiKeyId): void
    {
        $this->apiKeyId = $apiKeyId;
    }

    /**
     * @return string|null
     */
    public function getNotificationToken(): ?string
    {
        return $this->notificationToken;
    }

    /**
     * @param string|null $notificationToken
     */
    public function setNotificationToken(?string $notificationToken): void
    {
        $this->notificationToken = $notificationToken;
    }

    /**
     * @return string|null
     */
    public function getAccessToken(): ?string
    {
        return $this->accessToken;
    }

    /**
     * @param string|null $accessToken
     */
    public function setAccessToken(?string $accessToken): void
    {
        $this->accessToken = $accessToken;
    }

    /**
     * @return int|null
     */
    public function getTokenVersion(): ?int
    {
        return $this->tokenVersion;
    }

    /**
     * @param int|null $tokenVersion
     */
    public function setTokenVersion(?int $tokenVersion): void
    {
        $this->tokenVersion = $tokenVersion;
    }

    /**
     * @return string|null
     */
    public function getTritonUuid(): ?string
    {
        return $this->tritonUuid;
    }

    /**
     * @param string|null $tritonUuid
     */
    public function setTritonUuid(?string $tritonUuid): void
    {
        $this->tritonUuid = $tritonUuid;
    }

    /**
     * @return string|null
     */
    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    /**
     * @param string|null $uuid
     */
    public function setUuid(?string $uuid): void
    {
        $this->uuid = $uuid;
    }

    /**
     * @return string|null
     */
    public function getIp(): ?string
    {
        return $this->ip;
    }

    /**
     * @param string|null $ip
     */
    public function setIp(?string $ip): void
    {
        $this->ip = $ip;
    }

    /**
     * @return string|null
     */
    public function getHost(): ?string
    {
        return $this->host;
    }

    /**
     * @param string|null $host
     */
    public function setHost(?string $host): void
    {
        $this->host = $host;
    }

    /**
     * @return string|null
     */
    public function getCountry(): ?string
    {
        return $this->country;
    }

    /**
     * @param string|null $country
     */
    public function setCountry(?string $country): void
    {
        $this->country = $country;
    }

    /**
     * @return string|null
     */
    public function getCity(): ?string
    {
        return $this->city;
    }

    /**
     * @param string|null $city
     */
    public function setCity(?string $city): void
    {
        $this->city = $city;
    }

    /**
     * @return string|null
     */
    public function getModel(): ?string
    {
        return $this->model;
    }

    /**
     * @param string|null $model
     */
    public function setModel(?string $model): void
    {
        $this->model = $model;
    }

    /**
     * @return string|null
     */
    public function getVersion(): ?string
    {
        return $this->version;
    }

    /**
     * @param string|null $version
     */
    public function setVersion(?string $version): void
    {
        $this->version = $version;
    }

    /**
     * @return string|null
     */
    public function getOs(): ?string
    {
        return $this->os;
    }

    /**
     * @param string|null $os
     */
    public function setOs(?string $os): void
    {
        $this->os = $os;
    }

    /**
     * @return string|null
     */
    public function getPlatform(): ?string
    {
        return $this->platform;
    }

    /**
     * @param string|null $platform
     */
    public function setPlatform(?string $platform): void
    {
        $this->platform = $platform;
    }

    /**
     * @return string|null
     */
    public function getReffer(): ?string
    {
        return $this->reffer;
    }

    /**
     * @param string|null $reffer
     */
    public function setReffer(?string $reffer): void
    {
        $this->reffer = $reffer;
    }

    /**
     * @return string|null
     */
    public function getUserAgent(): ?string
    {
        return $this->userAgent;
    }

    /**
     * @param string|null $userAgent
     */
    public function setUserAgent(?string $userAgent): void
    {
        $this->userAgent = $userAgent;
    }

    /**
     * @return string|null
     */
    public function getIsCookieEnabled(): ?string
    {
        return $this->isCookieEnabled;
    }

    /**
     * @param string|null $isCookieEnabled
     */
    public function setIsCookieEnabled(?string $isCookieEnabled): void
    {
        $this->isCookieEnabled = $isCookieEnabled;
    }

    /**
     * @return string|null
     */
    public function getLastActivityDate(): ?string
    {
        return $this->lastActivityDate;
    }

    /**
     * @param string|null $lastActivityDate
     */
    public function setLastActivityDate(?string $lastActivityDate): void
    {
        $this->lastActivityDate = $lastActivityDate;
    }

    /**
     * @return string|null
     */
    public function getLastLogoutDate(): ?string
    {
        return $this->lastLogoutDate;
    }

    /**
     * @param string|null $lastLogoutDate
     */
    public function setLastLogoutDate(?string $lastLogoutDate): void
    {
        $this->lastLogoutDate = $lastLogoutDate;
    }

    /**
     * @return string|null
     */
    public function getCreateDate(): ?string
    {
        return $this->createDate;
    }

    /**
     * @param string|null $createDate
     */
    public function setCreateDate(?string $createDate): void
    {
        $this->createDate = $createDate;
    }

}