<?php
/**
 * CmsManager manager class
 *
 * @author Mikael Mkrtchyan, Levon Naghashyan
 * @site https://naghashyan.com
 * @mail miakel.mkrtchyan@naghashyan.com
 * @year 2017-2019
 * @package ngs.AdminTools.managers
 * @version 2.0.0
 *
 */

namespace ngs\AdminTools\managers;

use Monolog\Logger;
use ngs\AbstractManager;
use ngs\AdminTools\dal\binparams\NgsCmsParamsBin;
use ngs\AdminTools\dal\dto\AbstractCmsDto;
use ngs\AdminTools\dal\dto\NgsRuleDto;
use ngs\AdminTools\dal\mappers\AbstractCmsMapper;
use ngs\AdminTools\exceptions\NgsValidationException;
use ngs\AdminTools\util\ArrayUtil;
use ngs\AdminTools\util\LoggerFactory;
use ngs\AdminTools\util\StringUtil;
use ngs\AdminTools\util\ValidateUtil;
use ngs\exceptions\DebugException;
use ngs\exceptions\NgsErrorException;

abstract class AbstractCmsManager extends AbstractManager
{

    /**
     * @var AbstractCmsMapper
     */
    private $mapper = null;
    private Logger $logger;
    private array $possibleValues = [];
    private array $selectionList = [];


    public function __construct()
    {
        parent::__construct();
        $this->logger = LoggerFactory::getLogger(get_class($this), get_class($this));
    }


    /**
     * returns relative entities info
     *
     * @return array
     */
    public function getRelationEntities(): array
    {
        return [];
    }


    /**
     * return fields that dont need to export
     * if need to add some other fields, need to override and merge with parent method
     * @return array
     */
    public function getFieldsToSkipForExport(): array
    {
        return ['id', 'updated_by', 'created_by', 'sage_key', 'sage_id', 'image', 'system'];
    }

    /**
     * return custom fields, that need to export, but dont exists in mapArray;
     * @return array
     */
    public function getAdditionalFieldsToExport(): array
    {
        return [];
    }


    /**
     * returns columns for the item that can be used in formula for the export
     * @return array
     */
    public function getCustomizableExportColumns() :array
    {
        return [];
    }


    /**
     * returns request parameters array formater as ['db_field_name' => value]
     *
     * @param string $type
     * @param array $additionalValidators
     * @param AbstractCmsDto|null $dto
     * @param object|null $request
     * @param array|null $requestData
     *
     * @return array
     *
     * @throws NgsErrorException
     * @throws NgsValidationException
     * @throws DebugException
     */
    public function getRequestParameters(string $type, array $additionalValidators = [], $dto = null, $request = null, ?array $requestData = null): array
    {
        $updateArr = [];
        $editFields = $this->getAddEditFieldsMethods($type === 'add' ? $type : 'edit', $dto);
        $dtoToCheck = $this->createDto();


        $hasNotFilledRequiredFields = false;

        $validationResult = $this->validateRequest($type, $editFields, $additionalValidators, $request, $requestData, $dto);
        if($validationResult['errors']) {
            $mapArray = $dtoToCheck->getCmsMapArray();

            $errorText = ValidateUtil::getErrorTextByErrors($validationResult['errors'], $mapArray);

            if(!$this->hasDraftSupport($dto)) {
                throw new NgsValidationException($errorText, 0, null, $validationResult['errors']);
            }
            else {
                $notEmptyErrors = ValidateUtil::getNotEmptyErrors($validationResult['errors']);
                if(!$notEmptyErrors) {
                    $hasNotFilledRequiredFields = true;
                    $validationResult['fields']['status'] = AbstractCmsDto::DRAFT_STATUS;
                }
                else {
                    $errorText = ValidateUtil::getErrorTextByErrors($notEmptyErrors, $mapArray);
                    throw new NgsValidationException($errorText, 0, null, $notEmptyErrors);
                }
            }
        }

        $requestData = $validationResult['fields'];

        foreach ($editFields as $methodKey => $methodValue) {
            if($methodValue['relative']) {
                continue;
            }
            $key = $methodValue['data_field_name'];
            $fieldName = $methodValue['data_field_name'];
            if(!$dtoToCheck->hasWriteAccess($fieldName)) {
                continue;
            }
            if(!isset($requestData[$fieldName]) && $methodValue['action_type'] !== 'checkbox') {
                continue;
            }

            $value = isset($requestData[$fieldName]) ? $requestData[$fieldName] : "";
            if (is_string($value)) {
                $value = trim($value);
            }
            if ($methodValue['required'] && $value === null) {
                $fieldDisplayName = $methodValue['display_name'];
                throw new NgsErrorException($fieldDisplayName . ' field is required!');
            }
            if ($methodValue['type'] === 'number' && $value && !is_numeric($value)) {
                throw new NgsErrorException($key . ' field should be number!');
            }
            if ($methodValue['action_type'] === 'checkbox') {

                $value = $value === 'on' ? 1 : 0;
            }

            if (is_null($value)) {
                continue;
            }

            if($value === '') {
                if (in_array($methodValue['action_type'], ['number', 'select']) ) {
                    $value = null;
                }
            }

            if ($methodValue['action_type'] === 'date') {
                $format = 'Y-m-j';
                if ($fieldName === 'date_start') {
                    $format = 'd F Y, H:i';
                    $value .= ', 00:00';
                } else if ($fieldName === 'date_end') {
                    $format = 'd F Y, H:i';
                    $value .= ', 23:59';
                }
                $date = \DateTime::createFromFormat($format, $value);

                if (!$date) {
                    continue;
                }
                $value = $date->format('Y-m-j H:i:s');
            }
            if (!($methodValue['action_type'] === 'password' && !$value)) {
                $updateArr[$key] = $value;
            }
        }

        return ['data' => $updateArr, 'hasNotFilledRequiredFields' => $hasNotFilledRequiredFields];
    }


