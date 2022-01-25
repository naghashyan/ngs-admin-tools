<?php

namespace ngs\AdminTools\dal\dto\job;


use ngs\dal\dto\AbstractDto;

class JobDto extends AbstractDto
{

    /** @var string */
    public string $tableName = 'ngs_jobs';


    protected $id;
    protected $name;
    protected $params;
    protected $executor;
    protected $result;
    protected $progress;
    protected $status;
    protected $userId;
    protected $addedTime;


    // Map of DB value to Field value
    protected array $mapArray = [
        'id' => 'id',
        'name' => 'name',
        'params' => 'params',
        'executor' => 'executor',
        'result' => 'result',
        'progress' => 'progress',
        'status' => 'status',
        'user_id' => 'userId',
        'added_time' => 'addedTime'
    ];

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id): void
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name): void
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * @param mixed $params
     */
    public function setParams($params): void
    {
        $this->params = $params;
    }

    /**
     * @return mixed
     */
    public function getExecutor()
    {
        return $this->executor;
    }

    /**
     * @param mixed $executor
     */
    public function setExecutor($executor): void
    {
        $this->executor = $executor;
    }

    /**
     * @return mixed
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @param mixed $result
     */
    public function setResult($result): void
    {
        $this->result = $result;
    }

    /**
     * @return mixed
     */
    public function getProgress()
    {
        return $this->progress;
    }

    /**
     * @param mixed $progress
     */
    public function setProgress($progress): void
    {
        $this->progress = $progress;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed $status
     */
    public function setStatus($status): void
    {
        $this->status = $status;
    }

    /**
     * @return mixed
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @param mixed $userId
     */
    public function setUserId($userId): void
    {
        $this->userId = $userId;
    }

    /**
     * @return mixed
     */
    public function getAddedTime()
    {
        return $this->addedTime;
    }

    /**
     * @param mixed $addedTime
     */
    public function setAddedTime($addedTime): void
    {
        $this->addedTime = $addedTime;
    }

    public function getMapArray(): array
    {
        return $this->mapArray;
    }
}