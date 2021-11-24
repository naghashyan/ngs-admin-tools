<?php


namespace ngs\NgsAdminTools\dal\dto;

use ngs\dal\dto\AbstractDto;

class ExportTemplateDto extends AbstractDto
{



    protected $id;
    protected $name;
    protected $itemType;
    protected $fields;
    protected $userId;
    protected $updated;

    /** @var string */
    public $tableName = 'ngs_saved_export_templates';

    // Map of DB value to Field value
    protected $mapArray = [
        'id' => 'id',
        'name' => 'name',
        'item_type' => 'itemType',
        'fields' => 'fields',
        'user_id' => 'userId',
        'created' => 'created',
        'updated' => 'updated'
    ];

    // returns map array
    public function getMapArray(): array
    {
        return $this->mapArray;
    }

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
    public function getItemType()
    {
        return $this->itemType;
    }

    /**
     * @param mixed $itemType
     */
    public function setItemType($itemType): void
    {
        $this->itemType = $itemType;
    }

    /**
     * @return mixed
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * @param mixed $fields
     */
    public function setFields($fields): void
    {
        $this->fields = $fields;
    }

    /**
     * @return mixed
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @param mixed $userId
     */
    public function setUserId($userId): void
    {
        $this->userId = $userId;
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
}
