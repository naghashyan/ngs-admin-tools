<?php

namespace ngs\AdminTools\dal\dto\notification;

use ngs\AdminTools\dal\dto\AbstractCmsDto;
use ngs\AdminTools\validators\ArrayValidator;
use ngs\AdminTools\validators\NumberValidator;
use ngs\AdminTools\validators\TextValidator;
use ngs\dal\dto\AbstractDto;

class NotificationTemplateDto extends AbstractCmsDto
{

    /** @var string */
    public string $tableName = 'ngs_notification_templates';


    protected $id;
    protected $name;
    protected $template;
    protected $inEmail;
    protected $inSystem;
    protected $eventName;
    protected $created;
    protected $updated;
    protected $createdBy;
    protected $updatedBy;
    protected $createdByName;
    protected $updatedByName;


    // Map of DB value to Field value
    protected array $mapArray = [
        'id' => ['type' => 'number', 'display_name' => 'ID', 'field_name' => 'id', 'visible' => false, 'sortable' => true, 'actions' => [], 'required_in' => []],
        'name' => ['type' => 'text', 'searchable'=>true,'filterable' => true, 'help_text' => 'Event Name', 'validators' => [['class' => TextValidator::class, 'is_required' => true]], 'actions' => ['add', 'edit'], 'required_in' => []],
        'template' => ['type' => 'long_text','searchable'=>true,'filterable' => true, 'help_text' => 'Notification Template', 'validators' => [['class' => TextValidator::class, 'is_required' => true]], 'actions' => ['add', 'edit'], 'required_in' => []],
        'in_email' => ['type' => 'checkbox','filterable' => true, 'help_text' => 'Notify Method', 'validators' => [['class' => TextValidator::class]], 'actions' => ['add', 'edit'], 'required_in' => []],
        'in_system' => ['type' => 'checkbox','filterable' => true,'help_text' => 'Notify Method',  'validators' => [['class' => TextValidator::class]], 'actions' => ['add', 'edit'], 'required_in' => []],
        'event_name' => ['type' => 'select','help_text' => 'Subscribed Event name','display_name'=>'Event to subscribe', 'validators' => [['class' => TextValidator::class, 'is_required' => true]], 'actions' => ['add', 'edit'], 'required_in' => []],
        'groups' => ['type' => 'select','filterable' => true,'display_name'=>'User Group', 'help_text' => 'User Groups That Should Be Notified', 'validators' => [['class' => TextValidator::class, 'is_required' => true]], 'actions' => ['add', 'edit'], 'required_in' => []],
        'created' => ['type' => 'date', 'security_configurable' => false, 'filterable' => true, 'display_name' => 'Created', 'field_name' => 'created', 'sortable' => true, 'visible' => true, 'actions' => []],
        'updated' => ['type' => 'date', 'security_configurable' => false, 'filterable' => true,'display_name' => 'Updated', 'field_name' => 'updated', 'sortable' => true, 'visible' => false, 'actions' => []],
        'created_by' => ['type' => 'number', 'security_configurable' => false, 'field_name' => 'createdBy', 'visible' => false, 'sortable' => true, 'actions' => [], 'required_in' => []],
        'updated_by' => ['type' => 'number', 'security_configurable' => false, 'field_name' => 'updatedBy', 'visible' => false, 'sortable' => false, 'actions' => [], 'required_in' => []],
        'created_by_name' => ['type' => 'text', 'security_configurable' => false, 'display_name' => 'Created By', 'field_name' => 'createdByName', 'visible' => true, 'sortable' => true, 'actions' => [], 'required_in' => []],
        'updated_by_name' => ['type' => 'text', 'security_configurable' => false, 'display_name' => 'Updated By', 'field_name' => 'updatedByName', 'visible' => true, 'sortable' => true, 'actions' => [], 'required_in' => []]
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
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name): void
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @param mixed $template
     */
    public function setTemplate($template): void
    {
        $this->template = $template;
    }

    /**
     * @return mixed
     */
    public function getInEmail()
    {
        return $this->inEmail;
    }

    /**
     * @param mixed $inEmail
     */
    public function setInEmail($inEmail): void
    {
        $this->inEmail = $inEmail;
    }

    /**
     * @return mixed
     */
    public function getInSystem()
    {
        return $this->inSystem;
    }

    /**
     * @param mixed $inSystem
     */
    public function setInSystem($inSystem): void
    {
        $this->inSystem = $inSystem;
    }

    /**
     * @return mixed
     */
    public function getEventName()
    {
        return $this->eventName;
    }

    /**
     * @param mixed $eventName
     */
    public function setEventName($eventName): void
    {
        $this->eventName = $eventName;
    }

    /**
     * @return mixed
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * @param mixed $created
     */
    public function setCreated($created): void
    {
        $this->created = $created;
    }

    /**
     * @return mixed
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * @param mixed $updated
     */
    public function setUpdated($updated): void
    {
        $this->updated = $updated;
    }

    /**
     * @return mixed
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * @param mixed $createdBy
     */
    public function setCreatedBy($createdBy): void
    {
        $this->createdBy = $createdBy;
    }

    /**
     * @return mixed
     */
    public function getUpdatedBy()
    {
        return $this->updatedBy;
    }

    /**
     * @param mixed $updatedBy
     */
    public function setUpdatedBy($updatedBy): void
    {
        $this->updatedBy = $updatedBy;
    }

    /**
     * @return mixed
     */
    public function getCreatedByName()
    {
        return $this->createdByName;
    }

    /**
     * @param mixed $createdByName
     */
    public function setCreatedByName($createdByName): void
    {
        $this->createdByName = $createdByName;
    }

    /**
     * @return mixed
     */
    public function getUpdatedByName()
    {
        return $this->updatedByName;
    }

    /**
     * @param mixed $updatedByName
     */
    public function setUpdatedByName($updatedByName): void
    {
        $this->updatedByName = $updatedByName;
    }
}