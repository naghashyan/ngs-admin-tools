<?php

/**
 * FilterManager manager class
 * used to handle functional related with users saved filters
 *
 * @author Mikael Mkrtchyan
 * @site http://naghashyan.com
 * @mail mikael.mkrtchyan@naghashyan.com
 * @year 2020
 * @package ngs.AdminTools.managers
 * @version 1.0
 *
 */

namespace ngs\AdminTools\managers;

use ngs\AbstractManager;
use ngs\AdminTools\dal\mappers\FilterMapper;

class FilterManager extends AbstractManager
{

    /**
     * @var FilterManager instance of class
     */
    private static $instance = null;


    /**
     * Returns an singleton instance of this class
     *
     * @return FilterManager
     */
    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new FilterManager();
        }
        return self::$instance;
    }




    /**
     * get user all saved filters for given type
     *
     * @param $userId
     * @param $itemType
     *
     * @return array|\ngs\dal\dto\AbstractDto[]|null
     *
     * @throws \Exception
     */
    public function getUserSavedFilters($userId, $itemType) {
        $mapper = FilterMapper::getInstance();
        $filters = $mapper->getUserSavedFiltersByType($userId, $itemType);
        return $filters;
    }


    /**
     * get preselected filter for given entity
     *
     * @param $userId
     * @param $itemType
     *
     * @return array|\ngs\dal\dto\AbstractDto|null
     *
     * @throws \Exception
     */
    public function getEntityPreselectedFilter($userId, $itemType) {
        $mapper = FilterMapper::getInstance();
        $filter = $mapper->getEntityPreselectedFilter($userId, $itemType);
        return $filter;
    }


    /**
     * get user all saved filters for given type
     *
     * @param $userId
     * @param $itemType
     * @param $name
     *
     * @return AbstractDto|null
     *
     * @throws \Exception
     */
    public function getUserSavedFilterByItemTypeAndName($userId, $itemType, $name) {
        $mapper = FilterMapper::getInstance();
        $filter = $mapper->getUserSavedFilterByItemTypeAndName($userId, $itemType, $name);
        return $filter;
    }


    /**
     * save filter for given user
     *
     * @param $userId
     * @param $itemType
     * @param $name
     * @param $filter
     *
     * @return int|null
     *
     * @throws \ngs\exceptions\DebugException
     */
    public function saveUserFilter($userId, $itemType, $name, $filter) {
        $mapper = FilterMapper::getInstance();
        $filterDto = $mapper->createDto();
        $filterDto->setItemType($itemType);
        $filterDto->setName($name);
        $filterDto->setUserId($userId);
        $filterDto->setFilter($filter);

        $id = $mapper->insertDto($filterDto);

        return $id;
    }


    /**
     * delete user saved filter
     *
     * @param $userId
     * @param $filterId
     *
     * @return bool
     *
     * @throws \ngs\exceptions\DebugException
     */
    public function deleteUserSavedFilter($userId, $filterId) {
        $mapper = FilterMapper::getInstance();
        $filter = $mapper->getSavedFilterById($filterId);
        if(!$filter) {
            return false;
        }

        if($filter->getUserId() != $userId) {
            return false;
        }

        $result = $mapper->deleteByPK($filterId);

        return $result !== null;
    }


}