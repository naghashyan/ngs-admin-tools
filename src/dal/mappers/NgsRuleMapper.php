<?php

/**
 *
 * NgsRuleMapper class is extended class from AbstractMysqlMapper.
 * It contatins all read and write functions which are working with ngs_rules table.
 *
 * @author Mikael Mkrtchyan
 * @site http://naghashyan.com
 * @mail mikael.mkrtchyan@naghashyan.com
 * @year 2021
 * @package ngs.AdminTools.dal.mappers
 * @version 1.0
 *
 */

namespace ngs\AdminTools\dal\mappers;

use ngs\AdminTools\dal\dto\NgsRuleDto;
use ngs\dal\dto\AbstractDto;
use ngs\dal\mappers\AbstractMysqlMapper;

class NgsRuleMapper extends AbstractMysqlMapper
{

    //! Private members.

    private static ?NgsRuleMapper $instance = null;

    /**
     * Returns an singleton instance of this class
     *
     * @return NgsRuleMapper Object
     */
    public static function getInstance(): NgsRuleMapper
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * returns related table name
     *
     * @return string
     */
    public function getTableName(): string
    {
        return "ngs_rules";
    }


    /**
     * returns related dto
     *
     * @return AbstractDto
     */
    public function createDto(): AbstractDto
    {
        return new NgsRuleDto();
    }


    /**
     * returns primary key column from table
     *
     * @return string
     */
    public function getPKFieldName(): string {
        return "id";
    }


    /**
     * @var string
     */
    private $GET_RULE_BY_ID = 'SELECT * FROM %s WHERE `id` = :id';

    /**
     * returns rules by id
     *
     * @param int $id
     *
     * @return NgsRuleDto|null
     *
     * @throws \ngs\exceptions\DebugException
     */
    public function getRuleById(int $id) {
        $sqlQuery = sprintf($this->GET_RULE_BY_ID, $this->getTableName());
        return $this->fetchRow($sqlQuery, ['id' => $id]);
    }

    /**
     * @var string
     */
    private $GET_RULES_BY_IDS = 'SELECT * FROM %s WHERE `id` IN (%s)';

    /**
     * returns rules by ids
     *
     * @param array $ids
     */
    public function getRulesByIds(array $ids) {
        $inCondition = implode(",", $ids);
        $sqlQuery = sprintf($this->GET_RULES_BY_IDS, $this->getTableName(), $inCondition);
        return $this->fetchRows($sqlQuery, []);
    }


    /**
     * @var string
     */
    private $GET_CLASS_RULES_BY_RULE_NAME = 'SELECT * FROM %s WHERE `rule_name` = :ruleName AND item_id IS NULL ORDER BY `priority` ASC';

    /**
     * @var string
     */
    private $GET_OBJECT_RULES_BY_RULE_NAME = 'SELECT * FROM %s WHERE `rule_name` = :ruleName AND (item_id IS NULL OR item_id = :itemId) ORDER BY `priority` ASC';

    /**
     * returns rules by rule class name
     *
     * @param string $ruleName
     *
     * @return NgsRuleDto[]
     *
     * @throws \ngs\exceptions\DebugException
     */
    public function getRules(string $ruleName, ?int $itemId = null) {
        $params = ['ruleName' => $ruleName];
        if(!$itemId) {
            $sqlQuery = sprintf($this->GET_CLASS_RULES_BY_RULE_NAME, $this->getTableName());

        }
        else {
            $sqlQuery = sprintf($this->GET_OBJECT_RULES_BY_RULE_NAME, $this->getTableName());
            $params['itemId'] = $itemId;
        }

        return $this->fetchRows($sqlQuery, $params);
    }


    /**
     * @var string
     */
    private $GET_RULES_BY_RULE_NAME_AND_ITEM_ID = 'SELECT * FROM %s WHERE `rule_name` = :ruleName AND item_id = :itemId ORDER BY `priority` ASC';


    /**
     * returns rules by rule class name ad item id
     *
     * @param string $ruleName
     * @param int $itemId
     */
    public function getItemRules(string $ruleName, int $itemId) {
        $sqlQuery = sprintf($this->GET_RULES_BY_RULE_NAME_AND_ITEM_ID, $this->getTableName());
        return $this->fetchRows($sqlQuery, ['ruleName' => $ruleName, 'itemId' => $itemId]);
    }


    /**
     * @var string
     */
    private $GET_RULE_BY_RULE_NAME_AND_PRIORITY = 'SELECT * FROM %s WHERE `rule_name` = :ruleName AND `priority` = :priority';

    /**
     * returns rule by rule class name and priority
     *
     * @param string $ruleName
     * @param int $priority
     *
     * @return NgsRuleDto|null
     *
     * @throws \ngs\exceptions\DebugException
     */
    public function getRule(string $ruleName, int $priority) {
        $sqlQuery = sprintf($this->GET_RULE_BY_RULE_NAME_AND_PRIORITY, $this->getTableName());
        return $this->fetchRow($sqlQuery, ['ruleName' => $ruleName, 'priority' => $priority]);
    }


    /**
     * creates new rule
     *
     * @param string $ruleName
     * @param string $name
     * @param array $conditions
     * @param array $actions
     * @param int $priority
     * @param int|null $itemId
     *
     * @return NgsRuleDto|null
     *
     * @throws \ngs\exceptions\DebugException
     */
    public function createRule(string $ruleName, string $name, array $conditions, array $actions, int $priority, int $itemId = null) {
        $newRule = new NgsRuleDto();
        $newRule->setRuleName($ruleName);
        $newRule->setName($name);
        $newRule->setConditions(json_encode($conditions, JSON_UNESCAPED_UNICODE));
        $newRule->setActions(json_encode($actions, JSON_UNESCAPED_UNICODE));
        $newRule->setPriority($priority);
        if($itemId) {
            $newRule->setItemId($itemId);
        }

        $id = $this->insertDto($newRule);

        if($id) {
            $newRule->setId($id);
            return $newRule;
        }

        return null;
    }


    /**
     * returns associative array by sql request
     *
     * @param string $sql
     * @param array $params
     * @return |null
     */
    public function getData(string $sql, array $params = []) {
        try {
            return $this->fetchRows($sql, $params, ['cache' => false, 'ttl' => 3600, 'force' => false], true);
        }
        catch(\Exception $exp) {
            return null;
        }

    }
}
