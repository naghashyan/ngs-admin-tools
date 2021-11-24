<?php
/**
 *
 * LanguageMapper class is extended class from AbstractCmsMapper.
 * It contatins all read and write functions which are working with languages table.
 *
 * @author Mikael Mkrtchyan
 * @site http://naghashyan.com
 * @mail mikael.mkrtchyan@naghashyan.com
 * @year 2021
 * @package ngs.NgsAdminTools.dal.mappers
 * @version 1.0
 *
 */

namespace ngs\NgsAdminTools\dal\mappers;

use ngs\NgsAdminTools\dal\dto\LanguageDto;
use ngs\dal\dto\AbstractDto;
use ngs\dal\mappers\AbstractMysqlMapper;

class LanguageMapper extends AbstractCmsMapper {

    //! Private members.

    private static $instance;
    public $tableName = "languages";

    /**
     * Returns an singleton instance of this class
     *
     * @return LanguageMapper
     */
    public static function getInstance(): LanguageMapper {
        if (self::$instance == null){
            self::$instance = new LanguageMapper();
        }
        return self::$instance;
    }

    public function createDto(): AbstractDto
    {
        return new LanguageDto();
    }

    /**
     * @see AbstractMysqlMapper::getPKFieldName()
     */
    public function getPKFieldName() :string {
        return "id";
    }

    /**
     * @see AbstractMysqlMapper::getTableName()
     */
    public function getTableName() :string {
        return $this->tableName;
    }


    private $GET_LANGUAGES = 'SELECT * FROM `%s`';

    /**
     * @return LanguageDto[]
     * @throws \ngs\exceptions\DebugException
     */
    public function getLanguages() :array {
        $sqlQuery = sprintf($this->GET_LANGUAGES, $this->getTableName());
        return $this->fetchRows($sqlQuery, []);
    }


    private $GET_LANGUAGE_BY_ID = 'SELECT * FROM `%s` WHERE `id` =:id';

    /**
     * returns language found by id
     *
     * @param int $id
     * @return LanguageDto|null
     *
     * @throws \ngs\exceptions\DebugException
     */
    public function getLanguageById(int $id) :?LanguageDto {
        $sqlQuery = sprintf($this->GET_LANGUAGE_BY_ID, $this->getTableName());
        return $this->fetchRow($sqlQuery, ['id' => $id]);
    }


    private $GET_LANGUAGE_BY_CODE = 'SELECT * FROM `%s` WHERE `code` =:langaugeCode';

    /**
     * returns language by iso 2 code
     *
     * @param string $code
     *
     * @return LanguageDto|null
     */
    public function getLanguageByCode(string $code) {
        $sqlQuery = sprintf($this->GET_LANGUAGE_BY_CODE, $this->getTableName());
        return $this->fetchRow($sqlQuery, ['langaugeCode' => $code]);
    }
}