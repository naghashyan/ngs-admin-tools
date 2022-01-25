<?php
/**
 * @author Mikael Mkrtchyan
 * @site http://naghashyan.com
 * @mail mikael.mkrtchyan@naghashyan.com
 * @year 2021
 */

namespace ngs\AdminTools\exceptions;

class InvalidUserException extends \ngs\exceptions\InvalidUserException
{
    /**
     * InvalidUserException constructor.
     * @param string $msg
     * @param int $code
     * @throws \ngs\exceptions\DebugException
     */
    public function __construct($msg = 'access denied', $code = -10)
    {
        if (NGS()->get('IM_MODE') === 'api') {
            http_response_code(401);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['code' => -10, 'msg' => $msg]);
            exit;
        }
        if (isset($_COOKIE['_im_token'])) {
            NGS()->getSessionManager()->deleteUser();
        }
        if (NGS()->get('IM_MODE') === 'api') {
            NGS()->define('display_json', true);
        }
        parent::__construct($msg, $code);
    }

}
