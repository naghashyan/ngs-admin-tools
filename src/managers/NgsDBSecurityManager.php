<?php
/**
 * NgsDBSecurityManager
 *
 * @author Mikael Mkrtchyan
 * @site http://naghashyan.com
 * @mail mikael.mkrtchyan@naghashyan.com
 * @year 2020
 * @package ngs.NgsAdminTools.managers
 * @version 1.0.0
 *
 */

namespace ngs\NgsAdminTools\managers;

use ngs\NgsAdminTools\dal\dto\AbstractSecureDto;
use ngs\NgsAdminTools\dal\mappers\NgsSecurityMapper;


class NgsDBSecurityManager extends NgsSecurityManager
{

    private $securityCache = [];

    /**
     * NgsDBSecurityManager constructor.
     */
    public function __construct()
    {
        if(!$this->dbIsInitialized()) {
            $this->initializeDB();
        }

        parent::__construct();
    }


    /**
     * get dto field access info for given user
     *
     * @param AbstractSecureDto $dto
     * @param string $fieldName
     *
     * @return array
     */
    public function getFieldAccess(AbstractSecureDto $dto, string $fieldName) :?array {
        $tableName = $dto->getTableName();

        $mapper = $this->getMapper();
        $result = [];
        if(isset($this->securityCache[$tableName . '_' . $fieldName])) {
            return $this->securityCache[$tableName . '_' . $fieldName];
        }
        try {
            $ngsSecurityDtos = $mapper->getFieldSecurityInfo($tableName, $fieldName);
            if(!$ngsSecurityDtos) {
                return null;
            }
            foreach($ngsSecurityDtos as $ngsSecurityDto) {
                $accessType = $ngsSecurityDto->getAccessType();
                if(!isset($result[$accessType])) {
                    $result[$accessType] = ['allowed' => [], 'not_allowed' => []];
                }

                if($ngsSecurityDto->getRuleType() === "in" && !in_array($ngsSecurityDto->getRuleValue(), $result[$accessType]['allowed'])) {
                    $result[$accessType]['allowed'][] = $ngsSecurityDto->getRuleValue();
                }

                if($ngsSecurityDto->getRuleType() === "not_in" && !in_array($ngsSecurityDto->getRuleValue(), $result[$accessType]['not_allowed'])) {
                    $result[$accessType]['not_allowed'][] = $ngsSecurityDto->getRuleValue();
                }
            }
            $this->securityCache[$tableName . '_' . $fieldName] = $result;
            return $result;
        }
        catch(\Exception $exp) {
            return null;
        }
    }


    /**
     * check if all necessary tables are created
     *
     * @return bool
     */
    private function dbIsInitialized() :bool {
        $mapper = $this->getMapper();
        return $mapper->dbIsInitialized();
    }


    /**
     * creates all necessary tables,
     * if something is wrong throws NgsSecurityException
     *
     */
    private function initializeDB() :void {
        $mapper = $this->getMapper();
        $mapper->initializeDB();
    }

    /**
     * returns instance of ngsSecurity mapper
     *
     * @return NgsSecurityMapper
     */
    private function getMapper() {
        return NgsSecurityMapper::getInstance();
    }
}