    /**
     * fill dto with possible values
     * @param $dto
     * @param array $possibleValues
     * @param array|null $onlyFields
     */
    public function fillDtoWithRelationalData($dto, array $possibleValues, ?array $onlyFields = null) {
        $relationalFields = $this->getRelationEntities();
        $dtoRelativeValues = $this->getRelativeSelectedValues($dto, $onlyFields);
        foreach($possibleValues as $fieldName => $possibleValues) {
            $relativeValues = [];

            if(isset($relationalFields[$fieldName]) && $relationalFields[$fieldName]['relation_type'] === 'many_to_many') {
                if(isset($relationalFields[$fieldName]['only_for_rule']) && $relationalFields[$fieldName]['only_for_rule']) {
                    continue;
                }
                $relativeValues = isset($dtoRelativeValues[$fieldName]) ? $dtoRelativeValues[$fieldName] : [];
            }

            $setter = StringUtil::getSetterByDbName($fieldName);
            $getter = StringUtil::getGetterByDbName($fieldName);

            if(!method_exists($dto, $getter)) {
                continue;
            }

            $valuesArray = ArrayUtil::findInArray($possibleValues, 'id', $relativeValues ? $relativeValues : $dto->$getter());
            if(!$valuesArray) {
                $valuesArray = ArrayUtil::findInArray($possibleValues, 'id', $relativeValues ? $relativeValues : (int) $dto->$getter());
            }
            $valueToSet = $dto->$getter();
            if($valuesArray) {
                if(isset($valuesArray[0])) {
                    $valueToSet = [];
                    foreach($valuesArray as $valueArray) {
                        $valueToSet[] = $valueArray['value'];
                    }
                    $valueToSet = implode(", ", $valueToSet);
                }
                else {
                    $valueToSet = $valuesArray['value'];
                }
            }

            $dto->$setter($valueToSet);
        }
    }


    /**
     * @param NgsCmsParamsBin $paramsBin
     * @return NgsCmsParamsBin
     */
    public function modifyNgsListBinParams(NgsCmsParamsBin $paramsBin) : NgsCmsParamsBin
    {
        return $paramsBin;
    }


    /**
     * @param $itemDto
     * @param bool $forFilter
     * @param array $additionalData
     *
     * @return array
     */
    public function getSelectionPossibleValues($itemDto, bool $forFilter = false, array $additionalData = [])
    {
        if (isset($this->possibleValues[get_class($itemDto)])) {
            return $this->modifySelectionValues($this->possibleValues[get_class($itemDto)]);
        }
        $selectValues = $this->getPossibleValuesForSelects($itemDto, $forFilter, $additionalData);
        $relationEntities = $this->getRelationEntities();
        foreach ($relationEntities as $key => $relationEntity) {
            if (isset($relationEntity['relation_type']) && $relationEntity['relation_type'] === 'many_to_many') {
                /** @var AbstractCmsManager $manager */
                $manager = $relationEntity['relative_manager'];
            } else {
                /** @var AbstractCmsManager $manager */
                $manager = $relationEntity['manager'];
            }
            $paramsBin = $this->getParamsBinForSelectingRelativeFields($key, $itemDto);
            $selectValues[$key] = $manager->getSelectionList($paramsBin);
            if($forFilter) {
                $selectValues[$key] = $manager->modifyFilterValues($selectValues[$key]);
            }
        }

        $this->possibleValues[get_class($itemDto)] = $selectValues;
        return $this->modifySelectionValues($selectValues);
    }

