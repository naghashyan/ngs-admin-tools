<?php

/**
 *
 * @author Mikael Mkrtchyan
 * @site http://naghashyan.com
 * @mail mkael.mkrtchyan@naghashyan.com
 * @package ngs.AdminTools.templater
 * @version 1.0.0
 * @year 2020
 */

namespace ngs\AdminTools\templater;

class NgsCmsTemplater extends \ngs\templater\NgsTemplater {
    public function __construct() {
        parent::__construct();
    }

    public function getCustomHeader() {
        $jsString = '';
        if (NGS()->getSessionManager()->getCustomerId()){
            $jsString .= 'NGS.setUserId(' . NGS()->getSessionManager()->getCustomerId() . ');';
        }
        if (isset(NGS()->getConfig()->APIs->firebase)){
            $jsString .= 'NGS.setFirebaseConfig("' . base64_encode(json_encode(NGS()->getConfig()->APIs->firebase)) . '");';
        }
        $jsString .= 'NGS.setApiHost("' . NGS()->get('IN_API_HOST') . '");';
        if (NGS()->getSessionManager()->getUserToken()){
            $jsString .= 'NGS.setImToken("' . NGS()->getSessionManager()->getUserToken() . '");';
        }
        return $jsString;
    }


    public function getSmartyTemplater() {
        return new NgsSmartyTemplater();
    }
}
