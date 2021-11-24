<?php
/**
 * NgsSecurityException exception class, this exception class used in this lib to notify about exception cases
 *
 * @author Mikael Mkrtchyan
 * @site http://naghashyan.com
 * @mail mikael.mkrtchyan@naghashyan.com
 * @year 2020
 * @package ngs.NgsAdminTools.exceptions
 * @version 1.0.0
 *
 */

namespace ngs\NgsAdminTools\exceptions;

use Throwable;

class NgsValidationException extends \Exception
{
    private array $params;

    public function __construct($message = "", $code = 0, Throwable $previous = null, array $params = [])
    {
        $this->params = $params;
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return array
     */
    public function getParams() {
        return $this->params;
    }
}
