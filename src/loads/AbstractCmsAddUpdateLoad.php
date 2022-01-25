<?php
/**
 * General parent load for all
 * cms add and update(edit) loads
 *
 * @author Mikael Mkrtchyan
 * @site   https://naghashyan.com
 * @email  mikael.mkrtchyan@naghashyan.com
 * @year   2021
 * @package ngs.AdminTools.loads
 * @version 1.0
 * @copyright Naghashyan Solutions
 *
 **/

namespace ngs\AdminTools\loads;


use ngs\AdminTools\dal\binparams\NgsCmsParamsBin;
use ngs\AdminTools\dal\dto\AbstractCmsDto;
use ngs\AdminTools\managers\AbstractCmsManager;
use ngs\AdminTools\managers\NgsRuleManager;
use ngs\AdminTools\managers\TranslationManager;
use ngs\AdminTools\util\StringUtil;
use ngs\AdminTools\util\ValidateUtil;

abstract class AbstractCmsAddUpdateLoad extends AbstractCmsLoad
{


    /**
     * @var array
     */
    private array $addEditFieldsMethods = [];
    protected array $nestLoads = [];
    public const NGS_CMS_EDIT_ACTION_TYPE_POPUP = 'popup';
    public const NGS_CMS_EDIT_ACTION_TYPE_INPLACE = 'inplace';


    /**
     * @param array $visibleFieldsMethods
     */
    public function setAddEditFieldsMethods(array $visibleFieldsMethods): void
    {
        $this->addEditFieldsMethods = $visibleFieldsMethods;
    }


    /**
     * @return array
     */
    public function getAddEditFieldsMethods(): array
    {
        $result = [];
        foreach ($this->addEditFieldsMethods as $key => $addEditFieldMethod) {
            $result[$addEditFieldMethod['tab']][$addEditFieldMethod['group_name']][$key] = $addEditFieldMethod;
        }
        return $result;
    }


    /**
     * @param AbstractCmsDto|null $itemDto
     * @return array
     */
    public function getCmsAdditionalTabs(?AbstractCmsDto $itemDto): array
    {
        if (!$itemDto) {
            $itemDto = $this->getManager()->createDto();
            $itemDto->setId(-1);
        }
        $additionalTabs = $itemDto->getNgsCmsTabsArray();
        foreach ($additionalTabs as $key => $tab) {
            $tabUID = uniqid('ngs-AdminTools-', false);
            $additionalTabs[$key]['nest_uid'] = $tabUID;
            $this->nestLoads[$tabUID] = [
                'action' => $tab['action'],
                'args' => [
                    'parentId' => $itemDto->getId(),
                    'cmsModal' => true,
                    'cmsUUID' => $tabUID
                ]
            ];
        }
        return $additionalTabs;

    }


    /**
     * @return string
     */
    public function getTemplate(): string
    {
        return NGS()->getTemplateDir('ngs-AdminTools') . '/add_update.tpl';
    }

    /**
     * returns get edit action type
     * @return string
     */
    public function getEditActionType(): string
    {
        return '';
    }

    /**
     * @return AbstractCmsManager
     */
    abstract public function getManager();

    /**
     * @return int
     */
    abstract public function getItemId(): int;


    /**
     * @return null
     */
    public function getItemObject()
    {
        return null;
    }

    /**
     * returns js cancel load page
     * @return string
     */
    abstract public function getCancelLoad(): string;

    /**
     * returns js save item action
     * @return string
     */
    abstract public function getSaveAction(): string;

    /**
     * @param $cmsDto
     * @param string $type
     */
    private function initializeAddEditFieldsMethods($cmsDto, string $type): void
    {
        $visibleFieldsMethods = $cmsDto->getAddEditFieldsMethods($type);
        if (count($visibleFieldsMethods)) {
            $this->setAddEditFieldsMethods($visibleFieldsMethods);
        }
    }


