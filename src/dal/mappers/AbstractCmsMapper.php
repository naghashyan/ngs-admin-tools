<?php
/**
 *
 * AbstractAlbumMapper class is extended class from AbstractMysqlMapper.
 * It contatins functions that used in album mapper.
 *
 * @author Mikael Mkrtcyan
 * @site http://naghashyan.com
 * @mail mikael.mkrtchyan@naghashyan.com
 * @year 2017
 * @package ngs.AdminTools.dal.mappers
 * @version 1.0
 *
 */

namespace ngs\AdminTools\dal\mappers;

use Monolog\Logger;
use ngs\AdminTools\dal\binparams\NgsCmsParamsBin;
use ngs\AdminTools\dal\dto\AbstractCmsDto;
use ngs\AdminTools\dal\dto\NgsRuleDto;
use ngs\AdminTools\managers\NgsRuleManager;
use ngs\AdminTools\util\LoggerFactory;
use ngs\dal\dto\AbstractDto;
use ngs\dal\mappers\AbstractMysqlMapper;

abstract class AbstractCmsMapper extends AbstractMysqlMapper
{

    private Logger $logger;

    public function __construct()
    {
        parent::__construct();
        $this->logger = LoggerFactory::getLogger(get_class($this), get_class($this));
    }

    /**
     * @var string
     */
    public string $tableName;

    /**
     * @see AbstractMysqlMapper::getPKFieldName()
     */
    public function getPKFieldName(): string
    {
        return 'id';
    }

    /**
     * @see AbstractMysqlMapper::getTableName()
     */
    public function getTableName(): string
    {
        return $this->tableName;
    }


    /**
     * if yes will set status as deleted instead of removing
     *
     * @return bool
     */
    protected function hasSoftDelete() :bool
    {
        return false;
    }


    /**
     * indicates if table has created_by updated_by fields
     *
     * @return bool
     */
    public function hasCreator(): bool
    {
        return false;
    }


    /** @var string  */
    private $DELETE_ITEM_BY_ID = 'DELETE FROM %s WHERE `id`=:itemId';
    /** @var string  */
    private $DELETE_DRAFT_ITEM_BY_ID = 'DELETE FROM %s WHERE `id`=:itemId AND `status` = "draft"';
    /** @var string  */
    private $SOFT_DELETE_ITEM_BY_ID = 'UPDATE %s SET `status` = "deleted" WHERE `id`=:itemId';

    public function deleteItemById($itemId, bool $forceDelete = false)
    {
        $query = $this->DELETE_ITEM_BY_ID;
        if($this->doSoftDelete() && !$forceDelete) {
            $sqlQuery = sprintf($this->DELETE_DRAFT_ITEM_BY_ID, $this->getTableName());
            $res = $this->executeUpdate($sqlQuery, ['itemId' => $itemId]);
            if (!is_numeric($res)) {
                return false;
            }
            $query = $this->SOFT_DELETE_ITEM_BY_ID;
        }
        $sqlQuery = sprintf($query, $this->getTableName());
        $res = $this->executeUpdate($sqlQuery, ['itemId' => $itemId]);
        if (is_numeric($res)) {
            return true;
        }
        return false;
    }

    /** @var string  */
    private $DELETE_ITEMS_BY_IDS = 'DELETE FROM %s WHERE `id` IN (%s)';
    /** @var string  */
    private $DELETE_DRAFT_ITEMS_BY_IDS = 'DELETE FROM %s WHERE `id` IN (%s) AND `status` = "draft"';
    /** @var string  */
    private $SOFT_DELETE_ITEMS_BY_IDS = 'UPDATE %s SET `status` = "deleted" WHERE `id` IN (%s)';

    public function deleteItemsByIds(array $ids)
    {
        if (!$ids) {
            return true;
        }
        if(!is_numeric($ids[0])) {
            $idsQuery = '"' . implode('", "', $ids) . '"';
        }
        else {
            $idsQuery = implode(",", $ids);
        }

        $query = $this->DELETE_ITEMS_BY_IDS;
        if($this->doSoftDelete()) {
            $sqlQuery = sprintf($this->DELETE_DRAFT_ITEMS_BY_IDS, $this->getTableName(), $idsQuery);
            $res = $this->executeUpdate($sqlQuery, []);
            if (!is_numeric($res)) {
                return false;
            }
            $query = $this->SOFT_DELETE_ITEMS_BY_IDS;
        }
        $sqlQuery = sprintf($query, $this->getTableName(), $idsQuery);
        $res = $this->executeUpdate($sqlQuery, []);
        if (is_numeric($res)) {
            return true;
        }
        return false;
    }

