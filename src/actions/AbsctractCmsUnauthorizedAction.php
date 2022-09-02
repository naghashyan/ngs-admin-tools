<?php

/**
 * General parent cms actions for unauthorized requests.
 *
 *
 * @author Mikael Mkrtchyan
 * @site https://naghashyan.com
 * @mail mikael.mkrtchyan@naghashyan.com
 * @year 2022
 * @package ngs.cms.actions
 * @version 9.0.0
 *
 */

namespace ngs\AdminTools\actions;

use Monolog\Logger;
use ngs\AdminTools\util\LoggerFactory;
use ngs\request\AbstractAction;
use ngs\exceptions\NgsErrorException;

abstract class AbsctractCmsUnauthorizedAction extends AbsctractCmsAction
{

    public final function service() {
        $this->validateIpForAction();
        $this->executeAction();
    }


    /**
     * do action logic here
     *
     * @return mixed
     */
    protected abstract function executeAction();


    /**
     * @return array
     */
    public function getRequestAllowedGroups() {
        return [];
    }


    /**
     * validate if request is done from allowed IP address
     *
     * @throws NgsErrorException
     */
    private function validateIpForAction() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        if(!NGS()->getDefinedValue('ALLOWED_IPS_FOR_ACTION') || !in_array($ip, NGS()->getDefinedValue('ALLOWED_IPS_FOR_ACTION'))) {
            throw new NgsErrorException('ip ' . $ip . ' not allowed to do this action', -1);
        }
    }
}
