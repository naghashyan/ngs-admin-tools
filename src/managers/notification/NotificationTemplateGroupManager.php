<?php
/**
 * NotificationTemplateGroupManager manager class
 * for handle user notification template groups about job execution
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

use ngs\AdminTools\dal\dto\notification\NotificationTemplateGroupDto;
use ngs\AdminTools\dal\mappers\notification\NotificationTemplateGroupMapper;
use ngs\AbstractManager;

class NotificationTemplateGroupManager extends AbstractManager {

    private static ?NotificationTemplateGroupManager $instance = null;

    private array $notificationGroups = [];

    /**
     * Returns an singleton instance of this class
     *
     * @return NotificationTemplateGroupManager Object
     */
    public static function getInstance(): NotificationTemplateGroupManager {
        if (self::$instance === null){
            self::$instance = new self();
        }
        return self::$instance;
    }


    /**
     * @return NotificationTemplateGroupMapper
     */
    public function getMapper() :NotificationTemplateGroupMapper
    {
        return NotificationTemplateGroupMapper::getInstance();
    }


    /**
     * returns notifiacion template group ids
     *
     * @param int $notificationTemplateId
     * @return array
     */
    public function getNotificatioTempalteGroups(int $notificationTemplateId) :array {
        if(isset($this->notificationGroups[$notificationTemplateId])) {
            return $this->notificationGroups[$notificationTemplateId];
        }

        /** @var NotificationTemplateGroupDto[] $notificationGroups */
        $notificationGroups = $this->getMapper()->getNotificatioTempalteGroups($notificationTemplateId);
        $ids = [];
        foreach($notificationGroups as $group) {
            if(!in_array($group->getGroupId(), $ids)) {
                $ids[] = $group->getGroupId();
            }
        }
        
        $this->notificationGroups[$notificationTemplateId] = $ids;
        return $this->notificationGroups[$notificationTemplateId];
    }
}