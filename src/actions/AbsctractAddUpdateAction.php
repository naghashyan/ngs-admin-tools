<?php
/**
 * General parent cms add update action.
 *
 *
 * @author Mikael Mkrtcyan
 * @site https://naghashyan.com
 * @mail mikael.mkrtchyan@naghashyan.com
 * @year 2010-2019
 * @package ngs.NgsAdminTools.actions
 * @version 9.0.0
 *
 */

namespace ngs\NgsAdminTools\actions;

use ngs\NgsAdminTools\dal\dto\AbstractCmsDto;
use ngs\NgsAdminTools\exceptions\NgsValidationException;
use ngs\NgsAdminTools\managers\AbstractCmsManager;
use ngs\NgsAdminTools\managers\LogManager;
use ngs\NgsAdminTools\managers\TranslationManager;
use ngs\NgsAdminTools\util\ValidateUtil;
use ngs\exceptions\DebugException;
use ngs\exceptions\NgsErrorException;

abstract class AbsctractAddUpdateAction extends AbsctractCmsAction
{

    /**
     * return default action manager
     *
     * @return AbstractCmsManager
     */
    public abstract function getManager();

    /**
     * fields that should be set after save request
     *
     * @var array
     */
    private $addEditFieldsMethods = [];

    /**
     * sets addEditFieldMethods which are used to fill request data
     * @param array $visibleFieldsMethods
     */
    public function setAddEditFieldsMethods(array $visibleFieldsMethods): void
    {
        $this->addEditFieldsMethods = $visibleFieldsMethods;
    }

    /**
     * returns addEditFieldMethods which are used to fill request data
     * @return array
     */
    public function getAddEditFieldsMethods(): array
    {
        return $this->addEditFieldsMethods;
    }

    /**
     * by using mapArray sets addEditFieldMethods which are used to fill request data
     *
     *
     * @param string $type
     * @param AbstractCmsDto $cmsDto
     */
    public function initializeAddEditFieldsMethods(string $type, $cmsDto = null): void
    {
        if(!$cmsDto) {
            $cmsDto = $this->getManager()->createDto();
        }
        $visibleFieldsMethods = $cmsDto->getAddEditFieldsMethods($type);
        if (count($visibleFieldsMethods)) {
            $this->setAddEditFieldsMethods($visibleFieldsMethods);
        }
    }

    /**
     * @param $params
     * @return mixed
     */
    public function beforeService(array $params): array
    {
        return $params;
    }

    public final function service()
    {
        $action = 'create';
        $userId = NGS()->getSessionManager()->getUser()->getId();
        $tableName = $this->getManager()->getMapper()->getTableName();

        try {
            $manager = $this->getManager();

            $itemDto = null;
            if ($this->args()->id) {
                $action = 'update';

                $currentItem = $manager->getItemById($this->args()->id);
                if($currentItem->getSystem()) {
                    throw new \Exception('cannot edit this item');
                }
                if($this->args()->updated_at && $currentItem->getUpdated() && $this->args()->updated_at != $currentItem->getUpdated() && !$this->args()->confirmed_to_udpate) {
                    throw new NgsErrorException('item updated, do you want to override', -1, ['overrideIssue' => true]);
                }

                $params = $this->getRequestParameters('edit', $currentItem);
                $params = $this->beforeService($params);

                $this->getLogger()->info('update action started', $params);
                $this->loggerActionStart($params, $this->args()->id);
                $itemDto = $manager->updateItem($this->args()->id, $params, false);
                $manager->updateItemRelations($this->args(), $itemDto->getId());
            } else {
                $params = $this->getRequestParameters('add');
                $this->loggerActionStart($params);
                $params = $this->beforeService($params);
                $this->getLogger()->info('create action started', $params);
                $itemDto = $manager->createItem($params, false);

                $manager->updateItemRelations($this->args(), $itemDto->getId());
            }

            if($manager->hasTranslations()) {
                $translations = $this->args()->translations ? $this->args()->translations : [];
                $params['translations'] = $translations;
                $this->saveTranslations($itemDto, $translations);
            }



            $this->addParam('afterActionLoad', $this->getAfterActionLoad());
            $this->addParam('itemId', $itemDto->getId());

            $this->addParam('actionType', $action);
            $this->addParam('tableName', $tableName);

            $this->addPagingParameters();

            $this->afterService($itemDto);
            $this->loggerActionEnd($itemDto);
            $this->getLogger()->info('action finished');

            $logManager = LogManager::getInstance();
            $logManager->addLog($userId, $action, json_encode($params, JSON_UNESCAPED_UNICODE), $tableName, true, $itemDto->getId());
        }
        catch(\Exception $exp) {

            if(!isset($params)) {
                $params = $this->getParamsWithoutValidate($action);
            }

            if($manager->hasTranslations()) {
                $translations = $this->args()->translations ? $this->args()->translations : [];
                $params['translations'] = $translations;
            }

            $logManager = LogManager::getInstance();

            if($this->args()->id) {
                $id =  $this->args()->id;
            }else {
                $id = $itemDto ? $itemDto->getId() : null;
            }

            $logManager->addLog($userId, $action, json_encode($params, JSON_UNESCAPED_UNICODE), $tableName, false, $id, $exp->getMessage());

            $this->getLogger()->error('action failed ' . $exp->getMessage());
            $params = [];
            if($exp instanceof NgsErrorException) {
                $params = $exp->getParams();
            }
            throw new NgsErrorException("While " .$action.  " an item, was an error, this is the error text: \n" . $exp->getMessage(), -1, $params);
        }

    }



