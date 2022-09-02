<?php
/**
 * AuthorizationException triggers when incorrect username password used
 *
 * @author Mikael Mkrtchyan
 * @site http://naghashyan.com
 * @mail mikael.mkrtchyan@naghashyan.com
 * @year 2022
 * @package ngs.AdminTools.exceptions.api
 * @version 1.0.0
 *
 */

namespace ngs\AdminTools\exceptions\api;



class AuthorizationException extends \Exception
{
    /**
     * InvalidActionException constructor.
     *
     * @param string $msg
     * @param int $code
     * @throws \ngs\exceptions\DebugException
     */
    public function __construct($msg = 'invalid credentials')
    {
        parent::__construct($msg, 0);
    }
}
