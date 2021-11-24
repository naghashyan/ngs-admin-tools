<?php
/**
 * Contains definitions for all participant roles in system.
 *
 * @author Levon Naghashyan
 * @site http://naghashyan.com
 * @mail levon@naghashyan.com
 * @year 2009-2014
 * @package security
 * @version 6.0
 *
 */

namespace ngs\NgsAdminTools\security;

use ngs\NgsAdminTools\exceptions\InvalidUserException;
use ngs\NgsAdminTools\security\users\AdminUser;
use ngs\NgsAdminTools\security\users\ApiUser;
use ngs\NgsAdminTools\security\users\GuestUser;

class UserGroups
{
    public static $ADMIN = 0;
    public static $API = 1;
    public static $GUEST = 2;

    /**
     * @throws InvalidUserException
     */
    public static function getUserByLevel($level)
    {

        switch ($level) {
            case self::$ADMIN:
                return new AdminUser();
            case self::$API:
                return new ApiUser();
            case self::$GUEST:
                return new GuestUser();
            default :
                throw new InvalidUserException("user not found");
        }
    }

    /**
     * @throws InvalidUserException
     */
    public static function getUserByType($ut)
    {
        switch ($ut) {
            case "user" :
                return new GuestUser();
            case self::$API:
                return new AdminUser();
            case "api" :
                return new ApiUser();
            case UserGroups::$GUEST :
                return new GuestUser();
                break;
            default :
                throw new InvalidUserException("user not found");
                break;
        }
    }

    public static function getUserLevelByName($name)
    {
        switch ($name) {
            case "user" :
                return self::$GUEST;
            case "admin" :
                return self::$ADMIN;
            case "api" :
                return self::$API;
            default :
                return null;
        }
    }

    /**
     * @param $level
     * @return string
     * @throws InvalidUserException
     */
    public static function getNameByUserLevel($level)
    {
        switch ($level) {
            case self::$GUEST :
                return "user";
            case self::$ADMIN :
                return "admin";
            case self::$API :
                return "api";
            default :
                throw new InvalidUserException("user not found");
        }
    }
}