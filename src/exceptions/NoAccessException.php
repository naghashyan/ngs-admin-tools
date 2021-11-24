<?php
/**
 * @author Levon Naghashyan <levon@naghashyan.com>
 * @site https://naghashyan.com
 * @mail levon@naghashyan.com
 * @year 2016-2019
 */

namespace ngs\NgsAdminTools\exceptions;

class NoAccessException extends \ngs\exceptions\NoAccessException
{
    /**
     * NoAccessException constructor.
     * @param string $msg
     * @param int $code
     * @throws \ngs\exceptions\DebugException
     */
    public function __construct($msg = 'access denied', $code = -10)
    {
        if (isset($_COOKIE['_im_token'])) {
            NGS()->getSessionManager()->deleteUser();
        }
        parent::__construct($msg, $code);
    }


}