    /**
     * @var string
     */
    private $GET_IDS_BY_PARAMS = 'SELECT DISTINCT(%s.id) as id FROM %s %s %s %s';
    public function getIdsByParams(NgsCmsParamsBin $paramsBin) :array
    {
        $joinCondition = $paramsBin->getJoinCondition();
        if($paramsBin->getGroupBy()) {
            $groupBy = $paramsBin->getGroupBy();
        }else {
            $groupBy = '';
        }
        $whereConditionResult = $this->getWhereCondition($paramsBin);
        $tableName = $this->getTableName();
        $sql = $this->GET_IDS_BY_PARAMS;
        $sqlQuery = sprintf($sql, $tableName, $tableName,
            $joinCondition, $whereConditionResult['condition'], $groupBy);
        $bindArray = $whereConditionResult['params'];

        $cache = ['cache' => false, 'ttl' => 3600, 'force' => false];
        $result = $this->fetchRows($sqlQuery, $bindArray, $cache, true);
        if(!$result) {
            return [];
        }

        return array_column($result, 'id');
    }

    /**
     * @var string
     */
    private $GET_LIST = 'SELECT %s %s FROM %s %s %s %s LIMIT ?, ?';
    private $GET_UNLIMITED_LIST = 'SELECT %s %s FROM %s %s %s %s';

    /**
     * @param NgsCmsParamsBin $paramsBin
     * @param bool $forSelect
     * @return array|null
     */
    public function getList(NgsCmsParamsBin $paramsBin, bool $forSelect = false)
    {
        $creatorAndUpdaterSelects = '';
        $joinCondition = $paramsBin->getJoinCondition();
        if ($this->hasCreator()) {
            $creatorAndUpdaterSelects = ', creatorUser.user_name as created_by_name, updateUser.user_name as updated_by_name';
            if (!$joinCondition) {
                $joinCondition = " ";
            }

            $joinCondition .= ' LEFT JOIN `users` as creatorUser ON `' . $this->getTableName() . '`.`created_by` = `creatorUser`.`id`
                LEFT JOIN `users` as updateUser ON `' . $this->getTableName() . '`.`updated_by` = `updateUser`.`id`';

        }


        $orderBySql = $paramsBin->getOrderBy();
        $cmsMapArray = $this->createDto()->getCmsMapArray();
        if(strpos($paramsBin->getSortBy(), '.') === false) {
            if($cmsMapArray && $cmsMapArray[$paramsBin->getSortBy()] && isset($cmsMapArray[$paramsBin->getSortBy()]['from_other_table'])
                && $cmsMapArray[$paramsBin->getSortBy()]['from_other_table']) {
                $sortBySql = $paramsBin->getSortBy();
            }
            else {
                $sortBySql = $this->getTableName() . '.' . $paramsBin->getSortBy();
            }
        }
        else {
            $sortBySql = trim($paramsBin->getSortBy(), '.');
        }

        //todo: modify group by
        if($paramsBin->getGroupBy()) {
            $groupBy = $paramsBin->getGroupBy();
        }else {
            $groupBy = '';
        }

        $sortBySql = $groupBy . ' ORDER BY ' . $sortBySql . ' ' . $orderBySql;

        $sql = $paramsBin->getLimit() ? $this->GET_LIST : $this->GET_UNLIMITED_LIST;
        if(!$forSelect) {
            $selectCondition = $paramsBin->getSelect() ? $this->getTableName() . ".*, " . $paramsBin->getSelect() : $this->getTableName() . ".*";
        }
        else {
            $customFields = $paramsBin->getCustomFields();
            $additionalSelect = "";
            if($customFields) {

                $additionalSelect = implode(", ", $customFields);
                if($additionalSelect === "*") {
                    $additionalSelect = "";
                }
            }

            if($additionalSelect) {
                $additionalSelect = ", " . $additionalSelect . " ";
            }
            else {
                $additionalSelect = " ";
            }

            $selectCondition = $this->getTableName() . ".id, " . $this->getTableName() . ".name as value" . $additionalSelect;
        }

        $whereConditionResult = $this->getWhereCondition($paramsBin);
        $sqlQuery = sprintf($sql, $selectCondition, $creatorAndUpdaterSelects, $this->getTableName(),
            $joinCondition, $whereConditionResult['condition'], $sortBySql);
        $bindArray = $whereConditionResult['params'];
        if($paramsBin->getLimit()) {
            $bindArray[] = (int)$paramsBin->getOffset();
            $bindArray[] = (int)$paramsBin->getLimit();
        }
        if(!$forSelect) {

            $res = $this->fetchRows($sqlQuery, $bindArray);
            return $res;
        }
        else {
            $cache = ['cache' => false, 'ttl' => 3600, 'force' => false];
            return $this->fetchRows($sqlQuery, $bindArray, $cache, true);
        }

    }


