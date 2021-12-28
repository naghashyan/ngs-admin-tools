<?php
/**
 * NgsSecurityManager abstract manager class
 *
 * @author Mikael Mkrtchyan
 * @site http://naghashyan.com
 * @mail mikael.mkrtchyan@naghashyan.com
 * @year 2020
 * @package ngs.NgsAdminTools.managers
 * @version 1.0.0
 *
 */

namespace ngs\NgsAdminTools\managers;

use ngs\AbstractManager;
use ngs\NgsAdminTools\dal\dto\AbstractSecureDto;


abstract class NgsSecurityManager extends AbstractManager
{

    private static $securityMode = "DB";

    /**
     * instance of security manager
     */
    private static $instance = null;

    /**
     * Returns an singleton instance of this class
     *
     * @return NgsSecurityManager
     */
    public static function getInstance(): NgsSecurityManager {
        if (self::$instance == null){
            $managerName = 'ngs\NgsAdminTools\managers\Ngs' . self::$securityMode . 'SecurityManager';
            self::$instance = new $managerName();
        }
        return self::$instance;
    }


    public abstract function getFieldAccess(AbstractSecureDto $dto, string $fieldName) :?array;


}
