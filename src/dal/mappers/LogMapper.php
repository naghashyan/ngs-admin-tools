<?php
/**
 *
 * LogMapper class is extended class from AbstractCmsMapper.
 * It contatins all read and write functions which are working with ilyov_logs table.
 *
 * @author Mikael Mkrtchyan
 * @site http://naghashyan.com
 * @mail mikael.mkrtchyan@naghashyan.com
 * @year 2019
 * @package ngs.AdminTools.dal.mappers
 * @version 1.0
 *
 */

namespace ngs\AdminTools\dal\mappers;

use ngs\AdminTools\dal\dto\LogDto;
use ngs\dal\dto\AbstractDto;
use ngs\dal\mappers\AbstractMysqlMapper;

class LogMapper extends AbstractCmsMapper
{

    //! Private members.

    private static ?self $instance = null;
    public string $tableName = "ngs_logs";

    /**
     * Returns an singleton instance of this class
     *
     * @return LogMapper
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
     *
     * return LogDto
     */
    public function createDto(): LogDto
    {
        return new LogDto();
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
}