    /**
     * when need to do getList, and need to set paramsBin some condition, override this function.
     * need this function because otherwise should override the "getSelectionPossibleValues" function, and there are some private fields.
     * @param AbstractCmsDto $itemDto
     * @return |null
     */
    public function getParamsBinForSelectingRelativeFields(string $key, $itemDto) {
        return null;
    }


    /**
     * @param AbstractCmsDto $itemDto
     * @param array|null $onlyFields
     *
     * @return array
     */
    public function getRelativeSelectedValues($itemDto, ?array $onlyFields = null)
    {
        $relationEntities = $this->getRelationEntities();
        $result = [];


        $itemId = $itemDto ? $itemDto->getId() : -1;

        foreach ($relationEntities as $key => $relationEntity) {
            if($onlyFields && !in_array($key, $onlyFields) && !in_array($itemDto->getTableName() . '.' . $key, $onlyFields)) {
                continue;
            }

            if (isset($relationEntity['relation_type']) && $relationEntity['relation_type'] === 'many_to_many') {

                if (isset($relationEntity['only_for_rule']) && $relationEntity['only_for_rule']) {
                    continue;
                }
                /** @var AbstractCmsManager $manager */
                $manager = $relationEntity['manager'];
                $result[$key] = $manager->getRelativeValues($relationEntity['field'], $relationEntity['relation_field'], $itemId);
            }
        }

        return $result;
    }


    /**
     * @param $itemDto
     * @param bool $forFilter
     * @param array $additionalData
     * @return array
     */
    public function getPossibleValuesForSelects($itemDto, bool $forFilter = false, array $additionalData = []): array
    {
        return [];
    }

    /**
     * return assoc array with field_name as key and help_text if exists as value
     * @return array
     */
    public function getHelpTexts(): array
    {
        $mapper = $this->getMapper();
        $itemDto = $mapper->createDto();
        $mapArray = $itemDto->getCmsMapArray();

        $result = [];
        foreach ($mapArray as $key => $value) {
            $result[$key] = (isset($value['help_text'])) ? $value['help_text'] : null;
        }
        return $result;
    }


    /**
     * return assoc array with field_name as key and help_text if exists as value
     * @return array
     */
    public function getDefaultValuesOfFields(): array
    {
        $mapper = $this->getMapper();
        $itemDto = $mapper->createDto();
        $mapArray = $itemDto->getCmsMapArray();

        $result = [];
        foreach ($mapArray as $key => $value) {
            if((isset($value['default_value']))) {
                $result[$key] = $value['default_value'];
            }
        }
        return $result;
    }


    /**
     * @return bool
     */
    public function hasImage(): bool
    {
        return false;
    }

    public function hasImageThumbnails(): bool
    {
        return false;
    }

    public function hasAttachedFile(): bool
    {
        return false;
    }

    public function hasTranslations(): bool
    {
        return false;
    }

    /**
     * get allowed actions for loads
     * @return array
     */
    public function getAllowedActions(): array
    {
        return ['add', 'edit', 'delete'];
    }

    /**
     * determines whether current load should have add button or no it template
     * @return bool
     */
    public function loadShouldHaveAddButton() {
        $hasAddButton = true;
        $emptyDto = $this->getMapper()->createDto();
        $hasWriteAccess = $emptyDto->hasWriteAccess('id');

        if(!in_array('add', $this->getAllowedActions()) || !$hasWriteAccess) {
            $hasAddButton = false;
        }
        return $hasAddButton;
    }

    public function getEntitiesMainIdentifiers($itemDto): array
    {
        $cmsMapArray = $this->getMapper()->createDto()->getCmsMapArray();
        $res = [];

        if (!$cmsMapArray) {
            return $res;
        }

        foreach ($cmsMapArray as $name => $properties) {
            if (isset($properties['main_identifier']) && $properties['main_identifier']) {
                $getterFunction = StringUtil::getGetterByDbName($name);
                $displayName = isset($properties['display_name']) ? $properties['display_name'] : StringUtil::underlinesToCamelCase($name);
                $item = ['display_name' => $displayName, 'value' => $itemDto->$getterFunction()];
                $res[] = $item;
            }
        }
        return $res;
    }

    /**
     * @param $files
     * @param $id
     */


