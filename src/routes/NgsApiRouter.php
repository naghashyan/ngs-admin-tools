<?php
/**
 * default ngs routing class
 * this class by default used from dispacher
 * for matching url with routes
 *
 * @author Mikael Mkrtchyan
 * @site https://naghashyan.com
 * @year 2021
 * @package ngs.NgsAdminTools.routes
 * @version 1.0
 *
 */

namespace ngs\NgsAdminTools\routes;

use ngs\exceptions\DebugException;
use ngs\exceptions\NgsErrorException;
use ngs\exceptions\NotFoundException;
use ngs\routes\NgsRoutes;

class NgsApiRouter extends NgsRoutes
{

    /**
     * read from file json routes
     * and set in private property for cache
     *
     * @return Object Array
     */
    protected function getRouteConfig()
    {
        if ($this->routes == null) {
            $routesDir = NGS()->getConfigDir() . '/routes';
            if (is_dir($routesDir)) {
                $routeFiles = scandir($routesDir);
                $allApiRoutes = [];
                foreach($routeFiles as $routeFile) {
                    if($routeFile == "." || $routeFile === "..") {
                        continue;
                    }

                    $fileNameParts = explode(".", $routeFile);
                    if(count($fileNameParts) !== 2) {
                        continue;
                    }

                    $extension = strtolower($fileNameParts[count($fileNameParts) - 1]);
                    $name = strtolower($fileNameParts[0]);
                    if($extension !== 'json') {
                        continue;
                    }

                    $fileContent = json_decode(file_get_contents($routesDir . '/' . $routeFile), true);
                    $allApiRoutes[$name] = $fileContent;
                }
                $this->routes = $allApiRoutes;
            }
        }
        return $this->routes;
    }

    /**
     * @throws DebugException
     */
    protected function onNoMatchedRoutes() {
        $noMatchedRouteError = new NgsErrorException('no matched route found', -1);
        $noMatchedRouteError->setHttpCode(404);
        throw $noMatchedRouteError;
    }

    /**
     * this method returd file path and namsepace form action
     * @static
     * @access
     * @return String $namespace
     */
    public function getLoadORActionByAction($action) {
        if (!isset($action)){
            return false;
        }
        $pathArr = explode('.', $action);
        $action = array_splice($pathArr, count($pathArr) - 1);
        $action = $action[0];
        $module = array_splice($pathArr, 0, 1);
        $module = $module[0];
        $actionType = '';
        foreach ($pathArr as $i => $v){
            switch ($v){
                case NGS()->getActionPackage() :
                    $actionType = 'api_action';
                    $classPrefix = 'Action';
                    break;
                case NGS()->getLoadsPackage() :
                    $actionType = 'api_load';
                    $classPrefix = 'Load';
                    break;
            }
            if ($actionType != ''){
                break;
            }
        }
        if (strrpos($action, 'do_') !== false){
            $action = str_replace('do_', '', $action);
        }
        $action = preg_replace_callback('/_(\w)/', function ($m) {
                return strtoupper($m[1]);
            }, ucfirst($action)) . $classPrefix;
        return array('action' => $module . '\\' . implode('\\', $pathArr) . '\\' . $action, 'type' => $actionType);
    }


    /**
     * returns matched route data
     *
     * @param string $action
     * @param array $route
     * @return array
     */
    protected function getMatchedRouteData(string $action, array $route)
    {
        return [
            'action' => $action,
            'args' => $route['args'],
            'matched' => true,
            'request_params' => isset($route['request_params']) ? $route['request_params'] : [],
            'response_params' => isset($route['response_params']) ? $route['response_params'] : [],
            'action_method' => isset($route['actionMethod']) ? $route['actionMethod'] : "service"
        ];
    }
}