    /**
     * called after service function, gets in parameter deleted item DTO
     *
     * @param AbstractCmsDto $itemDto
     */
    public function afterService($itemDto): void
    {

    }

    protected function addPagingParameters()
    {
        $pageParams = [];
        if ($this->args()->pageParams && is_array($this->args()->pageParams)) {
            foreach ($this->args()->pageParams as $key => $pageParam) {
                if ($pageParam === 'null') {
                    continue;
                }
                $pageParams[$key] = $pageParam;
            }
        }
        $this->addParam('afterActionParams', $pageParams);
    }

    /**
     * returns load which will called after action
     * @return string
     */
    public function getAfterActionLoad(): ?string
    {
        return '';
    }

    /**
     * returns request parameters array formater as ['db_field_name' => value]
     *
     * @param string $type
     * @return array
     * @throws NgsErrorException
     * @throws NgsValidationException
     * @throws DebugException
     */
    public function getRequestParameters(string $type, $dto = null, array $requestData = null): array
    {
        $type = $type === 'add' ? $type : 'edit';
        $updateArr = [];
        $manager = $this->getManager();
        $this->initializeAddEditFieldsMethods($type, $dto);
        $dtoToCheck = $manager->createDto();
        $editFields = $this->getAddEditFieldsMethods();


        $validationResult = $this->validateRequest($requestData);
        if($validationResult['errors']) {
            $errorText = '<br />';
            foreach ($validationResult['errors'] as $error) {
                $errorText .= $error['message'] . '<br />';
            }

            $emptyDto = $manager->getMapper()->createDto();
            $mapArray = $emptyDto->getCmsMapArray();

            foreach ($mapArray as $key => $value) {
                $errorText = str_replace('>' . $key . '<', '>' . $value['display_name'] . '<', $errorText);
            }

            throw new NgsValidationException($errorText, 0, null, $validationResult['errors']);
        }
        else {
            $requestData = $validationResult['fields'];
        }
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

        $updateArr = array_merge($updateArr, $this->getAdditionalParams());
        return $updateArr;
    }

    /**
     * returns additional validators for fields
     *
     * @return array
     */
    public function getAdditionalValidators() :array {
        return [];
    }


    /**
     * @return array
     */
    public function getAdditionalParams(): array
    {
        return [];
    }


    /**
     * returns additional validators for fields
     *
     * @param int $itemId
     * @return array
     */
    public function getValidators(int $itemId = null) :array {
        $editFields = $this->getAddEditFieldsMethods();

        $result = [];
        foreach ($editFields as $methodKey => $methodValue) {
            $key = $methodValue['data_field_name'];
            if(!isset($result[$key])) {
                $result[$key] = [];
            }

            if(isset($methodValue['validators']) && $methodValue['validators']) {
                $result[$key] = $methodValue['validators'];
            }
        }


        $additionalValidators = $this->getAdditionalValidators();
        if($additionalValidators) {
            foreach($additionalValidators as $field => $validators) {

                if(!isset($result[$field])) {
                    $result[$field] = [];
                }
                $result[$field] = array_merge($result[$field], $validators);
            }
        }

        return $result;
    }


    /**
     * save dto translations
     *
     * @param $dto
     * @param array $translations
     * @return bool
     */
    private function saveTranslations($dto, array $translations) {
        if(!$translations) {
            return true;
        }

        $translationManager = TranslationManager::getInstance();
        return $translationManager->saveDtoTranslations($dto, $translations);
    }


    /**
     * validate request parameters
     *
     * @param array $requestData
     * @return array
     */
    private function validateRequest(array $requestData = null) {
        $fieldsWithValidators = $this->getValidators();
        $validators = ValidateUtil::prepareValidators($fieldsWithValidators, $this->args(), $requestData);

        $result = ValidateUtil::validateRequestData($validators);
        return $result;
    }

    /**
     * this function is for getting params that was sent from ui just to log them to ngs_logs table
     * @return array
     */
    private function getParamsWithoutValidate($action) :array {
        $fields = $this->getManager()->createDto()->getAddEditFields($action);

        $res = [];

        foreach ($fields as $fieldName => $field) {
            if($field['action_type'] === 'password') {
                continue;
            }
            if($field['action_type'] === 'checkbox') {
                if(!$this->args()->$fieldName) {
                    $res[$fieldName] = 0;
                }else {
                    $res[$fieldName] = 1;
                }
            }else {
                if(is_string($this->args()->$fieldName)) {
                    $res[$fieldName] = trim($this->args()->$fieldName);
                }else {
                    $res[$fieldName] = $this->args()->$fieldName;
                }

                $res[$fieldName] = is_string($this->args()->$fieldName)? trim($this->args()->$fieldName) : $this->args()->$fieldName;
            }
        }

        return array_merge($res, $this->getAdditionalParams());


    }


}