    /**
     * @param array $params
     * @param bool $updateRelations
     * @param bool $setCreator
     *
     * @return AbstractCmsDto|null
     */
    public function createItem(array $params, bool $updateRelations = true, bool $setCreator = true)
    {
        $mapper = $this->getMapper();
        $user = NGS()->getSessionManager()->getUser() && NGS()->getSessionManager()->getUser()->getId() ? NGS()->getSessionManager()->getUser() : null;
        if(!$user) {
            $userManager = UserManager::getInstance();
            $user = $userManager->getSystemUser();
        }
        if ($mapper->hasCreator() && $setCreator && $user) {
            $params['created_by'] = $user->getId();
        }
        $itemDto = $mapper->createDto();
        $itemDto->fillDtoFromArray($params);
        $id = $this->getMapper()->insertDto($itemDto);
        if(!$id) {
            return null;
        }
        if ($updateRelations) {
            $this->updateItemRelations($params, $id);
        }
        $itemDto->setId($id);
        return $itemDto;
    }


    /**
     *
     * returns true if field is uniq
     * 
     * @param $fieldName
     * @param $fieldValue
     * @param int $itemId
     * @param int $companyId
     *
     * @return bool
     */
    public function fieldIsUnique($fieldName, $fieldValue, int $itemId = null, int $companyId = null) {
        $items = $this->getMapper()->getListByField($fieldName, $fieldValue, $itemId, $companyId);
        return count($items) === 0;
    }


    /**
     * returns possible filterable values
     *
     * @return array
     */
    public function getFilterValues(): array
    {
        /** @var AbstractCmsDto $itemDto */
        $itemDto = $this->getMapper()->createDto();
        $tableName = $itemDto->getTableName();
        $mapArray = $itemDto->getCmsMapArray();
        $result = [];
        $possibleValues = $this->getSelectionPossibleValues($itemDto, true);
        foreach ($mapArray as $key => $mapArrayItem) {
            if (!isset($mapArrayItem['filterable']) || $mapArrayItem['filterable'] === false) {
                continue;
            }
            $fieldKey = $tableName. '.' . $key;
            if(isset($mapArrayItem['from_other_table']) && $mapArrayItem['from_other_table']) {
                if(is_string($mapArrayItem['from_other_table'])) {
                    $fieldKey = $mapArrayItem['from_other_table'];
                }
                else {
                    $fieldKey = $key;
                }
            }
            $filterItem = [
                'id' => $fieldKey,
                'value' => $mapArrayItem['display_name'],
                'type' => $mapArrayItem['type']
            ];

            if ($filterItem['type'] === 'select') {
                $filterItem['possible_values'] = ArrayUtil::getByMatchingKey($possibleValues, $filterItem['id']);
            }

            $result[] = $filterItem;
        }
        return $result;
    }


    /**
     * @return array
     */
    public function getExportableFields(): array
    {
        /** @var AbstractCmsDto $itemDto */
        $itemDto = $this->getMapper()->createDto();
        $tableName = $itemDto->getTableName();
        $fields = $itemDto->getCmsMapArray();
        $fields = array_merge($fields, $this->getAdditionalFieldsToExport());
        $fieldsToSkip = $this->getFieldsToSkipForExport();
        $result = [];
        foreach ($fields as $key => $item) {
            if (in_array($key, $fieldsToSkip) && !array_key_exists($key, $this->getAdditionalFieldsToExport()) ) {
                continue;
            }

            $exportableItem = [
                'id' => $tableName. '.' . $key,
                'value' => $item['display_name']
            ];


            $result[] = $exportableItem;
        }
        return $result;
    }
    
    /**
     *
     * set version 2 filter to $paramsBin
     * @param $filterData
     * @param NgsCmsParamsBin $paramsBin
     *
     * @return NgsCmsParamsBin
     */
    public function setFilterForList($filterData, NgsCmsParamsBin $paramsBin) {
        $searchData = null;
        $searchableFields = $this->getSearchableFields();
        if(isset($filterData['search'])) {
            $searchData = [
                'searchKeys' => $filterData['search'],
                'searchableFields' => $searchableFields
            ];
        }

        $filter = [];
        foreach($filterData as $key => $value) {
            if($key == 'search') {
                continue;
            }
            $filter[$key] = $value;
        }

        if($searchableFields || $filter) {
            $paramsBin->setVersion(2);
            $paramsBin->setFilter(['filter' => $filter, 'search' => $searchData, 'table' => $this->getMapper()->getTableName(), 'dto' => $this->getMapper()->createDto()]);
        }

        return $paramsBin;
    }

