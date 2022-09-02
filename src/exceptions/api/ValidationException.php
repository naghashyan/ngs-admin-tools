<?php
/**
 * ValidationException triggers when parameters are not correct
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



class ValidationException extends \Exception
{
    private array $params;
    /**
     * ValidationException constructor.
     *
     * @param string $msg
     * @param int $code
     * @throws \ngs\exceptions\DebugException
     */
    public function __construct($msg = 'validation exception', array $params = [])
    {
        $this->params = $params;
        parent::__construct($msg, 1);
    }

    /**
     * @return array
     */
    public function getParams() {
        return $this->params;
    }
}
