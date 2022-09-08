<?php
/**
 * MainParamsBin class provides setter/getter,
 * for working sending params between loads<----> managers <---->mapper
 *
 * @author Levon Naghashyan
 * @site https://naghashyan.com
 * @mail levon@naghashyan.com
 * @year 2019
 * @package ngs.AdminTools.dal.binparams
 * @version 1.0.0
 */

namespace ngs\AdminTools\dal\binparams;

use ngs\exceptions\DebugException;

class NgsCmsParamsBin
{

    private static $epsilion = 0.001;

    private $userId = null;
    private $languageId = null;
    private $select = "";
    private $sortBy = "id";
    private $orderBy = "DESC";
    private $offset = 0;
    private $limit = 350;
    private $page = 1;
    private $itemId = null;
    private $position = null;
    private $groupBy = null;
    private $itemType = null;
    private $returnItemsCount = null;
    private $joinCondition = "";
    private $whereCondition = [];
    private $customField = "*";
    private $customFields = [];
    private $searchKey = "";
    private $tableName = '';
    private $filterData = null;
    private $version = 1;

    /**
     * @param int $userId
     * the userId to set
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
    }

    /**
     * @return int userId
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @param string $orderBy
     * the orderBy to set
     */
    public function setOrderBy($orderBy)
    {
        $this->orderBy = $orderBy;
    }

    /**
     * @return string orderBy
     */
    public function getOrderBy()
    {
        return $this->orderBy;
    }

    /**
     * @param string $sortBy
     * the sortBy to set
     */
    public function setSortBy($sortBy)
    {
        $this->sortBy = $sortBy;
    }

    /**
     * @return string sortBy
     */
    public function getSortBy()
    {
        return $this->sortBy;
    }

    /**
     * @param int $offset
     * the offset to set
     */
    public function setOffset($offset)
    {
        $this->offset = $offset;
    }

    /**
     * @return int offset
     */
    public function getOffset()
    {
        return $this->offset;
    }

    /**
     * @param int $limit
     * the limit to set
     */
    public function setLimit($limit)
    {
        $this->limit = $limit;
    }

    /**
     * @return int limit
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * @param int $page
     * the limit to set
     */
    public function setPage($page)
    {
        $this->page = $page;
    }

    /**
     * @return int page
     */
    public function getPage()
    {
        return $this->page;
    }


    /**
     * @param string $itemType
     * the itemId to set
     */
    public function setItemType($itemType)
    {
        $this->itemType = $itemType;
    }

    /**
     * @return string $itemType
     */
    public function getItemType()
    {
        return $this->itemType;
    }

    /**
     * @param int $itemId
     * the itemId to set
     */
    public function setItemId($itemId)
    {
        $this->itemId = $itemId;
    }

    /**
     * @return int itemId
     */
    public function getItemId()
    {
        return $this->itemId;
    }


    /**
     * @param int $position
     * the trackId to set
     */
    public function setPosition($position)
    {
        $this->position = $position;
    }

    /**
     * @return int $position
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param $groupBy
     * the groupBy to set
     */
    public function setGroupBy($groupBy)
    {
        $this->groupBy = $groupBy;
    }

    /**
     * @return string groupBy
     */
    public function getGroupBy()
    {
        return $this->groupBy;
    }

    /**
     * @param bool $returnItemsCount
     * the returnItemsCount to set
     */
    public function setReturnItemsCount($returnItemsCount)
    {
        $this->returnItemsCount = $returnItemsCount;
    }

    /**
     * @return bool returnItemsCount
     */
    public function getReturnItemsCount()
    {
        return $this->returnItemsCount;
    }

    /**
     * @param string $customField
     * the customField to set
     */
    public function setCustomField($customField)
    {
        $this->customField = $customField;
    }

    /**
     * @return string customField
     */
    public function getCustomField()
    {
        return $this->customField;
    }

    /**
     * @param string $customField
     * the customField to set
     */
    public function setCustomFields($customField)
    {
        $this->customFields[] = $customField;
    }

