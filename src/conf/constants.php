<?php
/**
 * default constants
 * in this file should be store all constants
 *
 * @author Levon Naghashyan
 * @site http://naghashyan.com
 * @mail levon@naghashyan.com
 * @year 2015
 * @version 2.0.0
 * @copyright Naghashyan Solutions LLC
 *
 */


//defining project version
NGS()->define('VERSION', '1.0.0');
//defining ngs namespace
NGS()->define('JS_FRAMEWORK_ENABLE', true);
NGS()->define('JS_BUILDER', 'ngs\util\JsBuilderV2');
NGS()->define('TINYMCE_KEY', '');
//define error show status
if (NGS()->getDefinedValue('ENVIRONMENT') != 'production'){
  error_reporting(E_ALL);
  ini_set('display_errors', '1');
}

/*
|--------------------------------------------------------------------------
| DEFINNING DEFAULTS PACKAGES DIRS
|--------------------------------------------------------------------------
*/
NGS()->define('SESSION_MANAGER', 'ngs\NgsAdminTools\managers\SessionManager');
NGS()->define('REQUEST_GROUP', \ngs\NgsAdminTools\security\RequestGroups::$adminsRequest);
NGS()->define('TEMPLATE_ENGINE', 'ngs\NgsAdminTools\templater\NgsCmsTemplater');