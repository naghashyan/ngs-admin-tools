<?php


namespace ngs\AdminTools\dal\mappers\job;

use ngs\AdminTools\dal\dto\job\JobDto;
use ngs\dal\dto\AbstractDto;
use ngs\dal\mappers\AbstractMysqlMapper;

class JobMapper extends AbstractMysqlMapper
{

    //! Private members.

    private static ?JobMapper $instance = null;
    public string $tableName = 'ngs_jobs';

    public function getTableName(): string
    {
        return $this->tableName;
    }

    public function getPKFieldName(): string
    {
        return 'id';
    }

    /**
     * Returns an singleton instance of this class
     *
     * @return JobMapper Object
     */
    public static function getInstance(): JobMapper
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }


    /**
     * @var string
     */
    private $GET_JOB_BY_ID = 'SELECT * FROM %s WHERE `id` = :itemId';

    public function getById(int $id) {
        $bindArray = ['itemId' => $id];
        $sqlQuery = sprintf($this->GET_JOB_BY_ID, $this->getTableName());
        return $this->fetchRow($sqlQuery, $bindArray);
    }

    /**
     * @var string
     */
    private $GET_RUNNING_JOB_BY_NAME_AND_PARAM = 'SELECT * FROM %s WHERE `name` = :jobName AND `params` LIKE :likeCondition AND `status` != "finished"';

    public function getRunningJobByNameAndParam(string $jobName, string $paramName, string $paramValue)
    {
        $likeCondition = '%"' . $paramName . '":' . $paramValue . ',%';
        $bindArray = [
            'likeCondition' => $likeCondition,
            'jobName' => $jobName
        ];
        $sqlQuery = sprintf($this->GET_RUNNING_JOB_BY_NAME_AND_PARAM, $this->getTableName());
        return $this->fetchRow($sqlQuery, $bindArray);
    }

    /**
     * @return JobDto
     */
    public function createDto() :AbstractDto
    {
        return new JobDto();
    }
}