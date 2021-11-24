<?php
/**
 * UserDto mapper class
 * setter and getter generator
 * for ilyov_users table
 * provides constants for user types and status
 *
 * @author Mikael Mkrtchyn
 * @site http://naghashyan.com
 * @mail mikael.mkrtchyan@naghashyan.com
 * @year 2021
 * @package ngs.NgsAdminTools.dal.dto
 * @version 1.0
 *
 */

namespace ngs\NgsAdminTools\dal\dto;

use ngs\dal\dto\AbstractDto;


class UserDto extends AbstractDto
{
    protected $statusesArr = ['active' => true, 'pending' => true, 'disable' => true];
    // Map of DB value to Field value
    protected $mapArray = array('id' => 'id', 'user_name' => 'userName', 'password' => 'password',
        'email' => 'email', 'display_name' => 'displayName', 'first_name' => 'firstName', 'profile_image_id' => 'profileImageId',
        'gendre' => 'gendre', 'birth_date' => 'birthDate', 'country_id' => 'countryId',
        'last_name' => 'lastName', 'user_level' => 'userLevel', 'user_type' => 'userType',
        'theme' => 'theme', 'status' => 'status', 'create_date' => 'createDate', 'fb_user_id' => 'fbUserId',
        'fb_last_sync' => 'fbLastSync', 'lost_pass' => 'lostPass', 'last_modified_date' => 'lastModifiedDate', 'password_verification_hash' => 'passwordVerificationHash');


    // returns map array
    public function getMapArray(): array
    {
        return $this->mapArray;
    }


    private static $ITEM_TYPE = 'user';

    private $id;
    private $userName;
    private $firstName;
    private $password;
    private $lastName;
    private $email;
    private $phone;
    private $userType;
    private $authSecret;
    private $accountStatus;
    private $lastLoginDate;
    private $addedDate;

    /**
     * creates associative array with all fields of DTO
     *
     * @return array
     */
    public function getJsonArray(): array {
        return [
            'id' => $this->getId(),
            'user_name' => $this->getUserName(),
            'first_name' => $this->getFirstName(),
            'last_name' => $this->getFirstName(),
            'email' => $this->getEmail(),
            'phone' => $this->getPhone(),
            'userType' => $this->getUserType(),
            'accountStatus' => $this->getAccountStatus(),
            'lastLoginDate' => $this->getLastLoginDate(),
            'added_date' => $this->getAddedDate()
        ];
    }

    /**
     * @return int
     */
    public function getId(): ?int {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getUserName(): ?string {
        return $this->userName;
    }

    /**
     * @param string $userName
     */
    public function setUserName(string $userName): void {
        $this->userName = $userName;
    }

    /**
     * @return string
     */
    public function getFirstName(): ?string {
        return $this->firstName;
    }

    /**
     * @param string $firstName
     */
    public function setFirstName(string $firstName): void {
        $this->firstName = $firstName;
    }

    /**
     * @return string
     */
    public function getPassword(): ?string {
        return $this->password;
    }

    /**
     * @param string $password
     * @param bool $doHash
     */
    public function setPassword(string $password, bool $doHash = false): void {
        if ($doHash === false){
            $this->password = $password;
            return;
        }
        $this->password = password_hash($password, PASSWORD_DEFAULT);
    }

    /**
     * @return string
     */
    public function getLastName(): ?string {
        return $this->lastName;
    }

    /**
     * @param string $lastName
     */
    public function setLastName(string $lastName): void {
        $this->lastName = $lastName;
    }

    /**
     * @return string
     */
    public function getEmail(): ?string {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail(string $email): void {
        $this->email = $email;
    }

    /**
     * @return string
     */
    public function getPhone(): ?string {
        return $this->phone;
    }

    /**
     * @param string $phone
     */
    public function setPhone(string $phone): void {
        $this->phone = $phone;
    }

    /**
     * @return string|null
     */
    public function getUserType(): ?string {
        return $this->userType;
    }

    /**
     * @param string|null $userType
     */
    public function setUserType(?string $userType): void {
        $this->userType = $userType;
    }

    /**
     * @return string
     */
    public function getAccountStatus(): ?string {
        return $this->accountStatus;
    }

    /**
     * @param string $accountStatus
     */
    public function setAccountStatus(string $accountStatus): void {
        $this->accountStatus = $accountStatus;
    }

    /**
     * @return string
     */
    public function getLastLoginDate(): ?string {
        return $this->lastLoginDate;
    }

    /**
     * @param string $lastLoginDate
     */
    public function setLastLoginDate(string $lastLoginDate): void {
        $this->lastLoginDate = $lastLoginDate;
    }

    /**
     * @return string
     */
    public function getAuthSecret(): ?string {
        return $this->authSecret;
    }

    /**
     * @param string $authSecret
     */
    public function setAuthSecret(?string $authSecret): void {
        $this->authSecret = $authSecret;
    }


    /**
     * @return string
     */
    public function getAddedDate(): ?string {
        return $this->addedDate;
    }

    /**
     * @param string $addedDate
     */
    public function setAddedDate(?string $addedDate): void {
        $this->addedDate = $addedDate;
    }

}