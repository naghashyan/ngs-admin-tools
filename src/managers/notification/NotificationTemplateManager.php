<?php
/**
 * NotificationTemplateManager manager class
 * for handle user notification templates about job execution
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

use ngs\AdminTools\dal\dto\job\JobDto;
use ngs\AdminTools\dal\dto\notification\NotificationDto;
use ngs\AdminTools\dal\dto\notification\NotificationTemplateDto;
use ngs\AdminTools\dal\mappers\notification\NotificationTemplateMapper;
use ngs\AbstractManager;
use ngs\AdminTools\managers\UserManager;
use ngs\AdminTools\util\MailSender;
use ngs\event\structure\AbstractEventStructure;
use ngs\templater\NgsSmartyTemplater;

class NotificationTemplateManager extends AbstractManager {

    private static ?NotificationTemplateManager $instance = null;

    /**
     * Returns an singleton instance of this class
     *
     * @return NotificationTemplateManager Object
     */
    public static function getInstance(): NotificationTemplateManager {
        if (self::$instance === null){
            self::$instance = new self();
        }
        return self::$instance;
    }


    /**
     * @return NotificationTemplateMapper
     */
    public function getMapper() :NotificationTemplateMapper
    {
        return NotificationTemplateMapper::getInstance();
    }


    /**
     * send notifications for given event if has notification subscribtion
     * 
     * @param AbstractEventStructure $event
     * @throws \ngs\exceptions\DebugException
     */
    public function sendNotificationsForEvent(AbstractEventStructure $event) {
        $countOfNotifications = $this->getEventNotificationTempaltesCount($event->getEventId());
        if($countOfNotifications) {
            $tempaltes = $this->getEventNotificationTempaltes($event->getEventId());
            foreach($tempaltes as $notificationTempalte) {
                $this->sendNotification($notificationTempalte, $event);
            }
        }
    }


    /**
     * send notification for tempalte
     *
     * @param NotificationTemplateDto $tempalte
     * @param AbstractEventStructure $event
     */
    public function sendNotification(NotificationTemplateDto $tempalte, AbstractEventStructure $event) {
        if(!$tempalte->getInEmail() && !$tempalte->getInSystem()) {
            return;
        }
        
        $userNotificationTemplateGroupManager = NotificationTemplateGroupManager::getInstance();
        $userGroups = $userNotificationTemplateGroupManager->getNotificatioTempalteGroups($tempalte->getId());

        $userManager = UserManager::getInstance();
        $users = [];
        $groupUserIds = [];
        if($userGroups) {
            $users = $userManager->getUsersByGroups($userGroups);
            $groupUserIds = $userManager->getUniqueValuesFromList($users, 'id');
        }

        $jobId = null;
        $job = null;
        if(method_exists($event, 'getJob')) {
            /** @var JobDto $job */
            $job = $event->getJob();
            $jobId = $job->getId();
            if($job->getUserId()) {
                $user = $userManager->getUserById($job->getUserId());
                if($user) {
                    $users[] = $user;
                }
            }
        }

        $content = $this->getNotificationContent($tempalte, $event);
        $userIds = $userManager->getUniqueValuesFromList($users, 'id');
        $notificationManager = NotificationsManager::getInstance();
        
        if($tempalte->getInEmail()) {
            $emails = $userManager->getUniqueValuesFromList($users, 'email');

            $result = $this->sendEmailNotifications($emails, $this->getEmailSubject($tempalte, $event), $content, $event->getAttachemts());
            if ($result['success']) {
                foreach ($userIds as $userId) {
                    $notificationManager->createNotification($userId, $tempalte, $content, false, $jobId, 'email');
                }
            }

        }

        if($tempalte->getInSystem()) {
            if($notificationManager->hasPushNotificationSupport()) {
                $sender = $notificationManager->getPushNotificationSender();
                $groups = $userGroups ?: [];
                $users = $groupUserIds ? array_diff($userIds, $groupUserIds) : $userIds;
                $sender->sendNotificationsToUsersAndGroups($groups, $users, ['event' => $event->getEventClass(), 'params' => $event->getParams(), 'level' => $tempalte->getLevel()]);
            }
            
            if($job) {
                if($notificationManager->getJobNotificationsCount($jobId)) {
                    $progress = $job->getProgress();
                    $notificationManager->updateJobNotifications($tempalte, $content, $progress, $jobId);
                    return;
                }
            }
            foreach($userIds as $userId) {
                $notificationManager = NotificationsManager::getInstance();
                $notificationManager->createNotification($userId, $tempalte, $content, false, $jobId, 'system');
            }
        }
    }

    /**
     * Return email subject
     * @param AbstractCmsDto $template
     * @param $event
     * @return string
     */
    public function getEmailSubject(NotificationTemplateDto $template, $event): string
    {
        if ($event->getEmailSubject()) {
            return $event->getEmailSubject();
        }

        return $template->getName();
    }


    /**
     * returns notification templates by event name
     *
     * @param string $eventName
     * @return array
     * @throws \ngs\exceptions\DebugException
     */
    public function getEventNotificationTempaltes(string $eventName) :array
    {
        return $this->getMapper()->getEventNotificationTempaltes($eventName);
    }


    /**
     * returns notification templates count by event name
     *
     * @param string $eventName
     * @return int
     * @throws \ngs\exceptions\DebugException
     */
    public function getEventNotificationTempaltesCount(string $eventName) :int
    {
        return $this->getMapper()->getEventNotificationTempaltesCount($eventName);
    }


    /**
     * @param array $emails
     * @param string $subject
     * @param string $content
     * @param array $attachments
     *
     * @return array
     */
    private function sendEmailNotifications(array $emails, string $subject, string $content, array $attachments = []) {
        $newMail = new MailSender();


        $newMail->setSubject($subject);
        $newMail->setRecipient($emails);

        if($attachments) {
            foreach($attachments as $attachment) {
                $newMail->addAttachment($attachment['path'], $attachment['name']);
            }
        }

        $from = NGS()->getDefinedValue('MAIL_SENDER');
        $newMail->setFrom($from);
        $result = $newMail->send('', $content);
        return $result;
    }


    /**
     * get notification content
     *
     * @param NotificationTemplateDto $tempalte
     * @param AbstractEventStructure $event
     * @return string
     */
    private function getNotificationContent(NotificationTemplateDto $tempalte, AbstractEventStructure $event) :string
    {
        $jobId = null;
        if(method_exists($event, 'getJob')) {
            $jobId = $event->getJob()->getId();
        }

        $templateTempName = $jobId ? md5($jobId . $tempalte->getTemplate()) : md5($tempalte->getTemplate());
        $tempatePath = NGS()->getDataDir('admin') . '/temp_email_templates/' . $templateTempName . '.tpl';

        file_put_contents($tempatePath, html_entity_decode($tempalte->getTemplate()));

        $availableParams = $event->getAvailableVariables();
        $params = [];
        foreach($availableParams as $key => $value) {
            $params[$key] = $value['value'];
        }

        $templater = new NgsSmartyTemplater();
        $templater->assign('ns', $params);
        $html = $templater->fetchTemplate($tempatePath);

        unlink($tempatePath);

        return $html ? $html : "";
    }
}
