<?php


namespace ngs\AdminTools\dal\mappers\notification;

use ngs\AdminTools\dal\dto\notification\NotificationTemplateGroupDto;
use ngs\dal\dto\AbstractDto;
use ngs\dal\mappers\AbstractMysqlMapper;

class NotificationTemplateGroupMapper extends AbstractMysqlMapper
{

    //! Private members.

    private static ?self $instance = null;
    public string $tableName = 'ngs_notification_template_groups';

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
     * @return self Object
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }


    /**
     * @return NotificationTemplateGroupDto
     */
    public function createDto() :AbstractDto
    {
        return new NotificationTemplateGroupDto();
    }


    private $GET_NOTIFICATION_TEMPLATE_GROUPS = "SELECT * FROM %s WHERE `notification_template_id` = :notificationId";

    public function getNotificatioTempalteGroups(int $notificationTemplateId) {
        $sqlQuery = sprintf($this->GET_NOTIFICATION_TEMPLATE_GROUPS, $this->getTableName());
        return $this->fetchRows($sqlQuery, ['notificationId' => $notificationTemplateId]);
    }
}