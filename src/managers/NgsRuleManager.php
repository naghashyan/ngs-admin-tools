<?php
/**
 * LogManager class provides all functions for creating,
 * and working with logs.
 *
 * @author Mikael Mkrtchyan
 * @site http://naghashyan.com
 * @mail mikael.mkrtchyan@naghashyan.com
 * @year 2019
 * @package ngs.AdminTools.managers
 * @version 6.5.0
 */

namespace ngs\AdminTools\managers;

use ngs\AbstractManager;
use ngs\AdminTools\dal\dto\AbstractCmsDto;
use ngs\AdminTools\dal\dto\NgsRuleDto;
use ngs\AdminTools\dal\mappers\NgsRuleMapper;
use ngs\AdminTools\util\ArrayUtil;
use ngs\AdminTools\util\MathUtil;
use ngs\AdminTools\util\StringUtil;


class NgsRuleManager extends AbstractManager
{

    private static $epsilion = 0.001;
    /** @var array $ruleClasses */
    private $ruleClasses = [];
    /** @var array $itemsByRules */
    private array $itemsByRules = [];
    /**
     * @var $instance
     */
    public static $instance;

    /**
     * Returns an singleton instance of this class
     *
     * @return NgsRuleManager
     */
    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new NgsRuleManager();
        }
        return self::$instance;
    }


    /**
     * modifies filter array
     *
     * @param array $filter
     * @param AbstractCmsDto $dto
     * @param string $field
     * @param string $fieldType
     * @param string $condition
     * @param mixed $value
     */
    public static function addConditionToFilterArray(array $filter, AbstractCmsDto $dto, string $field, string $fieldType, string $condition, $value)
    {
        if (!isset($filter['and'])) {
            $filter['and'] = [];
        }
        $filter['and'][] = [
            'fieldName' => $dto->getTableName() . '.' . $field,
            'conditionType' => $fieldType,
            'conditionValue' => $condition,
            'searchValue' => $value
        ];

        return $filter;
    }


    /**
     * modifies filter array
     *
     * @param array $filter
     * @param AbstractCmsDto $dto
     * @param string $field
     * @param string $fieldType
     * @param string $condition
     * @param string $value
     */
    public static function addOrConditionsToFilterArray(array $filter, array $conditions)
    {
        $result = ['or' => []];

        foreach($conditions as $condition) {
            $result['or'][] = [
                'fieldName' => $condition['dto']->getTableName() . '.' . $condition['field'],
                'conditionType' => $condition['fieldType'],
                'conditionValue' => $condition['condition'],
                'searchValue' => $condition['value']
            ];
        }

        $filter['and'][] = $result;
        return $filter;
    }


    /**
     * @param AbstractCmsDto $itemDto
     * @return AbstractCmsDto
     * @throws \Exception
     */
    public function modifyDtoByRules($itemDto)
    {
        $rules = [];

        $cmsMapArray = $itemDto->getCmsMapArray();
        foreach ($cmsMapArray as $dbField => $info) {
            if ($info['rule'] && !in_array($info['rule'], $rules)) {
                $rules[] = $info['rule'];
            }
        }

        return $this->modifyDtoByGivenRules($itemDto, $rules);
    }


    /**
     * modify dto by provided rules
     *
     * @param $itemDto
     * @param array $rules
     * @return AbstractCmsDto
     * @throws \Exception
     */
    public function modifyDtoByGivenRules($itemDto, array $rules)
    {
        foreach ($rules as $rule) {
            $updateProductRules = $this->getRules($rule, $itemDto->getId());
            if ($updateProductRules) {
                $itemDto = $this->executeActions($updateProductRules, $itemDto);
            }
        }

        return $itemDto;
    }


    /**
     *
     * returns rule class info
     *
     * @param NgsRuleDto $rule
     * @return array
     *
     * @throws \Exception
     */
    public function getRuleClassInfo(NgsRuleDto $rule): array
    {

        $ruleName = $rule->getRuleName();
        return $this->getRuleClassInfoByName($ruleName);
    }


    /**
     * returns rule class info by rule name
     *
     * @param string $ruleName
     * @return mixed
     * @throws \Exception
     */
    public function getRuleClassInfoByName(string $ruleName)
    {
        if (isset($this->ruleClasses[$ruleName])) {
            return $this->ruleClasses[$ruleName];
        }

        $ruleJsonsDir = NGS()->getDataDir('admin') . '/rules';
        if (!is_dir($ruleJsonsDir)) {
            throw new \Exception('rules folder not defined');
        }

        $ruleJsonPath = $ruleJsonsDir . '/' . $ruleName . '.json';
        if (!is_file($ruleJsonPath)) {
            throw new \Exception($ruleName . '.json not found in folder ' . $ruleJsonsDir);
        }

        $ruleClassInfo = json_decode(file_get_contents($ruleJsonPath), true);

        $this->ruleClasses[$ruleName] = $ruleClassInfo;

        return $ruleClassInfo;
    }


    /**
     * removes rule
     *
     * @param $ruleId
     * @return bool
     * @throws \ngs\exceptions\DebugException
     */
    public function deleteRule($ruleId)
    {
        try {
            $ruleMapper = NgsRuleMapper::getInstance();
            $result = $ruleMapper->deleteByPK($ruleId);

            return !!$result;
        } catch (\Exception $exp) {
            return false;
        }

    }


    /**
     * removes rules
     *
     * @param array $ruleIds
     * @return bool
     * @throws \ngs\exceptions\DebugException
     */
    public function deleteRules(array $ruleIds)
    {
        try {
            $ruleMapper = NgsRuleMapper::getInstance();
            $result = $ruleMapper->deleteByPKs($ruleIds);
            return !!$result;
        } catch (\Exception $exp) {
            return false;
        }

    }


    /**
     *
     * returns where condition
     *
     * @param NgsRuleDto $rule
     * @param string $itemClass
     * @param string $temField
     * @param string $condition
     * @param mixed $value
     *
     * @return string
     *
     * @throws \Exception
     */
    public function getWhereCondition(NgsRuleDto $rule, string $itemClass, string $temField, string $condition, $value)
    {
        $ruleClassInfo = $this->getRuleClassInfo($rule);


        if ($ruleClassInfo['dtoClass'] === $itemClass) {
            $mainTableAlias = $this->getMainTableAlias($ruleClassInfo);
            return '(' . $mainTableAlias . '.' . $temField . ' ' . $condition . ' ' . $value . ')';
        } else {
            $relations = isset($ruleClassInfo['relations']) ? $ruleClassInfo['relations'] : [];
            foreach ($relations as $relation) {
                if ($relation['dtoClass'] === $itemClass) {
                    /** @var AbstractCmsDto $relativeDto */
                    $relativeDto = new $relation['dtoClass'];
                    $relativeTable = $relativeDto->getTableName();

                    $relativeTableAlias = isset($relation['alias']) ? $relation['alias'] : $relativeTable;
                    return '(' . $relativeTableAlias . '.' . $temField . ' ' . $condition . ' ' . $value . ')';
                }
            }
        }

        return "";
    }


    /**
     * returns sql where condition from rule conditions
     *
     * @param NgsRuleDto $rule
     * @param bool $withTableNamesInColumn
     *
     * @return string
     *
     * @throws \Exception
     */
    public function getSqlConditionFromRule(NgsRuleDto $rule, bool $withTableNamesInColumn = true): string
    {
        $ruleClassInfo = $this->getRuleClassInfo($rule);
        return $this->getSqlRequestFromRules($ruleClassInfo, [$rule], $withTableNamesInColumn);
    }


    /**
     * returns sql select count where condition from rule conditions
     *
     * @param NgsRuleDto $rule
     * @param bool $withTableNamesInColumn
     *
     * @return string
     *
     * @throws \Exception
     */
    public function getSqlCountConditionFromRule(NgsRuleDto $rule, bool $withTableNamesInColumn = true): string
    {
        $ruleClassInfo = $this->getRuleClassInfo($rule);
        return $this->getSqlRequestFromRules($ruleClassInfo, [$rule], $withTableNamesInColumn, true);
    }


    /**
     * returns sql where condition from rules conditions
     *
     * @param NgsRuleDto[] $rules
     * @param bool $withTableNamesInColumn
     *
     * @return string
     *
     * @throws \Exception
     */
    public function getSqlConditionFromRules($rules, bool $withTableNamesInColumn = true): string
    {
        $ruleClassInfo = $this->getRuleClassInfo($rules[0]);
        return $this->getSqlRequestFromRules($ruleClassInfo, $rules, $withTableNamesInColumn);
    }


    /**
     * get item data by rule
     *
     * @param AbstractCmsDto $item
     * @param NgsRuleDto $rule
     *
     * @return array|null
     */
    public function getItemDataByRule($item, $rule): array
    {
        $ruleMapper = NgsRuleMapper::getInstance();

        $ngsRuleManager = NgsRuleManager::getInstance();
        $rule->addWhereCondition($ngsRuleManager->getWhereCondition($rule, get_class($item), 'id', '=', $item->getId()));
        $sqlCondition = $ngsRuleManager->getSqlConditionFromRule($rule);

        $result = $ruleMapper->getData($sqlCondition);
        if ($result) {
            return $result[0];
        }
        return [];
    }


    /**
     * execute rules actions on item,
     * item will not be updated in DB, only dto will be modified
     *
     * @param $rules
     * @param AbstractCmsDto $itemDto
     * @return AbstractCmsDto
     * @throws \Exception
     */
    public function executeActions($rules, AbstractCmsDto $itemDto)
    {

        if (!$rules) {
            return $itemDto;
        }


        $rules = $this->prioritizeRules($rules, $itemDto);
        foreach ($rules as $updateProductRule) {

            $itemDto = $this->executeAction($updateProductRule, $itemDto);
        }

        return $itemDto;
    }


    /**
     *
     * execute rule actions on item,
     * item will not be updated in DB, only dto will be modified
     *
     *
     * @param NgsRuleDto $rule
     * @param AbstractCmsDto $item
     *
     * @return AbstractCmsDto
     *
     * @throws \Exception
     */
    public function executeAction(NgsRuleDto $rule, $item)
    {

        $actions = $rule->getActions();
        $actions = json_decode($actions, true);
        $mapArray = $item->getCmsMapArray();
        $data = $this->getItemDataByRule($item, $rule);

        if (!$data) {
            return $item;
        }

        foreach ($actions as $action) {
            foreach ($action as $property => $setValue) {
                $type = $mapArray[$property]['type'];
                $isRelative = isset($mapArray[$property]['relative']) && $mapArray[$property]['relative'];
                $setterMethod = StringUtil::getSetterByDbName($property);
                $actionType = $this->getActionTypeByFieldType($type, $isRelative);

                if ($actionType === 'assign') {
                    $item->$setterMethod($setValue);
                } else if ($actionType === 'formula') {
                    try {
                        $item->$setterMethod(MathUtil::getValueByFormula($setValue, $data));
                    } catch (\Exception $exp) {
                        //TODO: handle formula issue
                    }

                } else {
                    //TODO: handle relative fields assigment
                }
            }
        }

        return $item;
    }


    /**
     * creates new rule by given data, if priority not specified will set last one  + 1
     *
     * @param string $ruleName
     * @param string $name
     * @param array $conditions
     * @param array $actions
     * @param int|null $priority
     * @param int|null $itemId
     *
     * @return NgsRuleDto
     *
     * @throws \ngs\exceptions\DebugException|\Exception
     */
    public function createRule(string $ruleName, string $name, array $conditions, array $actions, int $priority = null, int $itemId = null)
    {
        $ruleMapper = NgsRuleMapper::getInstance();
        if ($itemId) {
            $rules = $this->getItemRules($ruleName, $itemId);
        } else {
            $rules = $this->getRules($ruleName);
        }

        foreach ($rules as $rule) {
            if ($rule->getName() === $name) {
                throw new \Exception('rule with name ' . $name . ' already exists');
            }
        }
        $rulesCount = count($rules);

        if ($priority) {
            foreach ($rules as $rule) {
                if ($rule->getPriority() >= $priority) {
                    $rule->setPriority($priority);
                    $ruleMapper->updateByPK($rule);
                }
            }
        } else if ($rulesCount) {
            //if priority not specified get last rule priority + 1, they are ordered by priority ASC
            $lastRule = $rules[$rulesCount - 1];
            $priority = $lastRule->getPriority() + 1;
        } else {
            $priority = 1;
        }

        $newRule = $ruleMapper->createRule($ruleName, $name, $conditions, $actions, $priority, $itemId);

        if (!$newRule) {
            throw new \Exception('new rule creation failed');
        }

        return $newRule;
    }


    /**
     * update rule in DB
     *
     * @param NgsRuleDto $ruleDto
     * @return int|null
     * @throws \ngs\exceptions\DebugException
     */
    public function updateRule(NgsRuleDto $ruleDto)
    {
        //TODO: handle priority change
        $ruleMapper = NgsRuleMapper::getInstance();
        return $ruleMapper->updateByPK($ruleDto);
    }


    /**
     * returns rule by rule name and priority
     *
     * @param string $ruleName
     * @param int $priority
     * @return NgsRuleDto|null
     */
    public function getRule(string $ruleName, int $priority)
    {
        try {
            $ruleMapper = NgsRuleMapper::getInstance();
            $rule = $ruleMapper->getRule($ruleName, $priority);

            return $rule;
        } catch (\Exception $exp) {
            return null;
        }
    }


    /**
     * returns rule by id
     *
     * @param int $id
     *
     * @return NgsRuleDto|null
     */
    public function getRuleById(int $id)
    {
        try {
            $ruleMapper = NgsRuleMapper::getInstance();
            $rule = $ruleMapper->getRuleById($id);

            return $rule;
        } catch (\Exception $exp) {
            return null;
        }
    }

    /**
     * returns rules by ids
     *
     * @param int $id
     *
     * @return NgsRuleDto[]
     */
    public function getRulesByIds(array $ids)
    {
        try {
            if (!$ids) {
                return [];
            }
            $ruleMapper = NgsRuleMapper::getInstance();
            $rule = $ruleMapper->getRulesByIds($ids);

            return $rule;
        } catch (\Exception $exp) {
            return [];
        }
    }


    /**
     * returns rule by rule name
     *
     * @param string $ruleName
     * @param int $itemId
     *
     * @return NgsRuleDto[]
     */
    public function getRules(string $ruleName, ?int $itemId = null)
    {
        try {
            $ruleMapper = NgsRuleMapper::getInstance();
            $rules = $ruleMapper->getRules($ruleName, $itemId);

            return $rules;
        } catch (\Exception $exp) {
            return [];
        }
    }


    /**
     * returns rule by rule name
     *
     * @param string $ruleName
     * @param int $itemId
     *
     * @return NgsRuleDto[]
     */
    public function getItemRules(string $ruleName, int $itemId)
    {
        try {
            $ruleMapper = NgsRuleMapper::getInstance();
            $rules = $ruleMapper->getItemRules($ruleName, $itemId);

            return $rules;
        } catch (\Exception $exp) {
            return [];
        }
    }


    /**
     * returns possible filterable values
     *
     * @return array
     */
    public function getFilterValues(NgsRuleDto $rule): array
    {
        $ruleClassInfo = $this->getRuleClassInfo($rule);

        /** @var AbstractCmsDto $mainDto */
        $mainDto = new $ruleClassInfo['dtoClass'];
        $mainTableAlias = $this->getMainTableAlias($ruleClassInfo);

        $cmsMapArray = $mainDto->getCmsMapArray(true);

        $selectFields = $this->getDtoFieldsInfo($cmsMapArray, $mainTableAlias, $ruleClassInfo['selectFields'], true, true);
        $relations = isset($ruleClassInfo['relations']) ? $ruleClassInfo['relations'] : [];

        foreach ($relations as $relation) {
            /** @var AbstractCmsDto $relativeDto */
            $relativeDto = new $relation['dtoClass'];
            $relativeTable = $relativeDto->getTableName();
            $relativeTableAlias = isset($relation['alias']) ? $relation['alias'] : $relativeTable;
            $relativeCmsMapArray = $relativeDto->getCmsMapArray(true);

            $selectFields = array_merge($selectFields, $this->getDtoFieldsInfo($relativeCmsMapArray, $relativeTableAlias, $relation['selectFields'], true));
        }

        $result = [];
        $managerClass = $ruleClassInfo['managerClass'];
        /** @var AbstractCmsManager $manager */
        $manager = $managerClass::getInstance();
        $possibleValues = $manager->getSelectionPossibleValues($mainDto);

        foreach ($selectFields as $info) {
            $filterItem = [
                'id' => $info['key'],
                'value' => $info['display_name'],
                'type' => $info['type'],
                'is_main' => $info['is_main']
            ];

            if ($filterItem['type'] === 'select') {
                $filterItem['possible_values'] = ArrayUtil::getByMatchingKey($possibleValues, $filterItem['id']);
            }

            $result[] = $filterItem;
        }

        return $result;
    }


    /**
     * returns main dto table name
     *
     * @param NgsRuleDto $rule
     *
     * @return string
     *
     * @throws \Exception
     */
    public function getMainTableName(NgsRuleDto $rule)
    {
        $ruleClassInfo = $this->getRuleClassInfo($rule);
        return $this->getMainTableAlias($ruleClassInfo);
    }


    /**
     * returns sql query from rules
     *
     * @param array $ruleClassInfo
     * @param NgsRuleDto[] $rules
     * @param bool $withTableNamesInColumn
     * @param bool $count indicates if count should be selected
     *
     * @return string
     *
     * @throws \Exception
     */
    private function getSqlRequestFromRules(array $ruleClassInfo, $rules, bool $withTableNamesInColumn, bool $count = false): string
    {
        $result = "";
        $selectPart = $this->getSqlSelectPart($ruleClassInfo, $withTableNamesInColumn);
        $joinPart = $this->getSqlJoinsPart($ruleClassInfo);

        $ruleWhereConditions = [];
        $ruleHavingConditions = [];
        $additionalConditions = [];

        $functionalFields = $this->getRuleFunctionalFields($ruleClassInfo);

        foreach ($rules as $rule) {
            $conditions = $rule->getConditions();
            $conditions = json_decode($conditions, true);
            if ($conditions) {
                $filter = isset($conditions['filter']) ? $conditions['filter'] : $conditions;
                $dividedFilter = [];
                $search = isset($conditions['search']) ? $conditions['search'] : null;

                $whereCondition = $this->getWhereConditionFromRuleCondition(true, $functionalFields, $filter, $search);
                if($whereCondition) {
                    $ruleWhereConditions[] = "(" . $whereCondition . ")";
                }


                $havingCondition = $this->getWhereConditionFromRuleCondition(false, $functionalFields, $filter, $search);
                if($havingCondition) {
                    $ruleHavingConditions[] = "(" . $havingCondition . ")";
                }
            }

            $ruleAdditionalConditions = $rule->getAdditionalWhereConditions();

            if ($ruleAdditionalConditions) {
                if (count($ruleAdditionalConditions) === 1) {
                    $additionalConditions[] = $ruleAdditionalConditions[0];
                } else {
                    $additionalConditions[] = '(' . implode(' AND ', $ruleAdditionalConditions) . ')';
                }
            }
        }

        $groupBy = ' GROUP BY ' . $this->getMainTableAlias($ruleClassInfo) . '.id ';

        $whereConditions = "";
        if ($ruleWhereConditions) {
            $whereConditions .= " " . implode(" AND ", $ruleWhereConditions);
        } else {
            $whereConditions .= " 1=1 ";
        }

        $havingConditions = "";
        if ($ruleHavingConditions) {
            $havingConditions .= " " . implode(" AND ", $ruleHavingConditions);
        } else {
            $havingConditions .= " 1=1 ";
        }

        if ($additionalConditions) {
            if (count($additionalConditions) === 1) {
                $whereConditions .= ' AND ( ' . $additionalConditions[0] . ')';
            } else {
                $whereConditions .= ' AND ( ' . implode(" AND ", $additionalConditions) . ')';
            }
        }
        if (isset($ruleClassInfo['mandatoryWhereCondition']) && $ruleClassInfo['mandatoryWhereCondition']) {
            $whereConditions .= ' AND (' . $ruleClassInfo['mandatoryWhereCondition'] . ') ';
        }

        if($count) {
            $result = $this->filterFromSelectFieldsWichAreNotInWhereCondition($selectPart, $whereConditions, $havingConditions);
        }
        else {
            $result = $selectPart;
        }

        $result .= " " . $joinPart;
        $result .= " WHERE " . $whereConditions;
        $result .= $groupBy;
        $result .= " HAVING " . $havingConditions;

        if($count) {
            $result .= ') as `table_to_count`';
        }

        return $result;
    }


    /**
     * remove not necessary selects to calculate count
     *
     * @param string $selectPart
     * @param string $whereConditions
     * @param string $havingConditions
     */
    private function filterFromSelectFieldsWichAreNotInWhereCondition(string $selectPart, string $whereConditions, string $havingConditions) {
        $selectPart = substr($selectPart, 7); //remove first part SELECT
        $selectParts = explode('FROM', $selectPart); //remove FROM part, so left only selects
        $fromPart = $selectParts[count($selectParts) - 1];
        $selectPart = implode('FROM', array_slice($selectParts, 0, count($selectParts) - 1));
        $selectPart = trim($selectPart);
        $allSelects = explode(',', $selectPart);
        $whereConditions = str_replace('`', '', $whereConditions);
        $havingConditions = str_replace('`', '', $havingConditions);

        $leftSelects = [];
        foreach($allSelects as $selectIndex => $select) {
            $selectParts = explode("AS", $select);
            $usedInCondition = false;
            foreach($selectParts as $selectPart) {
                $selectPart = trim($selectPart);
                if(strpos($whereConditions, $selectPart) !== false || strpos($havingConditions, $selectPart) !== false) {
                    $usedInCondition = true;
                    break;
                }
            }
            if($usedInCondition || $selectIndex === 0) {
                $leftSelects[] = $select;
            }
        }

        $leftSelects = implode(',', $leftSelects);
        $leftSelects .= " FROM " . $fromPart;

        $leftSelects = 'SELECT COUNT(*) as count FROM (SELECT ' . $leftSelects;
        return $leftSelects;
    }


    /**
     * returns all alias of functional fields
     *
     * @param array $ruleClassInfo
     * @return array
     */
    private function getRuleFunctionalFields(array $ruleClassInfo) {
        $result = [];
        $mainSelectFields = $ruleClassInfo['selectFields'];

        if(is_array($mainSelectFields)) {
            foreach($mainSelectFields as $selectField) {
                if(isset($selectField['functional']) && $selectField['functional']) {
                    $result[] = $selectField['alias'];
                }
            }
        }


        if(isset($ruleClassInfo['relations'])) {
            $relations = $ruleClassInfo['relations'];
            foreach($relations as $relation) {
                $relationSelectFields = $relation['selectFields'];

                if(is_array($relationSelectFields)) {
                    foreach($relationSelectFields as $selectField) {
                        if(isset($selectField['functional']) && $selectField['functional']) {
                            $result[] = $selectField['alias'];
                        }
                    }
                }
            }
        }


        return $result;
    }


    /**
     * @param array $ruleClassInfo
     * @return mixed|string
     */
    private function getMainTableAlias(array $ruleClassInfo)
    {
        /** @var AbstractCmsDto $mainDto */
        $mainDto = new $ruleClassInfo['dtoClass'];
        $table = $mainDto->getTableName();
        $mainTableAlias = isset($ruleClassInfo['alias']) ? $ruleClassInfo['alias'] : $table;

        return $mainTableAlias;
    }


    /**
     * returns query select part
     *
     * @param array $ruleClassInfo
     * @param bool $withTableNamesInColumn
     * @param bool $count
     *
     * @return string
     */
    private function getSqlSelectPart(array $ruleClassInfo, bool $withTableNamesInColumn)
    {
        /** @var AbstractCmsDto $mainDto */
        $mainDto = new $ruleClassInfo['dtoClass'];
        $table = $mainDto->getTableName();
        $mainTableAlias = $this->getMainTableAlias($ruleClassInfo);

        $selectFields = $this->getSelectFieldsListWithAliases($ruleClassInfo, $withTableNamesInColumn);

        return "SELECT " . implode(", ", $selectFields) . ' FROM ' . $table . " AS " . $mainTableAlias;
    }


    /***
     * @param array $ruleClassInfo
     * @param bool $withTableNamesInColumn
     * @return array
     */
    private function getSelectFieldsListWithAliases(array $ruleClassInfo, bool $withTableNamesInColumn)
    {
        /** @var AbstractCmsDto $mainDto */
        $mainDto = new $ruleClassInfo['dtoClass'];
        $mainTableAlias = $this->getMainTableAlias($ruleClassInfo);

        $selectFields = [];

        if (is_array($ruleClassInfo['selectFields'])) {
            $selectFields = array_merge($selectFields, $this->getSelectFields($mainDto->getMapArray(), $mainTableAlias, $ruleClassInfo['selectFields'], $withTableNamesInColumn));
        } else {
            $selectFieldsList = explode(",", $ruleClassInfo['selectFields']);
            foreach ($selectFieldsList as $selectFieldItem) {
                if ($selectFieldItem === "*") {
                    $selectFields[] = $this->addAllTableColumnsInSelect($mainDto->getMapArray(true), $mainTableAlias, $withTableNamesInColumn);
                } else {
                    $selectFields[] = $selectFieldItem;
                }
            }
        }

        $relations = isset($ruleClassInfo['relations']) ? $ruleClassInfo['relations'] : [];
        foreach ($relations as $relation) {
            /** @var AbstractCmsDto $relativeDto */
            $relativeDto = new $relation['dtoClass'];
            $relativeTable = $relativeDto->getTableName();
            $relativeTableAlias = isset($relation['alias']) ? $relation['alias'] : $relativeTable;

            if (is_array($relation['selectFields'])) {
                $selectFields = array_merge($selectFields, $this->getSelectFields($relativeDto->getMapArray(), $relativeTableAlias, $relation['selectFields'], $withTableNamesInColumn));
            } else {
                $selectFieldsList = explode(",", $relation['selectFields']);
                foreach ($selectFieldsList as $selectFieldItem) {
                    if ($selectFieldItem === "*") {
                        $selectFields[] = $this->addAllTableColumnsInSelect($relativeDto->getMapArray(true), $relativeTableAlias, $withTableNamesInColumn);
                    } else {
                        $selectFields[] = $selectFieldItem;
                    }
                }
            }
        }

        return $selectFields;
    }


    /**
     * returns table all columns selection
     *
     * @param array $mapArray
     * @param string $tableName
     * @param bool $withTableNamesInColumn
     *
     * @return string
     */
    private function addAllTableColumnsInSelect(array $mapArray, string $tableName, bool $withTableNamesInColumn)
    {

        $result = [];
        $aliasPrefix = $withTableNamesInColumn ? $tableName . '.' : '';

        foreach ($mapArray as $dbField => $name) {
            $result[] = $tableName . '.' . $dbField . ' AS "' . $aliasPrefix . $name . '"';
        }

        return implode(", ", $result);
    }


    /**
     * creates selection array with field alias
     *
     * @param array $mapArray
     * @param string $tableAlias
     * @param array $selections
     * @param bool $withTableNamesInColumn
     *
     * @return array
     */
    private function getSelectFields(array $mapArray, string $tableAlias, array $selections, bool $withTableNamesInColumn): array
    {
        $result = [];

        $aliasPrefix = $withTableNamesInColumn ? $tableAlias . '.' : '';
        foreach ($selections as $selection) {
            if (is_array($selection)) {
                $dbField = $selection['field_name'];
                $fieldAlias = $selection['alias'];
                $finalAlias = $aliasPrefix ? '"' . $aliasPrefix . $fieldAlias . '"' : $fieldAlias;
                $fieldWithAlias = $dbField . ' AS ' . $finalAlias;
            } else {
                $finalAlias = $aliasPrefix ? '"' . $aliasPrefix . $mapArray[$selection] . '"' : $mapArray[$selection];
                $fieldWithAlias = $selection . ' AS ' . $finalAlias;
            }

            if(isset($selection['functional']) && $selection['functional']) {
                $result[] = $fieldWithAlias;
            }
            else {
                $result[] = $tableAlias . '.' . $fieldWithAlias;
            }
        }

        return $result;
    }


    /**
     * creates selection array with field alias
     *
     * @param array $mapArray
     * @param string $tableAlias
     * @param array|string $selections
     * @param bool $withTableNamesInColumn
     * @param bool $isMainDto
     *
     * @return array
     */
    private function getDtoFieldsInfo(array $cmsMapArray, string $tableAlias, $selections, bool $withTableNamesInColumn, bool $isMainDto = false): array
    {
        $result = [];
        $aliasPrefix = $withTableNamesInColumn ? $tableAlias . '.' : '';
        if (is_array($selections)) {
            foreach ($selections as $selection) {
                if (is_array($selection)) {
                    $dbField = $selection['field_name'];
                    $fieldAlias = $selection['alias'];

                    if(isset($selection['not_filterable']) && $selection['not_filterable']) {
                        continue;
                    }
                    $type = isset($selection['type']) ? $selection['type'] : $cmsMapArray[$dbField]['type'];
                    $displayName = isset($selection['display_name']) && $selection['display_name'] ? $selection['display_name'] : $fieldAlias;
                    $key = '`' . $tableAlias . '`.`' . $dbField . '`';
                    if(isset($selection['functional']) && $selection['functional']) {
                        $key = $fieldAlias;
                    }


                    $result[] = [
                        'key' => $key,
                        'display_name' => $displayName,
                        'type' => $type,
                        'is_main' => $isMainDto
                    ];
                }
            }
        } else {
            $selectFieldsList = explode(",", $selections);
            foreach ($selectFieldsList as $selectFieldItem) {
                if ($selectFieldItem === "*") {
                    foreach ($cmsMapArray as $dbField => $info) {
                        $type = $cmsMapArray[$dbField]['type'];
                        if(!isset($info['filterable']) || !$info['filterable']) {
                            continue;
                        }
                        $displayName = $info['display_name'];
                        $key = '`' . $tableAlias . '`.`' . $dbField . '`';
                        $result[] = [
                            'key' => $key,
                            'display_name' => $displayName,
                            'type' => $type,
                            'is_main' => $isMainDto
                        ];
                    }
                }
            }
        }


        return $result;
    }


    /**
     * returns joins part of sql query
     *
     * @param array $ruleClassInfo
     *
     * @return string
     *
     * @throws \Exception
     */
    private function getSqlJoinsPart(array $ruleClassInfo): string
    {
        $relations = isset($ruleClassInfo['relations']) ? $ruleClassInfo['relations'] : [];

        $result = [];
        $executedAlias = [];
        foreach ($relations as $relation) {
            /** @var AbstractCmsDto $relationalDto */
            $relationalDto = new $relation['dtoClass'];
            $table = $relationalDto->getTableName();

            $alias = isset($relation['alias']) ? $relation['alias'] : $table;
            if (in_array($alias, $executedAlias)) {
                throw new \Exception('alias duplication in relations');
            }
            $joinType = isset($relation['joinType']) ? strtoupper($relation['joinType']) : "INNER";


            $relationJoinQuery = $joinType . ' JOIN ' . $table . ' AS ' . $alias . ' ON ' . $relation['relQuery'];
            $result[] = $relationJoinQuery;
        }

        return implode(" ", $result);
    }


    /**
     * creates where condition using condition from the rule
     *
     * @param bool $forWhere
     * @param array $functionalFields
     * @param array $filter
     * @param array $search
     * @param null $operator
     *
     * @return string
     */
    private function getWhereConditionFromRuleCondition(bool $forWhere, array $functionalFields, ?array $filter, ?array $search, $operator = null)
    {
        $result = '';

        $searchCondition = '';
        if($search && $forWhere) {
            $searchCondition = $this->getSearchCondition($search);
        }

        if($filter) {
            if (isset($filter['or'])) {
                $filterCondition = $this->getWhereConditionFromRuleCondition($forWhere, $functionalFields, $filter['or'], null, 'or');
                if($filterCondition) {
                    $result = ' ( ' . $filterCondition . ' ) ';
                }
            } else if (isset($filter['and'])) {
                $filterCondition = $this->getWhereConditionFromRuleCondition($forWhere, $functionalFields, $filter['and'], null,  'and');
                if($filterCondition) {
                    $result = ' ( ' . $filterCondition . ' ) ';
                }
            } else if ($filter) {
                $delim = '';

                foreach ($filter as $filterItem) {
                    if (isset($filterItem['or'])) {
                        $filterCondition = $this->getWhereConditionFromRuleCondition($forWhere, $functionalFields, $filterItem, null, 'or');
                        if($filterCondition) {
                            $result .= $delim . $filterCondition;
                            $delim = ' ' . $operator . ' ';
                        }
                    } else if (isset($filterItem['and'])) {
                        $filterCondition = $this->getWhereConditionFromRuleCondition($forWhere, $functionalFields, $filterItem, null, 'and');
                        if($filterCondition) {
                            $result .= $delim . $filterCondition;
                            $delim = ' ' . $operator . ' ';
                        }
                    } else {
                        if(($forWhere && !in_array($filterItem['fieldName'], $functionalFields)) || (!$forWhere && in_array($filterItem['fieldName'], $functionalFields))) {
                            if ($result) {
                                $result .= $operator . ' ';
                            }

                            $result .= $this->getFieldName($filterItem);

                            if ($filterItem['conditionType'] == 'number') {

                                $result .= $this->getConditionAndSearchValueForNumberField($result, $filterItem);
                            } else {

                                $condition = $this->getConditionByType($filterItem);
                                if ($filterItem['conditionType'] === 'select' && isset($filterItem['searchValue']) &&
                                    ($filterItem['searchValue'] === 'is_null' || $filterItem['searchValue'] === 'is_not_null')) {
                                    $result .= $condition;
                                } else {
                                    $searchValue = $this->getSearchValueByType($filterItem);
                                    $result .= $condition . $searchValue;
                                }
                            }
                        }
                    }
                }
            }
        }

        $finalCondition = '';

        if($searchCondition) {
            $finalCondition = $searchCondition . ' ';
        }

        if ($result) {
            if ($finalCondition) {
                $finalCondition .= ' AND (' . $result . ')';
            } else {
                $finalCondition = $result;
            }
        }

        return $finalCondition;
    }


    /**
     * creates search condition
     *
     * @param $search
     * @return string
     */
    private function getSearchCondition($search) {
        $searchResult = '';
        if($search && $search['searchableFields'] && $search['searchKeys']) {
            $searchResult .= '(';
            $searchDelim = '';
            foreach($search['searchKeys'] as $searchKey) {
                foreach($search['searchableFields'] as $searchableField) {
                    $fullMatch = false;
                    if(is_array($searchableField)) {
                        $fullMatch = isset($searchableField['fullMatch']) && $searchableField['fullMatch'];
                        $searchableField = $searchableField['field'];
                    }
                    $searchResult .= $searchDelim;
                    //TODO: fix injection
                    if($fullMatch) {
                        $searchResult .= $searchableField . ' = "' . $searchKey . '"';
                    }
                    else {
                        $searchResult .= $searchableField . ' LIKE "%' . $searchKey . '%"';
                    }
                    if(!$searchDelim) {
                        $searchDelim = ' OR ';
                    }
                }
            }
            $searchResult .= ')';
        }

        return $searchResult;
    }


    /**
     * get field name
     *
     * @param $filterItem
     * @return mixed
     */
    private function getFieldName($filterItem) {
        if($filterItem['conditionType'] == 'date' && $filterItem['conditionValue'] === 'equal') {
            return "SUBSTRING(" . $filterItem['fieldName'] . ", 1, 10)"; //only date part
        }
        return $filterItem['fieldName'];
    }


    /**
     * number comparisons are not always correct because of float numbers
     * @param $result
     * @param $filterItem
     * @return string
     */
    private function getConditionAndSearchValueForNumberField($result, $filterItem)
    {
        switch ($filterItem['conditionValue']) {
            case 'equal' :
                return ' > ' . ($filterItem['searchValue'] - self::$epsilion) . ' AND ' . $result . ' < ' . ($filterItem['searchValue'] + self::$epsilion) . ' ';
            case 'greater' :
                return ' - ' . self::$epsilion . ' > ' . $filterItem['searchValue'] . ' ';
            case 'less' :
                return ' + ' . self::$epsilion . ' < ' . $filterItem['searchValue'] . ' ';
            case 'greater_or_equal' :
                return ' - ' . self::$epsilion . ' > ' . $filterItem['searchValue'] . ' OR (' .  $result . ' > ' . ($filterItem['searchValue'] - self::$epsilion) . ' AND ' . $result . ' < ' . ($filterItem['searchValue'] + self::$epsilion) . ' ) ';
            case 'less_or_equal' :
                return ' + ' . self::$epsilion . ' < ' . $filterItem['searchValue'] . ' OR (' .  $result . ' > ' . ($filterItem['searchValue'] - self::$epsilion) . ' AND ' . $result . ' < ' . ($filterItem['searchValue'] + self::$epsilion) . ' ) ';
        }
    }


    /**
     * returns condition type (=, !=, Like, NOT LIKE, >, < , ....) from rule condition item
     *
     * @param $filterItem
     *
     * @return string
     */
    private function getConditionByType($filterItem)
    {
        $condition = '';
        if($filterItem['conditionType'] === 'select' && isset($filterItem['conditionValue']) && $filterItem['conditionValue'] === 'not_equal') {
            if($filterItem['searchValue'] === 'is_null') {
                $filterItem['searchValue'] === 'is_not_null';
            }
            else if($filterItem['searchValue'] === 'is_not_null') {
                $filterItem['searchValue'] === 'is_null';
            }
        }
        if ($filterItem['conditionType'] === 'select' && isset($filterItem['searchValue']) && $filterItem['searchValue'] === 'is_null') {
            return ' IS NULL';
        } else if ($filterItem['conditionType'] === 'select' && isset($filterItem['searchValue']) && $filterItem['searchValue'] === 'is_not_null') {
            return ' IS NOT NULL';
        }
        
        if ($filterItem['conditionType'] == 'text') {
            $condition = $this->getTextCondition($filterItem['conditionValue']);
        } else if ($filterItem['conditionType'] == 'date') {
            $condition = $this->getDateCondition($filterItem['conditionValue']);
        } else if ($filterItem['conditionType'] === 'checkbox' || $filterItem['conditionType'] === 'select') {
            $condition = $this->getSelectCondition(isset($filterItem['conditionValue']) ? $filterItem['conditionValue'] : null);
        } else if ($filterItem['conditionType'] === 'in') {
            $condition = ' IN';
        } else if ($filterItem['conditionType'] === 'not_in') {
            $condition = ' NOT IN';
        }

        return $condition;
    }


    /**
     * returns condition if type is text
     *
     * @param string $conditionValue
     *
     * @return string
     */
    private function getTextCondition(string $conditionValue)
    {
        $condition = '';

        if ($conditionValue === 'equal') {
            $condition = ' =';
        } else if ($conditionValue === 'not_equal') {
            $condition = ' !=';
        } else if ($conditionValue === 'like') {
            $condition = ' LIKE';
        } else if ($conditionValue === 'not_like') {
            $condition = ' NOT LIKE';
        }

        return $condition;
    }


    /**
     * returns condition if type is date
     *
     * @param string $conditionValue
     * @return string
     */
    private function getDateCondition(string $conditionValue)
    {
        $condition = '';

        if ($conditionValue === 'equal') {
            $condition = ' =';
        } else if ($conditionValue === 'not_equal') {
            $condition = ' !=';
        } else if ($conditionValue === 'greater') {
            $condition = ' >';
        } else if ($conditionValue === 'greater_or_equal') {
            $condition = ' >=';
        } else if ($conditionValue === 'less') {
            $condition = ' <';
        } else if ($conditionValue === 'less_or_equal') {
            $condition = ' <=';
        }

        return $condition;
    }


    /**
     * returns condition if type is select
     *
     * @param string $conditionValue
     * @return string
     */
    private function getSelectCondition(?string $conditionValue)
    {
        $condition = '';

        if (!$conditionValue || $conditionValue === 'equal') {
            $condition = ' =';
        } else {
            $condition = ' !=';
        }

        return $condition;
    }


    /**
     * returns condition value from rule condition item
     *
     * @param $filterItem
     *
     * @return string
     */
    private function getSearchValueByType($filterItem)
    {
        if ($filterItem['conditionType'] === 'checkbox') {
            return ' ' . $filterItem['searchValue'] . ' ';
        } else if ($filterItem['conditionType'] === 'in' || $filterItem['conditionType'] === 'not_in') {
            return is_array($filterItem['searchValue']) ? ' (' . implode(',', $filterItem['searchValue']) . ') ' : ' ' . $filterItem['searchValue'] . ' ';
        }

        $searchValue = $filterItem['searchValue'];

        if (isset($filterItem['conditionValue']) && ($filterItem['conditionValue'] === 'like' || $filterItem['conditionValue'] === 'not_like')) {
            $searchValue = "%" . $searchValue . "%";
        }
        return ' "' . $searchValue . '" ';
    }


    /**
     * returns action type by field type
     *
     * @param string $fieldType
     * @param bool $isRelative
     * @return string
     */
    private function getActionTypeByFieldType(string $fieldType, bool $isRelative)
    {
        if (in_array($fieldType, ['checkbox', 'text', 'date', 'select']) && !$isRelative) {
            return 'assign';
        } else if (in_array($fieldType, ['number']) && !$isRelative) {
            return 'formula';
        } else if ($isRelative) {
            return 'relative_assign';
        }

        return 'assign';
    }


    /**
     * sorts and filters rules for given item
     *
     * @param NgsRuleDto[] $rules
     * @param AbstractCmsDto $dto
     * @return array
     * @throws \Exception
     */
    public function prioritizeRules(array $rules, AbstractCmsDto $dto)
    {
        $ruleClassInfo = $this->getRuleClassInfo($rules[0]);
        if (!isset($ruleClassInfo['additionalPriority']) || !$ruleClassInfo['additionalPriority']) {
            return $rules;
        }

        $filteredRules = [];

        foreach ($rules as $rule) {
            $data = $this->getItemDataByRule($dto, $rule);
            if ($data) {
                $filteredRules[] = $rule;
            }
        }
        /** @var NgsRuleDto[] $rules */
        $rules = $filteredRules;

        $field = $ruleClassInfo['additionalPriority']['field'];
        $result = [];

        $orderType = $ruleClassInfo['additionalPriority']['order'];
        $highPriorityPrefix = strtolower($orderType) === 'desc' ? 'b' : 'a';
        $lowPriorityPrefix = strtolower($orderType) === 'desc' ? 'a' : 'b';

        foreach ($rules as $rule) {
            $dtoToChange = clone $dto;
            $dtoToChange = $this->executeAction($rule, $dtoToChange);
            $getterMethod = StringUtil::getGetterByDbName($field);
            $isHighPriority = $rule->getIsHighPriority();
            $key = $isHighPriority ? $highPriorityPrefix : $lowPriorityPrefix;
            $key .= strval($dtoToChange->$getterMethod());
            $result[$key] = $rule;
        }
        
        if (strtolower($orderType) === 'desc') {
            krsort($result);
        } else {
            ksort($result);

        }

        $rules = array_values($result);

        if (isset($ruleClassInfo['applyOnlyOne']) && $ruleClassInfo['applyOnlyOne'] && $rules) {
            $rules = [$rules[0]];
        }
        return $rules;
    }
}