    /**
     * returns possible filterable values
     *
     * @return array
     */
    public function getSearchableFields(): array
    {
        $mapper = $this->getMapper();
        $itemDto = $mapper->createDto();
        $mapArray = $itemDto->getCmsMapArray();
        $result = [];
        $tableName = $mapper->getTableName();
        foreach ($mapArray as $key => $mapArrayItem) {
            $field = '`' . $tableName . '`.' . '`' . $key . '`';
            if (isset($mapArrayItem['searchable']) && $mapArrayItem['searchable'] === true && !in_array($field, $result)) {
                $result[] = $field;
            }
        }

        return $result;
    }

    /**
     * @param $itemId
     * @param array $params
     * @param bool $updateRelations
     * @param bool $setUpdator
     *
     * @return mixed
     */
    public function updateItem(int $itemId, array $params, bool $updateRelations = true, bool $setUpdator = true, bool $onlyNotSetFields = false)
    {
        $mapper = $this->getMapper();
        if ($mapper->hasCreator() && $setUpdator) {
            $user = NGS()->getSessionManager()->getUser() && NGS()->getSessionManager()->getUser()->getId() ? NGS()->getSessionManager()->getUser() : null;
            if(!$user) {
                $userManager = UserManager::getInstance();
                $user = $userManager->getSystemUser();
            }

            $params['updated_by'] = $user ? $user->getId() : null;
        }
        if(!isset($params['updated'])) {
            $params['updated'] = date('Y-m-j H:i:s');
        }
        $itemDto = $mapper->createDto();
        $itemDto->fillDtoFromArray($params, $onlyNotSetFields);

        $itemDto->setId($itemId);
        $result = $mapper->updateByPk($itemDto);
        if ($updateRelations) {
            $this->updateItemRelations($params, $itemDto->getId());
        }

        $itemDto = $this->getMapper()->getItemById($itemId);
        return $itemDto;
    }


    /**
     * updates dto in db
     *
     * @param AbstractCmsDto $item
     *
     * @return bool
     *
     * @throws \ngs\exceptions\DebugException
     */
    public function updateItemByPk($item): bool
    {
        $mapper = $this->getMapper();
        if ($mapper->hasCreator()) {
            $user = NGS()->getSessionManager()->getUser() && NGS()->getSessionManager()->getUser()->getId() ? NGS()->getSessionManager()->getUser() : null;
            if(!$user) {
                $userManager = UserManager::getInstance();
                $user = $userManager->getSystemUser();
            }

            $params['updated_by'] = $user->getId();
        }
        return $mapper->updateByPk($item);
    }


    /**
     * @param $requestArgs
     * @param $itemId
     */
    public function updateItemRelations($requestArgs, $itemId)
    {
        $relationEntities = $this->getRelationEntities();
        $mapper = $this->getMapper();
        foreach ($relationEntities as $key => $relationEntity) {
            if ($relationEntity['relation_type'] !== 'many_to_many') {
                continue;
            }
            if (isset($relationEntity['only_for_rule']) && $relationEntity['only_for_rule']) {
                continue;
            }
            /** @var AbstractCmsManager $manager */
            $manager = $relationEntity['manager'];

            $itemFromRequest = null;
            if (is_array($requestArgs)) {
                if (!isset($requestArgs[$key])) {
                    $requestArgs[$key] = [];
                }
                $itemFromRequest = $requestArgs[$key];
            } else if (!is_array($requestArgs)) {
                if (!isset($requestArgs->$key)) {
                    $requestArgs->$key = [];
                }
                $itemFromRequest = $requestArgs->$key;
            }
            if (is_null($itemFromRequest)) {
                continue;
            }
            $items = $manager->getListByField($relationEntity['relation_field'], $itemId);
            $executedItems = [];
            $existingRelations = [];

            foreach ($itemFromRequest as $relativeTableId) {
                $existingItem = $manager->getDtoFromListByField($items, $relationEntity['field'], $relativeTableId);
                if($existingItem) {
                    $executedItems[] = $existingItem->getId();
                    $existingRelations[] = $relativeTableId;
                }
            }

            $manager->deleteByField($relationEntity['relation_field'], $itemId, $executedItems);

            foreach($itemFromRequest as $relativeTableId) {
                if(in_array($relativeTableId, $existingRelations)) {
                    continue;
                }

                $dataToSave = [];
                $dataToSave[$relationEntity['relation_field']] = $itemId;
                $dataToSave[$relationEntity['field']] = $relativeTableId;
                $manager->createItem($dataToSave);
            }

        }


    }


    /**
     * this function returns array which informs about delete problems (should has keys confirmation_text, error_reason)
     * if this function returns null, no delete problem
     *
     * @param int $deleteItemId
     * @return array|null
     */
    public function getDeleteProblems(int $deleteItemId): ?array
    {
        return null;
    }


