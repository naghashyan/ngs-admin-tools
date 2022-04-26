<?php


namespace ngs\AdminTools\dal\mappers\notification;

use ngs\AdminTools\dal\dto\notification\NotificationDto;
use ngs\dal\dto\AbstractDto;
use ngs\dal\mappers\AbstractMysqlMapper;

class NotificationMapper extends AbstractMysqlMapper
{

    //! Private members.

    private static ?NotificationMapper $instance = null;
    public $tableName = 'ngs_notifications';

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
     * @return NotificationMapper Object
     */
    public static function getInstance(): NotificationMapper
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }


    /**
     * @return NotificationDto
     */
    public function createDto() :AbstractDto
    {
        return new NotificationDto();
    }


    private $GET_USER_NOT_READ_NOTIFICATION = "SELECT * FROM %s WHERE `type`='system' AND `user_id` = :userId AND `read` = 0 ORDER BY `shown` ASC, `id` DESC LIMIT :offset, :limit";

    /**
     * @param int $userId
     * @param int $offset
     * @param int $limit
     * @return NotificationDto[]
     * @throws \ngs\exceptions\DebugException
     */
    public function getUserNotReadNotifications(int $userId, int $offset, int $limit) :array
    {
        $sqlQuery = sprintf($this->GET_USER_NOT_READ_NOTIFICATION, $this->getTableName());
        return $this->fetchRows($sqlQuery, ['userId' => $userId, 'offset' => $offset, 'limit' => $limit]);
    }


    private $GET_USER_NOT_READ_NOTIFICATION_COUNT = "SELECT COUNT(*) as count FROM %s WHERE `type`='system' AND `user_id` = :userId AND `read` = 0";

    /**
     * @param int $userId
     * @return int
     * @throws \ngs\exceptions\DebugException
     */
    public function getUserNotReadNotificationsCount(int $userId) {
        $sqlQuery = sprintf($this->GET_USER_NOT_READ_NOTIFICATION_COUNT, $this->getTableName());
        $count = $this->fetchField($sqlQuery, 'count', ['userId' => $userId]);
        return (int) $count;
    }


    /**
     * mark order as read
     *
     * @param int $userId
     * @param int $notificationId
     * @return bool
     * @throws \ngs\exceptions\DebugException
     */
    public function markUserNotificationAsRead(int $userId, int $notificationId) {
        $notification = $this->getUserNotification($userId, $notificationId);
        if(!$notification) {
            return false;
        }
        $notification->setRead(1);
        $this->updateByPK($notification);
        return true;
    }

    /**
     * mark users all notifications read
     * @param int $userId
     * @return bool
     * @throws \ngs\exceptions\DebugException
     */
    public function markUserNotificationsAsRead(int $userId) {
        return !!$this->markUserNotificationsRead($userId);
    }


    /**
     * mark order as shown
     *
     * @param int $userId
     * @param int $notificationId
     * @return bool
     * @throws \ngs\exceptions\DebugException
     */
    public function markUserNotificationAsShown(int $userId, int $notificationId) {
        $notification = $this->getUserNotification($userId, $notificationId);
        if(!$notification) {
            return false;
        }
        $notification->setShown(1);
        $this->updateByPK($notification);
        return true;
    }


    private $GET_NOTIFICATION_BY_ID = "SELECT * FROM %s WHERE `id` = :notificationId";

    /**
     * returns notification by id
     *
     * @param int $notificationId
     *
     * @return NotificationDto|null
     *
     * @throws \ngs\exceptions\DebugException
     */
    public function getNotificationById(int $notificationId) :?NotificationDto
    {
        $sqlQuery = sprintf($this->GET_NOTIFICATION_BY_ID, $this->getTableName());
        $notification = $this->fetchRow($sqlQuery, ['notificationId' => $notificationId]);
        return $notification;
    }


    private $UPDATE_JOB_NOTIDICATIONS = "UPDATE %s SET `title`=:title, `content`=:content, `progress_percent`=:progress WHERE job_id=:jobId";

    public function updateJobNotifications(string $title, string $content, float $progress, int $jobId) {
        $sqlQuery = sprintf($this->UPDATE_JOB_NOTIDICATIONS, $this->getTableName());
        $result = $this->executeQuery($sqlQuery, ['content' => $content, 'progress'=>$progress, 'jobId' => $jobId, 'title' => $title]);
        return $result;
    }


    private $GET_JOB_NOTIFICATIONS_COUNT = "SELECT COUNT(*) as count FROM %s WHERE `job_id` = :jobId";

    public function getJobNotificationsCount(int $jobId) :int
    {
        $sqlQuery = sprintf($this->GET_JOB_NOTIFICATIONS_COUNT, $this->getTableName());
        $count = $this->fetchField($sqlQuery, 'count', ['jobId' => $jobId]);
        return (int) $count;
    }



    private $GET_USER_NOTIFICATION = "SELECT * FROM %s WHERE `user_id` = :userId AND `id` = :notificationId";

    /**
     * returns user notification by id
     *
     * @param int $userId
     * @param int $notificationId
     *
     * @return NotificationDto|null
     *
     * @throws \ngs\exceptions\DebugException
     */
    private function getUserNotification(int $userId, int $notificationId) :?NotificationDto
    {
        $sqlQuery = sprintf($this->GET_USER_NOTIFICATION, $this->getTableName());
        $notification = $this->fetchRow($sqlQuery, ['userId' => $userId, 'notificationId' => $notificationId]);
        return $notification;
    }

    private $MARK_USER_NOTIFICATIONS_READ = "UPDATE %s SET `read`=1 WHERE `user_id` = :userId";

    /**
     * set all notifications of user read
     * @param int $userId
     * @return int|null
     * @throws \ngs\exceptions\DebugException
     */
    private function markUserNotificationsRead(int $userId)
    {
        $sqlQuery = sprintf($this->MARK_USER_NOTIFICATIONS_READ, $this->getTableName());
        return $this->executeUpdate($sqlQuery, ['userId' => $userId]);
    }
}
