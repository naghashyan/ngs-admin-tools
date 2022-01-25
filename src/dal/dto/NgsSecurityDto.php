<?php
/**
 * NgsSecurityDto dto class
 *
 * @author Mikael Mkrtchyan
 * @site http://naghashyan.com
 * @mail mikael.mkrtchyan@naghashyan.com
 * @year 2020
 * @package ngs.AdminTools.dal.dto
 * @version 1.0.0
 *
 */

namespace ngs\AdminTools\dal\dto;

use ngs\dal\dto\AbstractDto;

class NgsSecurityDto extends AbstractDto
{

    /** @var array */
    protected $mapArray = [
        'id' => 'id',
        'dto_name' => 'dtoName',
        'dto_display_name' => 'dtoDisplayName',
        'field_name' => 'fieldName',
        'field_display_name' => 'fieldDisplayName',
        'access_type' => 'accessType',
        'rule_type' => 'ruleType',
        'rule_value' => 'ruleValue',
        'update_date' => 'updatedDate'
    ];

    /**
     * DB(type="int", length="11", primary="true")
     * @var int $id
     */
    protected $id;

    /**
     * DB(type="varchar", length="255")
     * @var string $dtoName
     */
    protected $dtoName;

    /**
     * DB(type="varchar", length="255")
     * @var string $fieldName
     */
    protected $fieldName;


    /**
     * DB(type="varchar", length="255")
     * @var string $fieldDisplayName
     */
    protected $fieldDisplayName;


    /**
     * DB(type="varchar", length="255")
     * @var string $dtoDisplayName
     */
    protected $dtoDisplayName;


    /**
     * DB(type="text")
     *
     * @var string $accessType
     */
    protected $accessType;

    /**
     * DB(type="text")
     *
     * @var string $ruleType
     */
    protected $ruleType;

    /**
     * DB(type="int", length="11")
     *
     * @var string $ruleType
     */
    protected $ruleValue;

    /**
     * DB(type="datetime")
     * @var \DateTime $updatedDate
     */
    protected $updatedDate;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getDtoName(): string
    {
        return $this->dtoName;
    }

    /**
     * @param string $dtoName
     */
    public function setDtoName(string $dtoName): void
    {
        $this->dtoName = $dtoName;
    }

    /**
     * @return string
     */
    public function getFieldName(): string
    {
        return $this->fieldName;
    }

    /**
     * @param string $fieldName
     */
    public function setFieldName(string $fieldName): void
    {
        $this->fieldName = $fieldName;
    }

    /**
     * @return string
     */
    public function getFieldDisplayName(): string
    {
        return $this->fieldDisplayName;
    }

    /**
     * @param string $fieldDisplayName
     */
    public function setFieldDisplayName(string $fieldDisplayName): void
    {
        $this->fieldDisplayName = $fieldDisplayName;
    }

    /**
     * @return string
     */
    public function getDtoDisplayName(): string
    {
        return $this->dtoDisplayName;
    }

    /**
     * @param string $dtoDisplayName
     */
    public function setDtoDisplayName(string $dtoDisplayName): void
    {
        $this->dtoDisplayName = $dtoDisplayName;
    }


    /**
     * @return string
     */
    public function getAccessType(): string
    {
        return $this->accessType;
    }

    /**
     * @param string $accessType
     */
    public function setAccessType(string $accessType): void
    {
        $this->accessType = $accessType;
    }

    /**
     * @return string
     */
    public function getRuleType(): string
    {
        return $this->ruleType;
    }

    /**
     * @param string $ruleType
     */
    public function setRuleType(string $ruleType): void
    {
        $this->ruleType = $ruleType;
    }

    /**
     * @return string
     */
    public function getRuleValue(): string
    {
        return $this->ruleValue;
    }

    /**
     * @param string $ruleValue
     */
    public function setRuleValue(string $ruleValue): void
    {
        $this->ruleValue = $ruleValue;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedDate(): \DateTime
    {
        return $this->updatedDate;
    }

    /**
     * @param \DateTime $updatedDate
     */
    public function setUpdatedDate(\DateTime $updatedDate): void
    {
        $this->updatedDate = $updatedDate;
    }

    /**
     * @return array|null
     */
    public function getMapArray() :?array {
        return $this->mapArray;
    }
}

