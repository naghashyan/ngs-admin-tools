<?php
/**
 * AbstractPushNotificationSender abstract push notification sender
 * all push notification senders should extend from this class
 *
 * @author Mikael Mkrtchyan
 * @site http://naghashyan.com
 * @mail mikael.mkrtchyan@naghashyan.com
 * @year 2020
 * @package ngs.AdminTools.notification.pushNotifications
 * @version 1.0.0
 *
 */

namespace ngs\AdminTools\managers\notification\pushNotifications;


abstract class AbstractPushNotificationSender
{
    /**
     * instance of push sender
     */
    protected static $instance = null;

    /**
     * Returns an singleton instance of this class
     *
     * @return AbstractPushNotificationSender
     */
    public abstract static function getInstance(array $params = []): AbstractPushNotificationSender;


    /**
     * returns sdk download link
     *
     * @return string
     */
    public abstract function getSdk() :string;

    /**
     * returns publish key
     *
     * @return string
     */
    public abstract function getPublishKey() :string;


    /**
     * returns publish key
     *
     * @return string
     */
    public abstract function getSubscribeKey() :string;


    /**
     * returns publish key
     *
     * @return string
     */
    public abstract function getUuid() :string;


    /**
     * send notification to given channel
     *
     * @param string $channel
     * @param string|array $message
     *
     * @return mixed
     */
    public abstract function sendNotification(string $channel, $message);


    /**
     * send notifications to given groups and users
     * 
     * @param array $groups
     * @param array $users
     * @param $message
     */
    public function sendNotificationsToUsersAndGroups(array $groups, array $users, $message) {
        $channels = [];
        foreach($groups as $group) {
            if(!in_array('group-' . $group, $channels)) {
                $channels[] = 'group-' . $group;
            }
        }

        foreach($users as $user) {
            if(!in_array('user-' . $user, $channels)) {
                $channels[] = 'user-' . $user;
            }
        }

        foreach($channels as $channel) {
            $this->sendNotification($channel, $message);
        }
    }
}