    private $GET_LIST_BY_FIELD = 'SELECT * FROM %s WHERE `%s` = :fieldValue';
    private $GET_LIST_BY_FIELD_EXPECT_ID = 'SELECT * FROM %s WHERE `%s` = :fieldValue AND id != :itemId';
    private $GET_LIST_BY_COMPANY_AND_FIELD_EXPECT_ID = 'SELECT * FROM %s WHERE `%s` = :fieldValue AND id != :itemId AND company_id = :companyId';

    public function getListByField($fieldName, $fieldValue, int $expectId = null, int $companyId = null)
    {
        $params = [
            'fieldValue' => $fieldValue
        ];
        $dto = $this->createDto();
        if (!$expectId) {
            if($companyId && method_exists($dto, 'getCompanyId')) {
                $sqlQuery = sprintf($this->GET_LIST_BY_COMPANY_AND_FIELD_EXPECT_ID, $this->getTableName(), $fieldName);
                $params['companyId'] = $companyId;
                $params['itemId'] = -1;
            }
            else {
                $sqlQuery = sprintf($this->GET_LIST_BY_FIELD, $this->getTableName(), $fieldName);
            }
        } else {

            if($companyId && method_exists($dto, 'getCompanyId')) {
                $sqlQuery = sprintf($this->GET_LIST_BY_COMPANY_AND_FIELD_EXPECT_ID, $this->getTableName(), $fieldName);
                $params['companyId'] = $companyId;
            }
            else {
                $sqlQuery = sprintf($this->GET_LIST_BY_FIELD_EXPECT_ID, $this->getTableName(), $fieldName);
            }

            $params['itemId'] = $expectId;
        }


        return $this->fetchRows($sqlQuery, $params);
    }


    /** @var string  */
    private $DELETE_BY_PARAMS = 'DELETE FROM %s %s';
    /** @var string  */
    private $SOFT_DELETE_BY_PARAMS = 'UPDATE %s set `status` = "deleted" %s';

    /**
     * @param NgsCmsParamsBin $paramsBin
     * @return bool
     */
    public function deleteByParams(NgsCmsParamsBin $paramsBin)
    {
        try {
            $whereConditionResult = $this->getWhereCondition($paramsBin);
            $query = $this->DELETE_BY_PARAMS;
            if($this->doSoftDelete()) {
                $deleteDraftCondition = $whereConditionResult['condition'];
                if(!trim($deleteDraftCondition)) {
                    $deleteDraftCondition = 'WHERE status = "draft"';
                }
                else {
                    $deleteDraftCondition .= ' AND status = "draft"';
                }
                $sqlQuery = sprintf($this->DELETE_BY_PARAMS, $this->getTableName(), $deleteDraftCondition);
                $res = $this->executeUpdate($sqlQuery, $whereConditionResult['params']);
                if (!is_numeric($res)) {
                    return false;
                }
                $query = $this->SOFT_DELETE_BY_PARAMS;
            }
            $sqlQuery = sprintf($query, $this->getTableName(), $whereConditionResult['condition']);
            $res = $this->executeUpdate($sqlQuery, $whereConditionResult['params']);
            if (is_numeric($res)) {
                return true;
            }
            return false;
        } catch (\Exception $exp) {
            return false;
        }
    }


    /** @var string  */
    protected $DELETE_BY_FIELD = 'DELETE FROM %s WHERE `%s` = :fieldValue';
    /** @var string  */
    protected $DELETE_DRAFT_BY_FIELD = 'DELETE FROM %s WHERE `%s` = :fieldValue AND status="draft"';
    /** @var string  */
    protected $DELETE_BY_FIELD_EXPECT_IDS = 'DELETE FROM %s WHERE `%s` = :fieldValue AND %s.id NOT IN %s';
    /** @var string  */
    protected $DELETE_DRAFT_BY_FIELD_EXPECT_IDS = 'DELETE FROM %s WHERE `%s` = :fieldValue AND %s.id NOT IN %s AND status="draft"';
    /** @var string  */
    protected $SOFT_DELETE_BY_FIELD = 'UPDATE %s SET `status` = "deleted" WHERE `%s` = :fieldValue';
    /** @var string  */
    protected $SOFT_DELETE_BY_FIELD_EXPECT_IDS = 'UPDATE %s SET `status` = "deleted" WHERE `%s` = :fieldValue AND %s.id NOT IN %s';

