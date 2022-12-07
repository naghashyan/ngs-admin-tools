<?php
/**
 *
 * BusinessMapper class is extended class from AbstractMysqlMapper.
 * It contatins all read and write functions which are working with ilyov_business table.
 *
 * @author Mikael Mkrtchyan
 * @site http://naghashyan.com
 * @mail mikael.mkrtchyan@naghashyan.com
 * @year 2018
 * @package admin.AdminTools.dal.mappers
 * @version 1.0
 *
 */

namespace ngs\AdminTools\dal\mappers;

use ngs\dal\dto\AbstractDto;
use ngs\dal\mappers\AbstractMysqlMapper;
use ngs\AdminTools\dal\dto\NgsSecurityDto;
use ngs\AdminTools\exceptions\NgsSecurityException;
use ReflectionProperty;

class NgsSecurityMapper extends AbstractMysqlMapper
{
    //! Private members.

    private static ?NgsSecurityMapper $instance = null;
    public string $tableName = 'ngs_security';

    /**
     * Returns an singleton instance of this class
     *
     * @return NgsSecurityMapper Object
     */
    public static function getInstance(): NgsSecurityMapper
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * @see AbstractMysqlMapper::getPKFieldName()
     */
    public function getPKFieldName(): string
    {
        return 'id';
    }

    /**
     * @see AbstractMysqlMapper::getTableName()
     */
    public function getTableName(): string
    {
        return $this->tableName;
    }


    public function createDto(): AbstractDto
    {
        return new NgsSecurityDto();
    }


    /**
     * @var string
     */
    private $GET_FIELD_DATA = 'SELECT * FROM %s WHERE dto_name = :tableName AND field_name = :fieldName';

    /**
     * @param string $tableName
     * @param string $fieldName
     * @return NgsSecurityDto[]|null
     * @throws \ngs\exceptions\DebugException
     */
    function getFieldSecurityInfo(string $tableName, string $fieldName) {
        $sqlQuery = sprintf($this->GET_FIELD_DATA, $this->getTableName());
        return $this->fetchRows($sqlQuery, ["tableName" =>$tableName, "fieldName" => $fieldName]);
    }


    /**
     * @var string
     */
    private $GET_TABLE_IF_EXISTS = 'SELECT 1 FROM %s LIMIT 1';

    /**
     * returns true if ngs_security table exists
     *
     * @return bool
     */
    public function dbIsInitialized() :bool
    {
        try {
            $sqlQuery = sprintf($this->GET_TABLE_IF_EXISTS, $this->getTableName());
            $result = $this->fetchRow($sqlQuery, []);
            return true;
        }
        catch(\Throwable $error) {
            return false;
        }
    }


    /**
     * @throws NgsSecurityException
     * @throws \ReflectionException
     */
    public function initializeDB() {
        $this->createTable();
    }

    /**
     * @throws NgsSecurityException
     * @throws \ReflectionException
     */
    private function createTable() {
        $dto = $this->createDto();
        $mapArray = $dto->getMapArray();
        $columns = "";

        foreach($mapArray as $dbField => $property) {
            $prop = new ReflectionProperty(get_class($dto), $property);
            $annotation = $prop->getDocComment();
            if(!$annotation) {
                continue;
            }
            $dbRelatedAnnotationMatches = [];
            preg_match('/DB\((.*)\)/', $annotation, $dbRelatedAnnotationMatches);
            if(!isset($dbRelatedAnnotationMatches[1])) {
                continue;
            }
            $dbAnnotation = $dbRelatedAnnotationMatches[1];

            $type = $this->getDbAnnotationPropertyValue($dbAnnotation, 'type');
            if(!$type) {
                throw new NgsSecurityException("type of property " . $property . " not specified");
            }
            $primary = $this->getDbAnnotationPropertyValue($dbAnnotation, 'primary');
            $primaryText = "";
            if($primary && $primary === "true") {
                $primaryText = "AUTO_INCREMENT PRIMARY KEY NOT NULL";
            }

            $length = "";
            if($type === "int" || $type === "varchar") {
                $length = $this->getDbAnnotationPropertyValue($dbAnnotation, 'length');
                $length = (int) $length;
                if(!$length) {
                    throw new NgsSecurityException("length of property " . $property . " is incorrect");
                }
                $length = "(" . $length . ")";
            }
            if($columns) {
                $columns .= ", ";
            }

            $columns .= $dbField . " " . $type . $length . " " . $primaryText;
        }

        $this->dbms->exec("CREATE TABLE IF NOT EXISTS " . $this->getTableName() . " (" . $columns .")");
    }


    /**
     * returns DB property value by name
     *
     * @param $annotation
     * @param $fieldName
     *
     * @return mixed|null
     */
    private function getDbAnnotationPropertyValue($annotation, $fieldName) {
        $fieldMatches = [];
        preg_match('/' . $fieldName . '="(.*?)"/', $annotation, $fieldMatches);
        if(!isset($fieldMatches[1])) {
            return null;
        }

        return $fieldMatches[1];
    }

}