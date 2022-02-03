<?php
/**
 *
 * AbstractDto parent class for all
 * ngs dtos
 *
 * @author Levon Naghashyan <levon@naghashyan.com>
 * @site https://naghashyan.com
 * @year 2009-2020
 * @package ngs.AdminTools.dal.dto
 * @version 4.0.0
 *
 * This file is part of the NGS package.
 *
 * @copyright Naghashyan Solutions LLC
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace ngs\AdminTools\dal\dto;


use ngs\AdminTools\managers\NgsSecurityManager;
use ngs\dal\dto\AbstractDto;
use ngs\security\users\AbstractNgsUser;

abstract class AbstractSecureDto extends AbstractDto {

    const NO_ACCESS = 0;
    const READ_ACCESS = 1;
    const WRITE_ACCESS = 2;
    const READ_WRITE_ACCESS = 3;


    public function getMapArray(): ?array {
        return null;
    }

    /**
     * there are some fields that should not exist in mapArray, but need to have in security manager for creating security table
     * @return array
     */
    public function getAdditionalSecurityFields(): array {
        return [];
    }

    public abstract function getTableName(): string;


    /**
     * @param $fieldName
     * @param AbstractNgsUser|null $user
     * @return bool
     * @throws \ngs\exceptions\DebugException
     */
    public function hasReadAccess($fieldName, AbstractNgsUser $user = null) : bool {
        $access = $this->getAccess($fieldName);
        if(!$access) {
            return true;
        }
        $readAccess = $access['read'];
        $sessionManager = NGS()->getSessionManager();
        if(!$user) {
            $user = $sessionManager->getUser();
        }
        $userLevel = $user->getLevel();
        return $sessionManager->userIsAllowed($readAccess, $userLevel);
    }


    /**
     * @param $fieldName
     * @param AbstractNgsUser|null $user
     * @return bool
     * @throws \ngs\exceptions\DebugException
     */
    public function hasWriteAccess($fieldName, AbstractNgsUser $user = null) : bool {
        $access = $this->getAccess($fieldName);
        if(!$access) {
            return true;
        }
        $readAccess = $access['write'];
        $sessionManager = NGS()->getSessionManager();
        if(!$user) {
            $user = $sessionManager->getUser();
        }
        $userLevel = $user->getLevel();
        
        return $sessionManager->userIsAllowed($readAccess, $userLevel);
    }

    /**
     * @param $fieldName
     *
     * @return array
     */
    public function getAccess($fieldName) :?array{
        $securityManager = NgsSecurityManager::getInstance();

        return $securityManager->getFieldAccess($this, $fieldName);
    }
}
