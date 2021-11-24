<?php

namespace ngs\NgsAdminTools\dal\dto\notification;

use ngs\dal\dto\AbstractDto;

class NotificationDto extends AbstractDto
{

    /** @var string */
    public string $tableName = 'ngs_notifications';


    protected $id;
    protected $title;
    protected $content;
    protected $read;
    protected $shown;
    protected $withProgress;
    protected $progressPercent;
    protected $userId;
    protected $addedDate;


    // Map of DB value to Field value
    protected array $mapArray = [
        'id' => 'id',
        'title' => 'title',
        'content' => 'content',
        'read' => 'read',
        'shown' => 'shown',
        'with_progress' => 'withProgress',
        'progress_percent' => 'progressPercent',
        'user_id' => 'userId',
        'added_date' => 'addedDate'
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
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $title
     */
    public function setTitle($title): void
    {
        $this->title = $title;
    }

    /**
     * @return mixed
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param mixed $content
     */
    public function setContent($content): void
    {
        $this->content = $content;
    }

    /**
     * @return mixed
     */
    public function getRead()
    {
        return $this->read;
    }

    /**
     * @param mixed $read
     */
    public function setRead($read): void
    {
        $this->read = $read;
    }

    /**
     * @return mixed
     */
    public function getShown()
    {
        return $this->shown;
    }

    /**
     * @param mixed $shown
     */
    public function setShown($shown): void
    {
        $this->shown = $shown;
    }

    /**
     * @return mixed
     */
    public function getWithProgress()
    {
        return $this->withProgress;
    }

    /**
     * @param mixed $withProgress
     */
    public function setWithProgress($withProgress): void
    {
        $this->withProgress = $withProgress;
    }

    /**
     * @return mixed
     */
    public function getProgressPercent()
    {
        return $this->progressPercent;
    }

    /**
     * @param mixed $progressPercent
     */
    public function setProgressPercent($progressPercent): void
    {
        $this->progressPercent = $progressPercent;
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
    public function getAddedDate()
    {
        return $this->addedDate;
    }

    /**
     * @param mixed $addedDate
     */
    public function setAddedDate($addedDate): void
    {
        $this->addedDate = $addedDate;
    }

    public function getMapArray(): array
    {
        return $this->mapArray;
    }
}