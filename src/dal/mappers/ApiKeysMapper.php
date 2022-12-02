<?php
/**
 *
 * ApiKeysMapper class is extended class from AbstractMysqlMapper.
 * It contatins all read and write functions which are working with api_keys table.
 *
 * @author Levon Naghashyan
 * @site https://naghashyan.com
 * @mail levon@naghashyan.com
 * @year 2020
 * @package api.dal.mappers.api
 * @version 1.0.0
 *
 */

namespace ngs\AdminTools\dal\mappers;

use ngs\AdminTools\dal\dto\ApiKeysDto;
use ngs\dal\mappers\AbstractMysqlMapper;

class ApiKeysMapper extends AbstractMysqlMapper
{

    //! Private members.

    private static ?self $instance = null;
    private string $tableName = 'api_keys';


    /**
     *
     * Returns an singleton instance of this class
     *
     * @return self
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * @see AbstractMysqlMapper::createDto()
     */
    public function createDto(): ApiKeysDto
    {
        return new ApiKeysDto();
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


    private string $GET_USER_BY_KEY_SQL = 'SELECT * FROM `%s` WHERE `key` = :apiKey';

    /**
     * Returns api user dto by api key
     *
     * @param string $apiKey
     * @return ApiKeysDto|null
     */
    public function getApiUserByKey(string $apiKey): ?ApiKeysDto
    {
        $sqlQuery = sprintf($this->GET_USER_BY_KEY_SQL, $this->tableName);
        return $this->fetchRow($sqlQuery, ['apiKey' => $apiKey]);
    }
}