<?php
/**
 * LogManager class provides all functions for creating,
 * and working with logs.
 *
 * @author Mikael Mkrtchyan
 * @site http://naghashyan.com
 * @mail mikael.mkrtchyan@naghashyan.com
 * @year 2021
 * @package ngs.AdminTools.managers
 * @version 1.0
 */

namespace ngs\AdminTools\managers;

use ngs\AdminTools\dal\mappers\LogMapper;
use ngs\AdminTools\util\LoggerFactory;


class LogManager extends AbstractCmsManager {

    /**
     * @var $instance
     */
    public static $instance;

    /**
     * Returns an singleton instance of this class
     *
     * @return LogManager
     */
    public static function getInstance() {
        if (self::$instance == null){
            self::$instance = new LogManager();
        }
        return self::$instance;
    }

    /**
     * @return \ngs\AdminTools\dal\mappers\AbstractCmsMapper|LogMapper
     */
    public function getMapper() {
        return LogMapper::getInstance();
    }

    public function getDeleteAction(): string
    {
        return "";
    }

    public function getMainLoad(): string
    {
        return "";
    }

    public function getListLoad(): string
    {
        return "";
    }

    public function getEditLoad(): string
    {
        return "";
    }


    /**
     * creates record in DB with action data
     * @param int $userId
     * @param string $action
     * @param string $data
     * @param string $tableName
     * @param bool $success
     * @param int $itemId
     *
     */
    public function addLog(int $userId, string $action, string $data, string $tableName, bool $success, int $itemId = null, string $errorText = null) {
        $mapper = $this->getMapper();
        $dto = $mapper->createDto();
        $dto->setUserId($userId);
        $dto->setAction($action);
        $dto->setData($data);
        $dto->setItemId($itemId);
        $dto->setItemTableName($tableName);
        $dto->setSuccess($success ? 1 : 0);
        $dto->setErrorText($success ? '' : $errorText);
        try {
            $mapper->insertDto($dto);
        }
        catch(\Exception $exp) {
            $logger = LoggerFactory::getLogger(get_class($this), get_class($this));
            $logger->error('save log for user failed', ['userId' => $userId, 'action' => $action, 'data' => $data, 'table' =>$tableName, 'itemId' => $itemId, 'success' => $success]);
        }


    }
}