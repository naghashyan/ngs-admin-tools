<?php

namespace ngs\AdminTools\dal\dto\notification;

use ngs\dal\dto\AbstractDto;

class NotificationDto extends AbstractDto
{

    /** @var string */
    public string $tableName = 'ngs_notifications';


    protected $id;
    protected $notificationTempalteId;
    protected $title;
    protected $content;
    protected $read;
    protected $shown;
    protected $level;
    protected $withProgress;
    protected $progressPercent;
    protected $type;
    protected $userId;
    protected $jobId;
    protected $addedDate;


    // Map of DB value to Field value
    protected array $mapArray = [
        'id' => 'id',
        'notification_tempalte_id' => 'notificationTempalteId',
        'title' => 'title',
        'content' => 'content',
        'read' => 'read',
        'level' => 'level',
        'shown' => 'shown',
        'with_progress' => 'withProgress',
        'progress_percent' => 'progressPercent',
        'type' => 'type',
        'user_id' => 'userId',
        'job_id' => 'jobId',
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
    public function getNotificationTempalteId()
    {
        return $this->notificationTempalteId;
    }

    /**
     * @param mixed $notificationTempalteId
     */
    public function setNotificationTempalteId($notificationTempalteId): void
    {
        $this->notificationTempalteId = $notificationTempalteId;
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
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * @param mixed $level
     */
    public function setLevel($level): void
    {
        $this->level = $level;
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
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     */
    public function setType($type): void
    {
        $this->type = $type;
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
    public function getJobId()
    {
        return $this->jobId;
    }

    /**
     * @param mixed $jobId
     */
    public function setJobId($jobId): void
    {
        $this->jobId = $jobId;
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
