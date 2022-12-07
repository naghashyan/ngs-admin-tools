<?php

namespace ngs\AdminTools\dal\dto;

use ngs\dal\dto\AbstractDto;

/**
 * Class NgsRuleDto
 *
 * this class used to keep rules, by which can be done following:
 * 1. get items by rule
 * 2. verify item by rule
 * 3. do some actions if item verified by rule
 *
 * @package ngs\AdminTools\dal\dto
 */
class NgsRuleDto extends AbstractDto
{
    protected $id;
    protected $name;
    protected $ruleName;
    protected $itemId;
    protected $priority;
    protected $isHighPriority;
    protected $conditions;
    protected $actions;

    /** @var array $additionalWhereConditions */
    private $additionalWhereConditions = [];

    /** @var string */
    public string $tableName = 'ngs_rules';

    // Map of DB value to Field value
    protected $mapArray = [
        'id' => 'id',
        'name' => 'name',
        'rule_name' => 'ruleName',
        'item_id' => 'itemId',
        'priority' => 'priority',
        'is_high_priority' => 'isHighPriority',
        'conditions' => 'conditions',
        'actions' => 'actions'
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
    public function getRuleName()
    {
        return $this->ruleName;
    }

    /**
     * @param mixed $ruleName
     */
    public function setRuleName($ruleName): void
    {
        $this->ruleName = $ruleName;
    }

    /**
     * @return mixed
     */
    public function getItemId()
    {
        return $this->itemId;
    }

    /**
     * @param mixed $itemId
     */
    public function setItemId($itemId): void
    {
        $this->itemId = $itemId;
    }

    /**
     * @return mixed
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * @param mixed $priority
     */
    public function setPriority($priority): void
    {
        $this->priority = $priority;
    }

    /**
     * @return mixed
     */
    public function getIsHighPriority()
    {
        return $this->isHighPriority;
    }

    /**
     * @param mixed $isHighPriority
     */
    public function setIsHighPriority($isHighPriority): void
    {
        $this->isHighPriority = $isHighPriority;
    }

    /**
     * @return mixed
     */
    public function getConditions()
    {
        return $this->conditions;
    }

    /**
     * @param mixed $conditions
     */
    public function setConditions($conditions): void
    {
        $this->conditions = $conditions;
    }

    /**
     * @return mixed
     */
    public function getActions()
    {
        return $this->actions;
    }

    /**
     * @param mixed $actions
     */
    public function setActions($actions): void
    {
        $this->actions = $actions;
    }

    /**
     * @return ?array
     */
    public function getAdditionalWhereConditions(): ?array
    {
        return $this->additionalWhereConditions;
    }

    /**
     * @param string $condition
     */
    public function addWhereCondition(string $condition): void
    {
        if($condition) {
            $this->additionalWhereConditions[] = $condition;
        }
    }
}