    public function deleteByField($fieldName, $fieldValue, ?array $expectIds = [])
    {
        try {
            if(!$expectIds) {
                $query = $this->DELETE_BY_FIELD;
                if($this->doSoftDelete()) {
                    $sqlQuery = sprintf($this->DELETE_DRAFT_BY_FIELD, $this->getTableName(), $fieldName);
                    $res = $this->executeUpdate($sqlQuery, ['fieldValue' => $fieldValue]);
                    if (!is_numeric($res)) {
                        return false;
                    }
                    $query = $this->SOFT_DELETE_BY_FIELD;
                }
                $sqlQuery = sprintf($query, $this->getTableName(), $fieldName);
                $res = $this->executeUpdate($sqlQuery, ['fieldValue' => $fieldValue]);
                if (is_numeric($res)) {
                    return true;
                }
            }
            else {
                $notInCondition = '(' . implode(",", $expectIds) . ')';
                $tableName = $this->getTableName();
                $query = $this->DELETE_BY_FIELD_EXPECT_IDS;
                if($this->doSoftDelete()) {
                    $sqlQuery = sprintf($this->DELETE_DRAFT_BY_FIELD_EXPECT_IDS, $tableName, $fieldName, $tableName, $notInCondition);
                    $res = $this->executeUpdate($sqlQuery, ['fieldValue' => $fieldValue]);
                    if (!is_numeric($res)) {
                        return false;
                    }
                    $query = $this->SOFT_DELETE_BY_FIELD_EXPECT_IDS;
                }
                $sqlQuery = sprintf($query, $tableName, $fieldName, $tableName, $notInCondition);
                $res = $this->executeUpdate($sqlQuery, ['fieldValue' => $fieldValue]);
                if (is_numeric($res)) {
                    return true;
                }
            }

            return false;
        } catch (\Exception $exp) {
            return false;
        }
    }

    /**
     * @var string
     */
    private $GET_ITEM_BY_ID = 'SELECT %s FROM %s %s WHERE %s.`id` = :itemId';

    /**
     * @param string $itemId
     * @param NgsCmsParamsBin $paramsBin
     * @return bool|mixed
     */
    public function getItemById(string $itemId, ?NgsCmsParamsBin $paramsBin = null)
    {
        $tableName = $this->getTableName();
        if($paramsBin) {
            $joinCondition = $paramsBin->getJoinCondition();
            $selectCondition = $paramsBin->getSelect() ? $tableName . ".*, " . $paramsBin->getSelect() : $tableName . ".*";
            $sqlQuery = sprintf($this->GET_ITEM_BY_ID, $selectCondition, $tableName, $joinCondition, $tableName);
        }
        else {
            $sqlQuery = sprintf($this->GET_ITEM_BY_ID, '*', $tableName, '', $tableName);
        }
        $bindArray = ['itemId' => $itemId];

        return $this->fetchRow($sqlQuery, $bindArray);
    }


    /**
     * @var string
     */
    private $GET_ITEMS_BY_IDS = 'SELECT * FROM %s WHERE `id` IN (%s)';

    /**
     * returns items by ids
     * @param string $itemId
     * @return AbstractCmsDto[]
     */
    public function getItemsByIds(array $itemIds)
    {
        if(!$itemIds) {
            return [];
        }
        $inCondition = implode("," , $itemIds);
        $sqlQuery = sprintf($this->GET_ITEMS_BY_IDS, $this->getTableName(), $inCondition);
        return $this->fetchRows($sqlQuery);
    }


    /**
     * @var string
     */
    private $GET_COUNT = 'SELECT COUNT(*) AS count FROM %s %s %s';

    /**
     * @param NgsCmsParamsBin $paramsBin
     *
     * @return int
     */
    public function getItemsCount(NgsCmsParamsBin $paramsBin): int
    {
        $whereConditionResult = $this->getWhereCondition($paramsBin);
        $sqlQuery = sprintf($this->GET_COUNT, $this->getTableName(),
            $paramsBin->getJoinCondition(), $whereConditionResult['condition']);

        $groupBy = '';
        if ($paramsBin->getGroupBy()) {
            $groupBy = $paramsBin->getGroupBy();
            $groupBy = str_replace('GROUP BY ', '', $groupBy);
        }

        if ($groupBy) {
            if (str_contains($groupBy, ',')) {
                $sqlQuery = str_replace('COUNT(*)', 'COUNT(DISTINCT ' . $groupBy . ') ', $sqlQuery);
            } else {
                $sqlQuery = str_replace('COUNT(*)', 'COUNT(DISTINCT(' . $groupBy . '))', $sqlQuery);
            }

        }

        return $this->fetchField($sqlQuery, 'count', $whereConditionResult['params']);
    }