    /**
     * load the page
     */
    public final function load(): void
    {
        $manager = $this->getManager();
        $itemDto = null;
        $fieldsType = 'add';
        if ($this->getItemObject()) {
            $itemDto = $manager->createDto();
            $itemDto->fillDtoFromArray($this->getItemObject());
            $fieldsType = 'edit';
        } else if ($this->getItemId() && $this->getItemId() > 0) {
            $itemDto = $manager->getItemById($this->getItemId(), $this->getParamsBin($this->getItemId()));
            $fieldsType = 'edit';
        } else {
            $itemDto = $manager->createDto();
        }
        if ($this->getItemId() && $this->getItemId() > 0 && $manager->hasImage()) {
            $mainImageUrl = NGS()->getDefinedValue('MY_HOST') . '/streamer/images/' . $manager->getMapper()->getTableName() . '/0?objectId=' . $this->getItemId();
            $this->addParam('mainImage', $mainImageUrl);
        }

        $ngsRuleManager = NgsRuleManager::getInstance();
        if ($itemDto && $itemDto->getId()) {
            $itemDto = $ngsRuleManager->modifyDtoByRules($itemDto);
        }
        if ($itemDto && $itemDto->getId()) {
            $itemDto = $this->modifyDto($itemDto);
        }

        if ($manager->hasTranslations()) {
            $translationManager = TranslationManager::getInstance();
            $dtoForTranslation = $itemDto->getId() ? $itemDto : $manager->createDto();
            $translations = $translationManager->getDtoTranslations($dtoForTranslation);
            $this->addParam('languages', $this->getLanguages());
            $this->addJsonParam('translations', $translations);
        }

        if ($itemDto->getId() && method_exists($manager, 'getSyncAdapter')) {
            $this->addParam('syncStatus', $manager->getSyncStatus($itemDto));
        }

        if ($itemDto->getId() && !empty($manager->getEntitiesMainIdentifiers($itemDto))) {
            $this->addParam('entitiesMainIdentifiers', $manager->getEntitiesMainIdentifiers($itemDto));
        }

        $validators = $this->getValidators($fieldsType);
        $this->addJsonParam('fieldValidators', $validators);
        $this->getLogger()->info($fieldsType . ' load started ' . ($itemDto && $itemDto->getId() ? $itemDto->getId() : ""));
        $this->initializeAddEditFieldsMethods($manager->createDto(), $fieldsType);
        $visibleFields = $this->getAddEditFieldsMethods();
        $this->addParam('requiredFields', $this->getRequiredFields($visibleFields));
        $additionalTabs = $this->getCmsAdditionalTabs($itemDto);
        $ngsTabs = array_keys($visibleFields);
        foreach ($additionalTabs as $tab) {
            $ngsTabs[] = $tab['tab'];
        }
        $this->addParam('ngsTabs', $ngsTabs);
        $this->addParam('visibleFields', $this->getAddEditFieldsMethods());
        $this->addParam('additionalTabs', $additionalTabs);
        $this->addJsonParam('itemId', $this->getItemId());

        $this->addJsonParam('tableName', $itemDto->getTableName());

        $this->addJsonParam('editLoad', $manager->getEditLoad());
        $this->addJsonParam('cancelLoad', $this->getCancelLoad());
        $this->addJsonParam('saveAction', $this->getSaveAction());
        $this->addJsonParam('currentTabId', $this->args()->currentTabId);
        $this->addJsonParam('isViewMode', $this->isViewLoad());
        $editActionType = $this->getEditActionType();
        if ($editActionType === self::NGS_CMS_EDIT_ACTION_TYPE_POPUP || $this->args()->popUp) {
            $editActionType = self::NGS_CMS_EDIT_ACTION_TYPE_POPUP;
        } else {
            $editActionType = self::NGS_CMS_EDIT_ACTION_TYPE_INPLACE;
        }
        if ($this->args()->parentId) {
            $this->addJsonParam('parentId', $this->args()->parentId);
        }

        $this->addJsonParam('editActionType', $editActionType);
        $this->addParam('tempId', $this->args()->tempId ? $this->args()->tempId : "");
        $this->addParam('itemDto', $itemDto);
        $this->addParam('helpTexts', $manager->getHelpTexts());
        $this->addParam('defaultValues', $manager->getDefaultValuesOfFields());
        $this->addParam('possibleValues', $manager->getSelectionPossibleValues($itemDto));
        $this->addParam('relationValues', $manager->getRelativeSelectedValues($itemDto));

        $jsParams = ['itemId' => $this->args()->itemId, 'parentId' => $this->args()->parentId,
            'page' => $this->getCurrentPage(), 'limit' => $this->getLimit(), 'offset' => $this->getOffset(),
            'pagesShowed' => $this->getPagesShowed(), 'ordering' => $this->args()->ordering,
            'sorting' => $this->args()->sorting, 'searchKey' => $this->args()->searchKey];
        $this->addJsonParam('fromViewPage', !!$this->args()->fromViewPage);
        $this->addJsonParam('fromListingPage', !!$this->args()->fromListingPage);
        $this->addJsonParam('pageParams', $jsParams);
        $this->afterCmsLoad($itemDto);
        $this->getLogger()->info($fieldsType . ' load finished ' . ($itemDto && $itemDto->getId() ? $itemDto->getId() : ""));
    }


    /**
     * pass paramsBin to add sub selects bin params
     * @return NgsCmsParamsBin|null
     */
    protected function getParamsBin(int $itemId): ?NgsCmsParamsBin
    {
        return null;
    }


    /**
     * modify dto if needed
     * @param AbstractCmsDto $itemDto
     * @return AbstractCmsDto
     */
    protected function modifyDto(AbstractCmsDto $itemDto)
    {
        return $itemDto;
    }


