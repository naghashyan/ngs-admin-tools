<?php
/**
 * NotificationsManager manager class
 * for handle user notifications about job execution
 *
 * @author Mikael Mkrtchyan
 * @site http://naghashyan.com
 * @mail mikael.mkrtchyan@naghashyan.com
 * @year 2021
 * @package ngs.AdminTools.managers.notification
 * @version 1.0.0
 *
 */

namespace ngs\AdminTools\managers\notification;

use ngs\AdminTools\dal\dto\notification\NotificationDto;
use ngs\AdminTools\dal\dto\notification\NotificationTemplateDto;
use ngs\AdminTools\dal\mappers\notification\NotificationMapper;
use ngs\AbstractManager;
use ngs\AdminTools\managers\notification\pushNotifications\AbstractPushNotificationSender;

class NotificationsManager extends AbstractManager {

    private static ?NotificationsManager $instance = null;

    /**
     * Returns an singleton instance of this class
     *
     * @return NotificationsManager Object
     */
    public static function getInstance(): NotificationsManager {
        if (self::$instance === null){
            self::$instance = new self();
        }
        return self::$instance;
    }


    /**
     * @return NotificationMapper
     */
    public function getMapper() :NotificationMapper
    {
        return NotificationMapper::getInstance();
    }


    /**
     * returns true if push notification can be sent
     *
     * @return bool
     */
    public static function hasPushNotificationSupport() {
        $pushSender = self::getPushNotificationSender();
        return $pushSender ? true : false;
    }


    /**
     * returns instance of push sender
     *
     * @return AbstractPushNotificationSender|null
     */
    public static function getPushNotificationSender() :?AbstractPushNotificationSender {
        $pushSender = NGS()->get('PUSH_SENDER');
        return $pushSender ?: null;
    }


    /**
     * create new job instance
     *
     * @param int $userId
     * @param NotificationTemplateDto $template
     * @param string $content
     * @param bool $withProgress
     * @param int|null $jobId
     * @param string $type
     *
     * @return NotificationDto|null
     */
    public function createNotification(int $userId, NotificationTemplateDto $template, string $content, bool $withProgress = false, ?int $jobId = null, string $type = 'system') :?NotificationDto
    {
        $mapper = $this->getMapper();
        $notificationDto = $mapper->createDto();

        $notificationDto->setNotificationTempalteId($template->getId());
        $notificationDto->setUserId($userId);
        $notificationDto->setTitle($template->getName());
        $notificationDto->setJobId($jobId);
        $notificationDto->setType($type);
        $notificationDto->setLevel($template->getLevel());
        $notificationDto->setContent($content);
        $notificationDto->setWithProgress($withProgress ? 1 : 0);
        $notificationDto->setRead(0);

        try {
            $id = $mapper->insertDto($notificationDto);
            if($id) {
                $notificationDto->setId($id);
                return $notificationDto;
            }

            return null;
        }
        catch (\Exception $exp) {
            return null;
        }
    }


    /**
     * returns count of notification by job id
     *
     * @param int $jobId
     * @return int
     */
    public function getJobNotificationsCount(int $jobId) :int
    {
        $mapper = $this->getMapper();
        return $mapper->getJobNotificationsCount($jobId);
    }


    /**
     * @param string $title
     * @param string $content
     * @param float $progress
     * @param int $jobId
     *
     * @return int|null
     */
    public function updateJobNotifications(NotificationTemplateDto $template, string $content, float $progress, int $jobId) :?int
    {
        $mapper = $this->getMapper();
        return $mapper->updateJobNotifications($template, $content, $progress, $jobId);
    }


    /**
     * update notification
     *
     * @param int $notificationId
     * @param float|null $progress
     * @param string|null $resultMessage
     *
     * @return bool
     */
    public function updateNotification(int $notificationId, ?float $progress = null, ?string $resultMessage = null) :bool
    {
        try {
            $mapper = $this->getMapper();
            $notification = $mapper->getNotificationById($notificationId);
            if(!$notification) {
                return false;
            }

            $somethingChanged = false;
            if($progress !== null) {
                $progress = $progress <= 0 ? 0 : $progress;
                $progress = $progress >= 100 ? 100 : $progress;
                $notification->setProgressPercent($progress);
                $somethingChanged = true;
            }
            if($resultMessage !== null) {
                $notification->setContent($resultMessage);
                $somethingChanged = true;
            }

            if($somethingChanged) {
                $mapper->updateByPK($notification);
            }

            return true;
        }
        catch(\Exception $exp) {
            return false;
        }
    }


    /**
     * get user not read notifications list
     *
     * @param int $userId
     * @param int $offset
     * @param int $limit
     *
     * @return NotificationDto[]
     *
     * @throws \ngs\exceptions\DebugException
     */
    public function getUserNotReadNotifications(int $userId, int $offset, int $limit) :array
    {
        return $this->getMapper()->getUserNotReadNotifications($userId, $offset, $limit);
    }


    /**
     * get user not read notifications count
     *
     * @param int $userId
     *
     * @return int
     *
     * @throws \ngs\exceptions\DebugException
     */
    public function getUserNotReadNotificationsCount(int $userId) :int
    {
        return $this->getMapper()->getUserNotReadNotificationsCount($userId);
    }


    /**
     * mark notification as read
     *
     * @param int $userId
     * @param int $notificationId
     *
     * @return bool
     *
     * @throws \ngs\exceptions\DebugException
     */
    public function markUserNotificationAsRead(int $userId, int $notificationId) :bool
    {
        if($notificationId === -1) {
            return $this->getMapper()->markUserNotificationsAsRead($userId);
        }
        return $this->getMapper()->markUserNotificationAsRead($userId, $notificationId);
    }


    /**
     * mark notification as shown
     *
     * @param int $userId
     * @param int $notificationId
     *
     * @return bool
     *
     * @throws \ngs\exceptions\DebugException
     */
    public function markUserNotificationAsShown(int $userId, int $notificationId) :bool
    {
        return $this->getMapper()->markUserNotificationAsShown($userId, $notificationId);
    }
}
