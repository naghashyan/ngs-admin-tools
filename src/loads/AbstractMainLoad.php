<?php
/**
 * AbstractMainLoad main load class
 *
 * @author Mikael Mkrtchyan
 * @site   http://naghashyan.com
 * @email  mikael.mkrtchyan@naghashyan.com
 * @year   2012-2017
 * @package ngs.AdminTools.loads
 * @version 6.5.0
 *
 **/

namespace ngs\AdminTools\loads;

use admin\managers\securityPage\SecurityPageManager;
use ngs\AdminTools\exceptions\NoAccessException;
use ngs\AdminTools\managers\notification\NotificationsManager;
use ngs\event\EventManager;
use ngs\AdminTools\util\LoggerFactory;
use ngs\Dispatcher;
use ngs\exceptions\NgsErrorException;
use ngs\request\AbstractLoad;
use ngs\AdminTools\util\NavigationUtil;
use Monolog\Logger;

abstract class AbstractMainLoad extends AbstractCmsLoad
{

    public function load()
    {
        $tinymceKey = 111111;
        $this->addParam('tinymceKey', $tinymceKey);
        $sessionManager = NGS()->getSessionManager();
        $currentUser = $sessionManager->getUser();
        $adminGroup = $sessionManager->getUserGroupByName('admin');

        $this->addParam('isSuperAdmin', $currentUser->getLevel() == $adminGroup->getId());
        $this->addParam('hasPushNotificationSupport', false);
        if (NotificationsManager::hasPushNotificationSupport()) {
            $pushNotificationSender = NotificationsManager::getPushNotificationSender();
            $this->addParam('hasPushNotificationSupport', true);
            $this->addParam('pushNotificationSdk', $pushNotificationSender->getSdk());
            $this->addParam('pushNotificationPublishKey', $pushNotificationSender->getPublishKey());
            $this->addParam('pushNotificationSubscribeKey', $pushNotificationSender->getSubscribeKey());
            $this->addParam('pushNotificationUuid', $pushNotificationSender->getUuid());
            $currentUserChannels = [];
            $currentUserChannels[] = 'group-' . $currentUser->getLevel();
            $currentUserChannels[] = 'user-' . $currentUser->getId();
            $this->addParam('currentUserChannels', implode(",", $currentUserChannels));
        }


        $this->initAllowedDtosForCurrentUser($currentUser->getLevel());

        $this->afterMainLoad();
    }


    /**
     * @return array
     * @throws \ngs\exceptions\DebugException
     */
    public function getRequestAllowedGroups()
    {
        $adminGroup = NGS()->getSessionManager()->getUserGroupByName('admin');
        return ["allowed" => [$adminGroup->getId()]];
    }

    protected function afterMainLoad()
    {

    }


    private function initAllowedDtosForCurrentUser($ruleValue): void
    {
        $securityManager = SecurityPageManager::getInstance();
        $listOfAllowedDtosAsArray = $securityManager->getAllowedDtosForCurrentUser($ruleValue);
        $this->addParam('allowedDtos', $listOfAllowedDtosAsArray);
    }
}