    /**
     * NgsCmsParamsBin $paramsBin
     *
     * @return AbstractCmsDto[]
     */
    public function getList(NgsCmsParamsBin $paramsBin = null)
    {
        if ($paramsBin === null) {
            $paramsBin = new NgsCmsParamsBin();
        }
        return $this->getMapper()->getList($paramsBin);
    }


    /**
     * indicates if dto changed according to given data
     *
     * @param $dto
     * @param $data
     * @return bool
     */
    public function dtoChanged($dto, $data)
    {
        foreach ($data as $key => $value) {
            $getterFunction = StringUtil::getGetterByDbName($key);
            if (method_exists($dto, $getterFunction) && ($dto->$getterFunction() !== $value)) {
                return true;
            };
        }

        return false;
    }


    /**
     * returns dto from dtos array by field and value
     *
     * @param $dtos
     * @param $fieldName
     * @param $value
     * @param bool $caseInsensitive
     *
     * @return AbstractCmsDto|null
     */
    public function getDtoFromListByField($dtos, $fieldName, $value, $caseInsensitive = false)
    {
        $getter = StringUtil::getGetterByDbName($fieldName);
        foreach ($dtos as $dto) {
            if($caseInsensitive) {
                if (strtolower($dto->$getter()) == strtolower($value)) {
                    return $dto;
                }
            }
            else {
                if ($dto->$getter() == $value) {
                    return $dto;
                }
            }
        }

        return null;
    }
    

    public function getListByField($fieldName, $fieldValue)
    {
        return $this->getMapper()->getListByField($fieldName, $fieldValue);
    }


    /**
     * returns item ids found by given params
     *
     * @param NgsCmsParamsBin $paramsBin
     * @return array
     */
    public function getIdsByParams(NgsCmsParamsBin $paramsBin) {
        $itemsIds = $this->getMapper()->getIdsByParams($paramsBin);
        return $itemsIds;
    }


    /**
     * delete items by params
     *
     * @param NgsCmsParamsBin|null $paramsBin
     * @param bool $forceDelete (if no will use listing bin params override, as displayed for user in listing)
     *
     * @return bool
     */
    public function deleteByParams(NgsCmsParamsBin $paramsBin = null, bool $forceDelete = true)
    {
        if(!$forceDelete && $paramsBin) {
            $paramsBin = $this->modifyNgsListBinParams($paramsBin);
            $itemsIds = $this->getIdsByParams($paramsBin);
            if(!$itemsIds) {
                return true;
            }
            $paramsBin = new NgsCmsParamsBin();
            $paramsBin->setWhereAndCondition(['dto' => $this->createDto(), 'field' => 'id'], '(' . implode(',', $itemsIds) . ')', 'in');
        }

        if ($paramsBin === null) {
            $paramsBin = new NgsCmsParamsBin();
        }
        return $this->getMapper()->deleteByParams($paramsBin);
    }


    /**
     * @param $fieldName
     * @param $fieldValue
     * @param array $expectIds
     * @return bool
     */
    public function deleteByField($fieldName, $fieldValue, ?array $expectIds = [])
    {
        return $this->getMapper()->deleteByField($fieldName, $fieldValue, $expectIds);
    }


    /**
     * returns js add page load
     * @return string
     */
    public function getRowClickLoad(): string
    {
        return "";
    }


    /**
     * action used to bulk export items as excel
     * @return string
     */
    public function getBulkExcelExportAction(): string
    {
        return "";
    }

    /**
     * action used to change content in export items as excel  templates
     * @return string
     */
    public function getBulkExcelExportContentAction(): string
    {
        return "ngs.AdminTools.actions.exportTemplates.get";
    }

    /**
     * action used to bulk export items as excel
     * @return string
     */
    public function getExcelFileDownloadLoad(): string
    {
        return 'admin.loads.download.download';
    }


    /**
     * action used to bulk delete items
     * @return string
     */
    public function getBulkDeleteAction(): string
    {
        return "";
    }


    /**
     * @return string
     */
    public function getListLoad(): string
    {
        return "";
    }

    /**
     * returns js add page load
     * @return string
     */
    public function getAddLoad(): string
    {
        return '';
    }

    /**
     * returns js edit page load
     * @return string
     */
    public function getEditLoad(): string
    {
        return "";
    }

    /**
     * returns js main page load
     * @return string
     */
    public function getMainLoad(): string
    {
        return "";
    }

    /**
     * returns js export load
     * @return string
     */
    public function getExportLoad(): string
    {
        return "";
    }

