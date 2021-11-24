<?php
/**
 * NgsAnnotatedSecurityManager
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

use ngs\NgsAdminTools\dal\dto\AbstractSecureDto;


class NgsAnnotatedSecurityManager extends NgsSecurityManager
{
    public function getFieldAccess(AbstractSecureDto $dto, string $fieldName) :?array {

    }
}