    public function validate(): void
    {
        $validators = $this->getValidators();
        $validatorWeNeed = $this->args()->validator;
        $this->addParam("ngsValidator", true);
        if ($this->args()->itemId && isset($validatorWeNeed['data'])) {
            $validatorWeNeed['data']['item_id'] = $this->args()->itemId;
        }
        $fieldsWithValidators = [];
        if ($this->args()->fieldName) {
            $fieldName = $this->args()->fieldName;
            $fieldValidators = $validators[$fieldName];

            if (!$fieldValidators) {
                $this->addParam('valid', true);
                return;
            }
            $fieldsWithValidators = [$fieldName => [$validatorWeNeed]];
        } else {
            $fieldNames = $this->args()->fieldNames;
            foreach ($fieldNames as $fieldName) {
                $fieldValidators = $validators[$fieldName];
                if (!$fieldValidators) {
                    $this->addParam('valid', true);
                    return;
                }
                $modifiedValidator = null;
                foreach ($fieldValidators as $fieldValidator) {
                    if ($fieldValidator['class'] === $validatorWeNeed['class']) {
                        $modifiedValidator = $validatorWeNeed;
                        $modifiedValidator['as'] = $fieldValidator['as'];
                        break;
                    }
                }
                if ($modifiedValidator) {
                    $fieldsWithValidators[$fieldName] = [$modifiedValidator];
                }
            }
        }

        $validators = ValidateUtil::prepareValidators($fieldsWithValidators, $this->args(), null);
        $result = ValidateUtil::validateRequestData($validators);

        if ($result['errors']) {
            $this->addParam('valid', false);
            $errorInfo = [];
            if (!isset($validatorWeNeed['as'])) {
                foreach ($result['errors'] as $error) {
                    $errorInfo = $error['message'];
                }
            } else {
                foreach ($result['errors'] as $error) {
                    $errorInfo[$error['field']] = $error['message'];
                }
            }

            $this->addParam('message', $errorInfo);
            return;
        }

        $this->addParam('valid', true);
    }


    /**
     * returns fields validators json
     * @param string $type
     * @return array|null
     */
    public function getValidators(string $type = "add")
    {
        $manager = $this->getManager();

        if ($type === "add") {
            $load = $manager->getAddLoad();
        } else {
            $load = $manager->getEditLoad();
        }

        if (!$load) {
            return null;
        }
        $load = StringUtil::getClassNameFromText($load, "load");
        $loadObject = new $load();
        $action = StringUtil::getClassNameFromText($loadObject->getSaveAction(), "action");
        if (class_exists($action)) {
            $actionObject = new $action();
            $actionObject->initializeAddEditFieldsMethods($type);
            $validators = $actionObject->getValidators($this->getItemId());
            return $validators;
        } else {
            return $this->getValidatorsForLoadsWhichHaveNoAction($type);
        }

    }

    /**
     * @return array|null
     */
    public function getValidatorsForLoadsWhichHaveNoAction($type)
    {

        $validators = [];
        $cmsDto = $this->getManager()->createDto();

        $visibleFieldsMethods = $cmsDto->getAddEditFieldsMethods($type);
        if (count($visibleFieldsMethods)) {

            foreach ($visibleFieldsMethods as $methodKey => $methodValue) {
                $key = $methodValue['data_field_name'];
                if (!isset($result[$key])) {
                    $validators[$key] = [];
                }
                if (isset($methodValue['validators']) && $methodValue['validators']) {
                    $validators[$key] = $methodValue['validators'];
                }
            }
        }
        if (!$validators) {
            return null;
        }
        return $validators;
    }


    /**
     * @return array
     */

    public function getDefaultLoads(): array
    {

        return $this->nestLoads;
    }


    /**
     * after cms loaded
     * @param $itemDto
     */
    public function afterCmsLoad($itemDto): void
    {

    }

    /**
     * adds correspond items id, and loadName to template
     * @param $relationField
     * @param $loadName
     */
    public function addRelatedEntityId($relationField, $loadName, $variableName = null)
    {
        if (!$variableName) {
            $variableName = 'relatedEntity';
        }
        $this->addParam($variableName, ['id' => $relationField, 'loadName' => $loadName]);
    }


    public function isViewLoad()
    {
        return !!strpos(get_class($this), "View");
    }


    /**
     * TODO: should be added dto, mapper, managers for languages management
     *
     * returns list of languages
     * key - language id, value ['name' => 'languageName', 'code' => 'isoCode']
     * @return array
     */
    private function getLanguages() {
        return [];
    }


    private function getRequiredFields($visibleFields)
    {
        $result = [];
        foreach ($visibleFields as $tab => $tabFields) {
            foreach ($tabFields as $groupName => $groupFields) {
                foreach ($groupFields as $field => $fieldInfo) {
                    $result[$fieldInfo['data_field_name']] = $fieldInfo['required'];
                }
            }
        }
        return $result;
    }
}
