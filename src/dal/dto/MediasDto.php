<?php
/**
 * MediasDto dto class
 * setter and getter generator
 * for medias table
 * used to store all radio events in insore
 *
 * @author Mikael Mkrtchyan
 * @site http://naghashyan.com
 * @mail mikael.mkrtchyan@naghashyan.com
 * @year 2018
 * @package dal.dto.radio
 * @version 1.0
 *
 */

namespace ngs\AdminTools\dal\dto;

use ngs\AdminTools\dal\dto\AbstractCmsDto;

class MediasDto extends AbstractCmsDto
{

    protected string $tableName = 'medias';

    protected $id;
    protected $originalName;
    protected $description;
    protected $type;
    protected $extension;
    protected $objectType;
    protected $objectKey;
    protected $isMain;
    protected $created;
    protected $updated;


    protected array $mapArray = [
        'id' => ['type' => 'number'],
        'original_name' => ['type' => 'text'],
        'description' => ['type' => 'text'],
        'type' => ['type' => 'text'],
        'extension' => ['type' => 'text'],
        'object_type' => ['type' => 'text'],
        'object_key' => ['type' => 'number'],
        'is_main' => ['type' => 'checkbox'],
        'created' => ['type' => 'date'],
        'updated' => ['type' => 'date']
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
    public function getOriginalName()
    {
        return $this->originalName;
    }

    /**
     * @param mixed $originalName
     */
    public function setOriginalName($originalName): void
    {
        $this->originalName = $originalName;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     */
    public function setDescription($description): void
    {
        $this->description = $description;
    }


    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     */
    public function setType($type): void
    {
        $this->type = $type;
    }

    /**
     * @return mixed
     */
    public function getExtension()
    {
        return $this->extension;
    }

    /**
     * @param mixed $extension
     */
    public function setExtension($extension): void
    {
        $this->extension = $extension;
    }

    /**
     * @return mixed
     */
    public function getObjectType()
    {
        return $this->objectType;
    }

    /**
     * @param mixed $objectType
     */
    public function setObjectType($objectType): void
    {
        $this->objectType = $objectType;
    }

    /**
     * @return mixed
     */
    public function getObjectKey()
    {
        return $this->objectKey;
    }

    /**
     * @param mixed $objectKey
     */
    public function setObjectKey($objectKey): void
    {
        $this->objectKey = $objectKey;
    }

    /**
     * @return mixed
     */
    public function getIsMain()
    {
        return $this->isMain;
    }

    /**
     * @param mixed $isMain
     */
    public function setIsMain($isMain): void
    {
        $this->isMain = $isMain;
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


}

