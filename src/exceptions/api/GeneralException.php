<?php
/**
 * GeneralException triggers when not handeled issue accured
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



class GeneralException extends \Exception
{
    /**
     * GeneralException constructor.
     *
     * @param string $msg
     * @param int $code
     * @throws \ngs\exceptions\DebugException
     */
    public function __construct($msg = 'general error')
    {
        parent::__construct($msg, 0);
    }
}