    /**
     * @return  array customField
     */
    public function getCustomFields()
    {
        if (count($this->customFields) == 0) {
            return ["*"];
        }
        return $this->customFields;
    }

    /**
     * @param bool $includeFavorite
     * the $includeFavorite to set
     */
    public function setIncludeFavorites($includeFavorite)
    {
        $this->includeFavorite = $includeFavorite;
    }

    /**
     * @return bool $includeFavorite
     */
    public function getIncludeFavorites()
    {
        return $this->includeFavorite;
    }

    /**
     * @param string $searchKey
     * the $searchKey to set
     */
    public function setSearchKey($searchKey)
    {
        $this->searchKey = $searchKey;
    }

    /**
     * @return string $searchKey
     */
    public function getSearchKey()
    {
        return $this->searchKey;
    }

    /**
     * @return null
     */
    public function getLanguageId()
    {
        return $this->languageId;
    }

    /**
     * @param null $languageId
     */
    public function setLanguageId($languageId): void
    {
        $this->languageId = $languageId;
    }

    /**
     * @return string
     */
    public function getSelect(): string
    {
        return $this->select;
    }

    /**
     * @param string $select
     */
    public function setSelect(string $select): void
    {
        $this->select = $select;
    }

    /**
     * @return string
     */
    public function getJoinCondition(): string
    {
        return $this->joinCondition;
    }

    /**
     * @param string $joinCondition
     */
    public function setJoinCondition(string $joinCondition): void
    {
        $this->joinCondition = $joinCondition;
    }

    /**
     * @return string
     */
    public function getTableName(): string
    {
        return $this->tableName;
    }

    /**
     * @param string $tableName
     */
    public function setTableName(string $tableName): void
    {
        $this->tableName = $tableName;
    }


    /**
     * @return array
     */
    public function getWhereCondition(): array
    {
        if ($this->getVersion() == 2) {
            $params = [];
            $condition = $this->getWhereConditionFromFilter(null, null, $params);
            return [
                'condition' => 'WHERE ' . $condition,
                'params' => $params
            ];
        }
        if (count($this->whereCondition) === 0) {
            return [
                'condition' => '',
                'params' => []
            ];
        }
        $params = [];
        $whereConditionSql = 'WHERE ';
        $operator = ' ';
        foreach ($this->whereCondition as $group => $value) {
            $whereConditionGeoupOperator = '(';

            foreach ($value as $key => $queryData) {

                if ($key > 0) {
                    $whereConditionGeoupOperator = '';
                }
                $_value = $queryData['value'];
                if (is_string($_value) && !in_array($queryData['comparison'], ['in', 'not_in', 'is null', 'is not null'])) {
                    $params[] = $_value;
                    $_value = ' ? ';
                } else if (is_string($_value) && in_array($queryData['comparison'], ['is null', 'is not null'])) {
                    $_value = "";
                    $queryData['comparison'] = strtoupper($queryData['comparison']);
                } else if (is_string($_value) && in_array($queryData['comparison'], ['in', 'not_in'])) {
                    $listParams = $_value;
                    $listParams = str_replace('(', '', $listParams);
                    $listParams = str_replace(')', '', $listParams);
                    $listParams = explode(",", $listParams);
                    $listParamsToInsert = [];
                    foreach ($listParams as $listParam) {
                        $params[] = $listParam;
                        $listParamsToInsert[] = '?';
                    }
                    $_value = '(' . implode(",", $listParamsToInsert) . ')';
                } else {
                    $params[] = $_value;
                    $_value = ' ? ';
                }
                $compresion = $queryData['comparison'];
                if ($compresion === 'in') {
                    $compresion = "IN";
                } else if ($compresion === 'not_in') {
                    $compresion = "NOT IN";
                } else if ($compresion === 'not_like') {
                    $condition = ' NOT LIKE';
                }
                $whereConditionSql .= $operator . $whereConditionGeoupOperator . $queryData['field'] . ' ' . $compresion . $_value;
                $operator = ' ' . $queryData['operator'] . ' ';
            }

            $whereConditionSql .= ')';
        }

        return [
            'condition' => $whereConditionSql,
            'params' => $params
        ];
    }

