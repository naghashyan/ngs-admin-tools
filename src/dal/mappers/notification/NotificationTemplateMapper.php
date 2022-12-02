<?php


namespace ngs\AdminTools\dal\mappers\notification;

use ngs\AdminTools\dal\dto\notification\NotificationTemplateDto;
use ngs\dal\dto\AbstractDto;
use ngs\dal\mappers\AbstractMysqlMapper;

class NotificationTemplateMapper extends AbstractMysqlMapper
{

    //! Private members.

    private static ?self $instance = null;
    public string $tableName = 'ngs_notification_templates';

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
     * @return NotificationTemplateMapper Object
     */
    public static function getInstance(): NotificationTemplateMapper
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }


    /**
     * @return NotificationTemplateDto
     */
    public function createDto() :AbstractDto
    {
        return new NotificationTemplateDto();
    }


    private $GET_NOTIFICATION_TEMPLATES_BY_EVENT = "SELECT * FROM %s WHERE `event_name` = :eventName";

    /**
     * @param string $eventName
     * @return NotificationTemplateDto[]
     * @throws \ngs\exceptions\DebugException
     */
    public function getEventNotificationTempaltes(string $eventName) :array
    {
        $sqlQuery = sprintf($this->GET_NOTIFICATION_TEMPLATES_BY_EVENT, $this->getTableName());
        return $this->fetchRows($sqlQuery, ['eventName' => $eventName]);
    }


    private $GET_NOTIFICATION_TEMPLATES_COUNT_BY_EVENT = "SELECT COUNT(*) as count FROM %s WHERE `event_name` = :eventName";

    /**
     * @param int $userId
     * @return int
     * @throws \ngs\exceptions\DebugException
     */
    public function getEventNotificationTempaltesCount(string $eventName) {
        $sqlQuery = sprintf($this->GET_NOTIFICATION_TEMPLATES_COUNT_BY_EVENT, $this->getTableName());
        $count = $this->fetchField($sqlQuery, 'count', ['eventName' => $eventName]);
        return (int) $count;
    }
}