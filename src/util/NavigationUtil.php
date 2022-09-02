<?php

namespace ngs\AdminTools\util;



class NavigationUtil
{


    /**
     * get full link by uri
     * 
     * @param string $uri
     * @return string
     */
    public static function getFullLink(string $uri){
        $protocol = "http://";
        if (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1) ||
            isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
            $protocol = 'https://';
        }
        $myHost = NGS()->get('MY_HOST');
        $myHost = str_replace("https://", "", $myHost);
        $myHost = str_replace("http://", "", $myHost);
        $uri = trim($uri, "/");

        return $protocol . $myHost . "/" . $uri;
    }

}

