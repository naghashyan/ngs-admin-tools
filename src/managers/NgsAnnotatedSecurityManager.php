<?php
/**
 * NgsAnnotatedSecurityManager
 *
 * @author Mikael Mkrtchyan
 * @site http://naghashyan.com
 * @mail mikael.mkrtchyan@naghashyan.com
 * @year 2020
 * @package ngs.AdminTools.managers
 * @version 1.0.0
 *
 */

namespace ngs\AdminTools\managers;

use ngs\AdminTools\dal\dto\AbstractSecureDto;


class NgsAnnotatedSecurityManager extends NgsSecurityManager
{
    public function getFieldAccess(AbstractSecureDto $dto, string $fieldName) :?array {

    }
}
