<?php

/**
 * General parent cms actions.
 *
 *
 * @author Levon Naghashyan
 * @site https://naghashyan.com
 * @mail levon@naghashyan.com
 * @year 2019
 * @package ngs.cms.actions
 * @version 9.0.0
 *
 */

namespace ngs\AdminTools\actions;

use Monolog\Logger;
use ngs\AdminTools\exceptions\NoAccessException;
use ngs\AdminTools\util\LoggerFactory;
use ngs\request\AbstractAction;
use ngs\exceptions\NgsErrorException;

abstract class AbsctractCmsAction extends AbstractAction
{

    private ?Logger $logger = null;

    public function __construct()
    {
        $this->logger = LoggerFactory::getLogger(get_class($this), get_class($this));
    }

    /**
     * @throws NoAccessException
     * @throws \ngs\exceptions\DebugException
     */
    public function onNoAccess():void {
        throw new \Exception('no access');
    }


    protected function addPagingParameters()
    {
        $result = [];
        $page = $this->args()->page ? $this->args()->page : 1;
        $result['page'] = $page;
        if ($this->args()->limit) {
            $result['limit'] = $this->args()->limit;
        }
        if ($this->args()->search_key) {
            $result['search_key'] = $this->args()->search_key;
        }
        if ($this->args()->sorting) {
            $result['sorting'] = $this->args()->sorting;
        }
        if ($this->args()->ordering) {
            $result['ordering'] = $this->args()->ordering;
        }
        if ($this->args()->parentId) {
            $result['parentId'] = $this->args()->parentId;
        }
        $this->addParam('afterActionParams', $result);
    }
    

    /**
     * @return mixed|null
     * @throws NgsErrorException
     */
    public function getRequestGroup()
    {
        if (!NGS()->get("REQUEST_GROUP") === null) {
            throw new NgsErrorException("please set in constats REQUEST_GROUP");
        }
        return NGS()->get("REQUEST_GROUP");
    }


    /**
     * @param $params
     */
    public function loggerActionStart($params)
    {

    }


    /**
     * @param null $dto
     */
    public function loggerActionEnd($dto = null)
    {

    }

    /**
     * @return Logger|null
     */
    public function getLogger(): Logger
    {
        return $this->logger;
    }


    /**
     * @return array
     */
    public function getRequestAllowedGroups()
    {
        if (method_exists($this, 'getManager')) {
            $manager = $this->getManager();
            $mapper = $manager->getMapper();
            $dto = $mapper->createDto();
            $accessInfo = $dto->getAccess('id');
            if (!$accessInfo) {
                return [];
            }
            return $accessInfo['write'];
        }
        $adminGroup = NGS()->getSessionManager()->getUserGroupByName('admin');
        if ($adminGroup) {
            return ["allowed" => [$adminGroup->getId()]];
        }
        return [];
    }


}