    /**
     * returns js delete item action
     * @return string
     */
    public function getDeleteAction(): string
    {
        return "";
    }


    public function deleteItemById($itemId, bool $forceDelete = false)
    {
        return $this->getMapper()->deleteItemById($itemId, $forceDelete);
    }


    /**
     * @param array $ids
     * @return mixed
     */
    public function deleteItemsByIds(array $ids)
    {
        return $this->getMapper()->deleteItemsByIds($ids);
    }

    /**
     * @param string $itemId
     * @param NgsCmsParamsBin $paramsBin
     * @return AbstractCmsDto|null
     */
    public function getItemById(string $itemId, ?NgsCmsParamsBin $paramsBin = null)
    {
        return $this->getMapper()->getItemById($itemId, $paramsBin);
    }

    /**
     * returns items array by ids
     * 
     * @param array $itemIds
     * @return AbstractCmsDto[]
     */
    public function getItemsByIds(array $itemIds)
    {
        return $this->getMapper()->getItemsByIds($itemIds);
    }

    /**
     * @return AbstractCmsMapper
     */
    public abstract function getMapper();
    
    
      /**
     * @return null
     */
    public function getChildMapper()
    {
        return null;
    }
    

    /**
     * @return AbstractCmsDto
     */
    public function createDto()
    {
        return $this->getMapper()->createDto();
    }

    /**
     * get all items count
     *
     * @param NgsCmsParamsBin $paramsBin
     *
     * @return int
     */
    public function getItemsCount(NgsCmsParamsBin $paramsBin): int
    {
        return $this->getMapper()->getItemsCount($paramsBin);
    }


    /**
     * returns values for selection
     *
     * @param NgsCmsParamsBin $paramsBin
     * 
     * @return array
     */
    public function getSelectionList(NgsCmsParamsBin $paramsBin = null)
    {
        $key = 'default';
        if($paramsBin) {
            $key = md5(json_encode($paramsBin->getWhereCondition()));
        }
        if(!isset($this->selectionList[$key]) || !$this->selectionList[$key]) {
            if(!$paramsBin) {
                $paramsBin = new NgsCmsParamsBin();
                $paramsBin->setOffset(0);
                $paramsBin->setLimit(null);
            }
            $mapper = $this->getMapper();
            $items = $mapper->getList($paramsBin, true);

            $result = [];
            foreach ($items as $item) {
                $result[] = $item;
            }
            $this->selectionList[$key] = $result;
        }
        
        return $this->selectionList[$key];
    }


    /**
     * this function finds in db the correspond relation entity for custom items and if finds return first row
     * @param $id
     * @param $searchField
     * @param $tableName
     * @return mixed|\ngs\dal\dto\AbstractDto|null
     */
    public function getRelatedEntity($id, $searchField, $tableName)
    {
        $res = $this->getMapper()->getRelatedEntity($id, $searchField, $tableName);
        if (!empty($res)) {
            return $res[0];
        }
        return null;
    }


    /**
     * this function finds in db the correspond relation entity for custom items and if finds return first row
     * @param $id
     * @param $searchField
     * @param $tableName
     * @return mixed|\ngs\dal\dto\AbstractDto|null
     */
    public function getRelatedEntities($id, $searchField, $tableName)
    {
        $res = $this->getMapper()->getRelatedEntity($id, $searchField, $tableName);
        if (!empty($res)) {
            return $res;
        }
        return [];
    }


    /**
     * @param $field
     * @param $relationField
     * @param $relationFieldValue
     * @return array
     */
    public function getRelativeValues($field, $relationField, $relationFieldValue)
    {
        $items = $this->getListByField($relationField, $relationFieldValue);

        $result = [];
        $getter = 'get' . StringUtil::underlinesToCamelCase($field, true);
        foreach ($items as $item) {
            $result[] = $item->$getter();
        }
        return $result;
    }


    /**
     * @param NgsRuleDto $rule
     * @param NgsCmsParamsBin $paramsBin
     * @return bool|AbstractCmsDto[]
     */
    public function getItemsByRule(NgsRuleDto $rule, NgsCmsParamsBin $paramsBin)
    {

        return $this->getMapper()->getItemsByRule($rule, $paramsBin);
    }


    /**
     * @param NgsRuleDto $rule
     * @return int
     */
    public function getItemsCountByRule(NgsRuleDto $rule)
    {
        return $this->getMapper()->getItemsCountByRule($rule);
    }


    /**
     * indicates if object can be saved without validation as draft item
     *
     * @param AbstractCmsDto|null $item
     * @return bool
     */
    public function hasDraftSupport($item) :bool {
        return false;
    }


