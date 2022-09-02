<?php

namespace ngs\AdminTools\dal\dto\notification;

use ngs\AdminTools\dal\dto\AbstractCmsDto;
use ngs\AdminTools\validators\ArrayValidator;
use ngs\AdminTools\validators\NumberValidator;
use ngs\AdminTools\validators\TextValidator;
use ngs\dal\dto\AbstractDto;

class NotificationTemplateGroupDto extends AbstractCmsDto
{

    /** @var string */
    public string $tableName = 'ngs_notification_template_groups';


    protected $id;
    protected $notificationTemplateId;
    protected $groupId;


    // Map of DB value to Field value
    protected array $mapArray = [
        'id' => ['type' => 'number', 'display_name' => 'ID', 'field_name' => 'id', 'visible' => false, 'sortable' => true, 'actions' => [], 'required_in' => []],
        'notification_template_id' => ['type' => 'number', 'validators' => [['class' => NumberValidator::class, 'is_required' => true]], 'actions' => [], 'required_in' => []],
        'group_id' => ['type' => 'number', 'validators' => [['class' => NumberValidator::class, 'is_required' => true]], 'actions' => [], 'required_in' => []]
    ];

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id): void
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getNotificationTemplateId()
    {
        return $this->notificationTemplateId;
    }

    /**
     * @param mixed $notificationTemplateId
     */
    public function setNotificationTemplateId($notificationTemplateId): void
    {
        $this->notificationTemplateId = $notificationTemplateId;
    }

    /**
     * @return mixed
     */
    public function getGroupId()
    {
        return $this->groupId;
    }

    /**
     * @param mixed $groupId
     */
    public function setGroupId($groupId): void
    {
        $this->groupId = $groupId;
    }
}