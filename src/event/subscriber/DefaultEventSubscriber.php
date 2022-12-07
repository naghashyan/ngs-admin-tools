<?php
/**
 * DefaultEventSubscriber here will be handled all admin tools default events
 *
 * @author Mikael Mkrtchyan, Levon Naghashyan
 * @site https://naghashyan.com
 * @mail miakel.mkrtchyan@naghashyan.com
 * @year 2017-2019
 * @package ngs.AdminTools.managers
 * @version 2.0.0
 *
 */

namespace ngs\AdminTools\event\subscriber;

use ngs\AdminTools\event\structure\AfterLoadEventStructure;
use ngs\AdminTools\event\structure\BeforeLoadEventStructure;
use ngs\AdminTools\managers\notification\NotificationTemplateManager;
use ngs\event\structure\EventDispatchedStructure;
use ngs\event\subscriber\AbstractEventSubscriber;

class DefaultEventSubscriber extends AbstractEventSubscriber
{

    /**
     * should return arrak,
     * key => eventStructClass
     * value => public method of this class, which will be called when (key) event will be triggered
     * 
     * @return array
     */
    public function getSubscriptions() :array {
        return [
            EventDispatchedStructure::class => "eventDispatchedHandler"
        ];
    }


    /**
     * @param EventDispatchedStructure $event
     */
    public function eventDispatchedHandler(EventDispatchedStructure $event) {
        $notificationTemplateManager = NotificationTemplateManager::getInstance();
        $notificationTemplateManager->sendNotificationsForEvent($event->getEvent());
    }
}