    /**
     * modify values for filter
     *
     * @param array $valuesForFilter
     * @return array
     */
    protected function modifyFilterValues(array $valuesForFilter) :array
    {
        return $valuesForFilter;
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
     * modify select values: adds 'is_default' => true to default values, which finds from getDefaultValuesOfFields
     * @param array $selectValues
     * @return array
     */
    protected function modifySelectionValues(array $selectValues) {
        $defaultValues = $this->getDefaultValuesOfFields();

        foreach ($defaultValues as $fieldName => $defaultValue) {
            if(!isset($selectValues[$fieldName])) {
                continue;
            }
            foreach ($selectValues[$fieldName] as $key => $value) {
                if($value['value'] === $defaultValue) {
                    $selectValues[$fieldName][$key]['is_default'] = true;
                }
            }
        }
        return $selectValues;
    }


    /**
     * can modify validators
     *
     * @param array $validators
     * @param object|null $request
     * @param array|null $requestData
     * @param null|AbstractCmsDto $dto
     * @return array
     */
    protected function modifyPreparedValidators(array $validators, $request, ?array $requestData = [], ?AbstractCmsDto $dto = null) {
        if($requestData) {
            $itemId = $requestData['id'] ?? null;
            $companyId = $requestData['company_id'] ?? null;
        }
        else {
            $itemId = $request->id ? $request->id : null;
            $companyId = $request->company_id ? $request->company_id : null;
        }

        foreach($validators as $validatorClass => $valuesToValidate) {
            if(isset($valuesToValidate[0])) {
                foreach($valuesToValidate as $key => $valueToValidate) {
                    if(isset($valueToValidate['data']) && $valueToValidate['data']) {
                        if ($itemId) {
                            $valuesToValidate[$key]['data']['item_id'] = $itemId;
                        }
                        if ($companyId) {
                            $valuesToValidate[$key]['data']['company_id'] = $companyId;
                        }
                    }
                }
                $validators[$validatorClass] = $valuesToValidate;
            }
            else {
                if(isset($valuesToValidate['additional_params']) && $valuesToValidate['additional_params']) {
                    if ($itemId) {
                        $validators[$validatorClass]['additional_params']['item_id'] = $itemId;
                    }
                    if ($companyId) {
                        $validators[$validatorClass]['additional_params']['company_id'] = $companyId;
                    }
                }
            }
        }

        return $validators;
    }


    /**
     * by using mapArray sets addEditFieldMethods which are used to fill request data
     *
     *
     * @param string $type
     * @param AbstractCmsDto $cmsDto
     *
     * @return array
     */
    private function getAddEditFieldsMethods(string $type, $cmsDto = null): array
    {
        if(!$cmsDto) {
            $cmsDto = $this->createDto();
        }
        $visibleFieldsMethods = $cmsDto->getAddEditFieldsMethods($type);
        if (count($visibleFieldsMethods)) {
            return $visibleFieldsMethods;
        }

        return [];
    }


    /**
     * validate request parameters
     *
     * @param string|null $type
     * @param array|null $editFields
     * @param array $additionalValidators
     * @param object|null $request
     * @param array $requestData
     * @param null|AbstractCmsDto $dto
     *
     * @return array
     */
    private function validateRequest(?string $type, ?array $editFields, array $additionalValidators, $request, array $requestData = null, ?AbstractCmsDto $dto = null) {
        $fieldsWithValidators = $this->getValidators($type, $editFields, $additionalValidators);
        $validators = ValidateUtil::prepareValidators($fieldsWithValidators, $request, $requestData);
        $validators = $this->modifyPreparedValidators($validators, $request, $requestData, $dto);
        $result = ValidateUtil::validateRequestData($validators);
        return $result;
    }


    /**
     * returns additional validators for fields
     *
     * @param string|null $type
     * @param array|null $editFields
     * @param array $additionalValidators
     *
     * @return array
     */
    public function getValidators(?string $type, ?array $editFields = [], array $additionalValidators = []) :array {
        if(!$editFields) {
            $editFields = $this->getAddEditFieldsMethods($type === 'add' ? $type : 'edit', null);
        }

        $validatorsByFields = ValidateUtil::getFieldsValidators($editFields);

        if($additionalValidators) {
            foreach($additionalValidators as $field => $validators) {

                if(!isset($validatorsByFields[$field])) {
                    $validatorsByFields[$field] = [];
                }
                $validatorsByFields[$field] = array_merge($validatorsByFields[$field], $validators);
            }
        }

        return $validatorsByFields;
    }
}