    private $defaultOperators = ['and' => true, 'or' => true, 'not' => true];
    private $defaultComparisons = ['=' => true, '<>' => true, '!=' => true, '>' => true, '>=' => true, '<' => true,
        '<=' => true, 'is null' => true, 'is not null' => true, 'like' => true, 'not_like' => true, 'exists' => true, 'in' => true, 'not_in' => true, 'not' => true];


    /**
     *
     * set where condition
     *
     * @param array $fieldArr ['dto'=>AbstractCmsDto, 'field'=>string]
     * @param string $value
     * @param string $operator acceptable Operators and, or, not
     * @param string $comparison acceptable Comparisons =, <>, !=, >, >=, <, <=, is null, is not null,like, exists, in, not
     */
    public function setWhereCondition(array $fieldArr, string $value, string $operator, string $comparison, string $group = '00'): void
    {
        if (!isset($this->defaultOperators[strtolower($operator)])) {
            throw new DebugException('please use and, or, not Operators');
        }
        if (!isset($this->defaultComparisons[strtolower($comparison)])) {
            throw new DebugException('please use =, <>, !=, >, >=, <, <=, is null, 
        is not null, like, exists, in, not Comparisons');
        }
        if (!isset($fieldArr['dto']) || !isset($fieldArr['field'])) {
            throw new DebugException('please correct fieldArr');
        }
        if (!$fieldArr['dto']->isExistField($fieldArr['field'])) {
            throw new DebugException($fieldArr['field'] . ' fieald not exist in dto');
        }
        $field = '`' . $fieldArr['dto']->getTableName() . '`.' . '`' . $fieldArr['field'] . '`';
        $this->whereCondition[$group][] = ['field' => $field, 'value' => $value, 'operator' => $operator, 'comparison' => $comparison];
    }

    /**
     * @param string $whereCondition
     */
    public function setWhereOrCondition(array $fieldArr, string $value, string $comparison = '=', string $group = '00'): void
    {
        $this->setWhereCondition($fieldArr, $value, 'or', $comparison, $group);
    }

    /**
     * @param string $key
     */
    public function setWhereAndCondition(array $fieldArr, string $value, string $comparison = '=', string $group = '00'): void
    {
        if ($this->getVersion() == 2) {
            if (!isset($this->filterData['filter']['and'])) {
                $this->filterData['filter']['and'] = [];
            }
            $comparison = $comparison === '=' ? 'equal' : 'not_equal';
            $this->filterData['filter']['and'][] = [
                'fieldName' => $fieldArr['field'],
                'conditionType' => $fieldArr['conditionType'],
                'conditionValue' => $comparison,
                'searchValue' => $value,
                'tableName' => isset($fieldArr['table']) ? $fieldArr['table'] : $fieldArr['dto']->getTableName()
            ];
        } else {
            $this->setWhereCondition($fieldArr, $value, 'and', $comparison, $group);
        }
    }


    public function setFilter(array $filter)
    {
        $this->filterData = $filter;
    }

    public function getFilter(): ?array
    {
        return $this->filterData;
    }


    public function getWhereConditionFromFilter($filter, $operator, array &$params)
    {
        $search = null;

        if (!$filter) {
            $filter = isset($this->filterData['filter']) ? $this->filterData['filter'] : null;
            $search = isset($this->filterData['search']) && $this->filterData['search'] ? $this->filterData['search'] : null;
        }
        $tableName = $this->filterData['table'];
        $result = '';
        $searchResult = $this->getSearchCondition($search, $params);

        if (!$filter) {
            return $searchResult;
        }

        if (isset($filter['or'])) {
            $result = ' ( ' . $this->getWhereConditionFromFilter($filter['or'], 'or', $params) . ' ) ';
        } else if (isset($filter['and'])) {
            $result = ' ( ' . $this->getWhereConditionFromFilter($filter['and'], 'and', $params) . ' ) ';
        } else if ($filter) {
            $delim = '';

            foreach ($filter as $filterItem) {
                if (isset($filterItem['or'])) {
                    $result .= $delim . $this->getWhereConditionFromFilter($filterItem, 'or', $params);
                    $delim = ' ' . $operator . ' ';
                } else if (isset($filterItem['and'])) {
                    $result .= $delim . $this->getWhereConditionFromFilter($filterItem, 'and', $params);
                    $delim = ' ' . $operator . ' ';
                } else {
                    if ($result) {
                        $result .= $operator . ' ';
                    }
                    $localTableName = isset($filterItem['tableName']) ? $filterItem['tableName'] : $tableName;
                    $fieldNameWithTable = strpos($filterItem['fieldName'], '.') === false ? $localTableName . '.' . $filterItem['fieldName'] : $filterItem['fieldName'];
                    $filterItem['fieldName'] = $fieldNameWithTable;
                    $result .= $this->getFieldName($filterItem);


                    if ($filterItem['conditionType'] == 'number') {
                        $result .= $this->getConditionAndSearchValueForNumberField($this->getFieldName($filterItem), $filterItem, $params);
                    } else {
                        $condition = $this->getConditionByType($filterItem);
                        if ($filterItem['conditionType'] === 'select' && isset($filterItem['searchValue']) &&
                            ($filterItem['searchValue'] === 'is_null' || $filterItem['searchValue'] === 'is_not_null')) {
                            $result .= $condition;
                        } else {
                            $searchValue = $this->getSearchValueByType($filterItem, $params);
                            $result .= $condition . $searchValue;
                        }
                    }
                }
            }
        }
        $finalCondition = '';
        if ($searchResult) {
            $finalCondition = $searchResult;
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
     * number comparisons are not always correct because of float numbers
     * @param string $fieldName
     * @param $filterItem
     * @param array $params
     * @return string
     */
    private function getConditionAndSearchValueForNumberField(string $fieldName, $filterItem, array &$params)
    {
        switch ($filterItem['conditionValue']) {
            case 'equal' :
                $params[] = $filterItem['searchValue'] - self::$epsilion;
                $params[] = $filterItem['searchValue'] + self::$epsilion;
                return ' > ? AND ' . $fieldName . ' < ? ';
            case 'greater' :
                $params[] = $filterItem['searchValue'];
                return ' - ' . self::$epsilion . ' > ? ';
            case 'less' :
                $params[] = $filterItem['searchValue'];
                return ' + ' . self::$epsilion . ' < ? ';
            case 'greater_or_equal' :
                $params[] = $filterItem['searchValue'];
                $params[] = $filterItem['searchValue'] - self::$epsilion;
                $params[] = $filterItem['searchValue'] + self::$epsilion;
                return ' - ' . self::$epsilion . ' > ? OR (' . $fieldName . ' > ? AND ' . $fieldName . ' < ? ) ';
            case 'less_or_equal' :
                $params[] = $filterItem['searchValue'];
                $params[] = $filterItem['searchValue'] - self::$epsilion;
                $params[] = $filterItem['searchValue'] + self::$epsilion;
                return ' + ' . self::$epsilion . ' < ? OR (' . $fieldName . ' > ? AND ' . $fieldName . ' < ? ) ';
        }
    }


    /**
     * creates search condition
     *
     * @param $search
     * @param array $params
     * @return string
     */
    private function getSearchCondition($search, array &$params)
    {
        $searchResult = '';
        if ($search && $search['searchableFields'] && $search['searchKeys']) {
            $searchResult .= '(';
            $searchDelim = '';
            foreach ($search['searchKeys'] as $searchKey) {
                foreach ($search['searchableFields'] as $searchableField) {
                    $searchResult .= $searchDelim;
                    //TODO: fix injection
                    $searchResult .= $searchableField . ' LIKE ? ';
                    $params[] = "%" . $searchKey . "%";
                    if (!$searchDelim) {
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
    private function getFieldName($filterItem)
    {
        if ($filterItem['conditionType'] == 'date' && $filterItem['conditionValue'] === 'equal') {
            return "SUBSTRING(" . $filterItem['fieldName'] . ", 1, 10)"; //only date part
        }
        return $filterItem['fieldName'];
    }


    /**
     * @param $filterItem
     * @return null|string
     */
    private function getConditionByType($filterItem)
    {
        $condition = null;
        if ($filterItem['conditionType'] === 'select' && isset($filterItem['conditionValue']) && $filterItem['conditionValue'] === 'not_equal') {
            if ($filterItem['searchValue'] === 'is_null') {
                $filterItem['searchValue'] === 'is_not_null';
            } else if ($filterItem['searchValue'] === 'is_not_null') {
                $filterItem['searchValue'] === 'is_null';
            }
        }
        if ($filterItem['conditionType'] === 'select' && isset($filterItem['searchValue']) && $filterItem['searchValue'] === 'is_null') {
            return ' IS NULL';
        } else if ($filterItem['conditionType'] === 'select' && isset($filterItem['searchValue']) && $filterItem['searchValue'] === 'is_not_null') {
            return ' IS NOT NULL';
        }
        if ($filterItem['conditionType'] == 'text') {
            if ($filterItem['conditionValue'] === 'equal') {
                $condition = ' =';
            } else if ($filterItem['conditionValue'] === 'not_equal') {
                $condition = ' !=';
            } else if ($filterItem['conditionValue'] === 'like') {
                $condition = ' LIKE';
            } else if ($filterItem['conditionValue'] === 'not_like') {
                $condition = ' NOT LIKE';
            }
        } else if ($filterItem['conditionType'] == 'date') {
            if ($filterItem['conditionValue'] === 'equal') {
                $condition = ' =';
            } else if ($filterItem['conditionValue'] === 'not_equal') {
                $condition = ' !=';
            } else if ($filterItem['conditionValue'] === 'greater') {
                $condition = ' >';
            } else if ($filterItem['conditionValue'] === 'greater_or_equal') {
                $condition = ' >=';
            } else if ($filterItem['conditionValue'] === 'less') {
                $condition = ' <';
            } else if ($filterItem['conditionValue'] === 'less_or_equal') {
                $condition = ' <=';
            }
        } else if ($filterItem['conditionType'] === 'checkbox' || $filterItem['conditionType'] === 'select') {
            if (!isset($filterItem['conditionValue']) || $filterItem['conditionValue'] === 'equal') {
                $condition = ' =';
            } else {
                $condition = ' !=';
            }

        } else if ($filterItem['conditionType'] === 'in') {
            $condition = ' IN';
        } else if ($filterItem['conditionType'] === 'not_in') {
            $condition = ' NOT IN';
        }

        return $condition;
    }


    /**
     * @param $filterItem
     * @param array $params
     *
     * @return string
     */
    private function getSearchValueByType($filterItem, array &$params)
    {
        if ($filterItem['conditionType'] === 'checkbox') {
            return ' ' . $filterItem['searchValue'] . ' ';
        } else if ($filterItem['conditionType'] === 'in' || $filterItem['conditionType'] === 'not_in') {
            if (is_array($filterItem['searchValue'])) {
                $values = [];
                foreach ($filterItem['searchValue'] as $filerSearchValue) {
                    $params[] = $filerSearchValue;
                    if (!is_numeric($filerSearchValue)) {
                        $values[] = '"?"';
                    } else {
                        $values[] = '?';
                    }
                }
                return ' (' . implode(',', $values) . ') ';
            } else {
                $params[] = $filterItem['searchValue'];
                return ' ? ';
            }

        }

        $searchValue = $filterItem['searchValue'];

        if (isset($filterItem['conditionValue']) && ($filterItem['conditionValue'] === 'like' || $filterItem['conditionValue'] === 'not_like')) {
            $params[] = "%" . $searchValue . "%";
        } else {
            $params[] = $searchValue;
        }
        return ' ? ';
    }


    /**
     * @param $version
     */
    public function setVersion($version)
    {
        $this->version = $version;
    }


    /**
     * @return int
     */
    public function getVersion()
    {
        return $this->version;
    }

}