    /**
     * verify $item by rule, returns true if item corresponds to rule conditions
     *
     * @param AbstractCmsDto $item
     * @param NgsRuleDto $rule
     *
     * @return bool
     */
    public function verifyItemByRule($item, $rule): bool
    {
        $ngsRuleManager = NgsRuleManager::getInstance();
        $data = $ngsRuleManager->getItemDataByRule($item, $rule);

        return !!$data;
    }


    /**
     * get items by rule
     *
     * @param NgsRuleDto $rule
     * @param NgsCmsParamsBin $paramsBin
     *
     * @return AbstractCmsDto[]
     */
    public function getItemsByRule($rule, NgsCmsParamsBin $paramsBin): array
    {
        try {

            $ngsRuleManager = NgsRuleManager::getInstance();
            $ruleConditionResult = $ngsRuleManager->getSqlConditionFromRule($rule, false);
            $sql = $ruleConditionResult['condition'];
            $params = $ruleConditionResult['params'];

            $orderBySql = $paramsBin->getOrderBy();
            $sortBySql = strpos($paramsBin->getSortBy(), '.') !== false ? $paramsBin->getSortBy() : $ngsRuleManager->getMainTableName($rule) . '.' . $paramsBin->getSortBy();
            $sortBySql = ' ORDER BY ' . $sortBySql . ' ' . $orderBySql;
            $sql .= $sortBySql;


            if ($paramsBin->getLimit()) {
                $offset = 0;
                $limit = $paramsBin->getLimit();
                if ($paramsBin->getOffset() || $paramsBin->getOffset() === 0) {
                    $offset = $paramsBin->getOffset();
                }
                $params[] = $offset;
                $params[] = $limit;
                $sql .= ' LIMIT ?, ?';
            }
            $items = $this->fetchRows($sql, $params);
            if(!$items) {
                return [];
            }
            return $items;

        } catch (\Exception $exp) {
            $this->getLogger()->error("failed to get list: " . $exp->getMessage(), ['sql' => $sql, 'params' => $params]);
            return [];
        }
    }


    /**
     * get items count by rule
     *
     * @param NgsRuleDto $rule
     *
     * @return int
     */
    public function getItemsCountByRule($rule): int
    {
        try {
            $ngsRuleManager = NgsRuleManager::getInstance();
            $conditionResult = $ngsRuleManager->getSqlCountConditionFromRule($rule, false);
            $res = $this->fetchField($conditionResult['condition'], 'count', $conditionResult['params']);
            return (int)$res;

        } catch (\Exception $exp) {
            return 0;
        }
    }

    /**
     * @param $id
     * @param $searchField
     * @param $tableName
     * @return array|AbstractDto[]|null
     * @throws \ngs\exceptions\DebugException
     */
    public function getRelatedEntity($id, $searchField, $tableName)
    {
        $query = 'SELECT * FROM %s WHERE `' . $searchField . '` = :id';
        $query = sprintf($query, $tableName);
        return $this->fetchRows($query, ['id' => $id]);
    }


    /**
     * returns logger instance
     *
     * @return Logger
     */
    protected function getLogger(): Logger
    {
        return $this->logger;
    }


    /**
     * can modify bin params before generating where condition
     * 
     * @param NgsCmsParamsBin $paramsBin
     * 
     * @return array
     */
    protected function getWhereCondition(NgsCmsParamsBin $paramsBin) :array {
        return $paramsBin->getWhereCondition();
    }


    private $softDelete = null;

    /**
     * indicates if item can be soft deleted
     *
     * @return false|void
     */
    private function doSoftDelete() {

        if($this->softDelete !== null) {
            return $this->softDelete;
        }

        if(!$this->hasSoftDelete()) {
            $this->softDelete = false;
            return $this->softDelete;
        }

        $dto = $this->createDto();
        if(method_exists($dto, 'getStatus')) {
            $this->softDelete = true;
            return $this->softDelete;
        }
        $this->softDelete = false;
        return $this->softDelete;
    }
}
