<?php
/**
 * PubNubPushNotificationSender pub nub push notification sender
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

use PubNub\PubNub;
use PubNub\PNConfiguration;

class PubNubPushNotificationSender extends AbstractPushNotificationSender
{
    /**
     * instance of push sender
     */
    protected static $instance = null;

    private PubNub $transport;

    private string $sdkLink;
    private string $publishKey;
    private string $subscribeKey;
    private string $uuid;

    private function __construct($params) {
        $this->publishKey = $params['NOTIFICATION_PUBLISH_KEY'] ?? null;
        $this->subscribeKey = $params['NOTIFICATION_SUBSCRIBE_KEY'] ?? null;
        $this->uuid = $params['NOTIFICATION_UUID'] ?? null;
        $this->sdkLink = $params['NOTIFICATION_SDK_LINK'] ?? null;

        $secretKey = $params['NOTIFICATION_SECRET_KEY'] ?? null;

        if(!$this->publishKey || !$this->subscribeKey || !$secretKey || !$this->uuid || !$this->sdkLink) {
            throw new \Exception('pub nub notification configs not exist');
        }

        $pnconf = new PNConfiguration();
        $pnconf->setSubscribeKey($this->subscribeKey);
        $pnconf->setPublishKey($this->publishKey);
        $pnconf->setSecretKey($secretKey);
        $pnconf->setUuid($this->uuid);

        $pubnub = new PubNub($pnconf);


        $this->transport = $pubnub;
    }


    /**
     * Returns an singleton instance of this class
     *
     * @return PubNubPushNotificationSender
     */
    public static function getInstance(array $params = []): PubNubPushNotificationSender {
        if (self::$instance === null){
            self::$instance = new self($params);
        }
        return self::$instance;
    }


    /**
     * returns sdk download link
     *
     * @return string
     */
    public function getSdk() :string {
        return $this->sdkLink;
    }

    /**
     * returns sdk download link
     *
     * @return string
     */
    public function getPublishKey() :string {
        return $this->publishKey;
    }

    /**
     * returns sdk download link
     *
     * @return string
     */
    public function getSubscribeKey() :string {
        return $this->subscribeKey;
    }


    /**
     * returns sdk download link
     *
     * @return string
     */
    public function getUuid() :string {
        return $this->uuid;
    }


    /**
     * send message to channel
     *
     * @param string $channel
     * @param string|array $message
     */
    public function sendNotification(string $channel, $message) {
        $this->transport->publish()->channel($channel)->message($message)->sync();
    }
    
    
    
}
