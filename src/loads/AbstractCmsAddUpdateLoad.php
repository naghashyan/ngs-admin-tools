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


use ngs\AdminTools\event\structure\AfterLoadEventStructure;
use ngs\event\EventManager;
use ngs\AdminTools\event\structure\BeforeLoadEventStructure;
use ngs\AdminTools\managers\LanguageManager;
use ngs\AdminTools\managers\MediasManager;
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
     * @return  string|int
     */
    abstract public function getItemId(): string|int;


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
        if($this->getItemId() && $this->getItemId() > 0) {
            $itemDto = $manager->getItemById($this->getItemId(), $this->getParamsBin($this->getItemId()));
            if(!$itemDto) {
                $this->onNotFound();
            }
        }
        $this->beforeCmsLoad();
        $itemDto = null;
        $fieldsType = 'add';
        if ($this->getItemObject()) {
            $itemDto = $manager->createDto();
            $itemDto->fillDtoFromArray($this->getItemObject());
            $fieldsType = 'edit';
        } else if ($this->getItemId() && $this->getItemId() > 0) {
            $itemDto = $itemDto ?: $manager->getItemById($this->getItemId(), $this->getParamsBin($this->getItemId()));
            $fieldsType = 'edit';
        } else {
            $itemDto = $manager->createDto();
        }

        $beforeLoadEvent = new BeforeLoadEventStructure(['id' => $this->getItemId()], get_class($this), $itemDto);
        $this->getEventManager()->dispatch($beforeLoadEvent);

        $this->addMainImage($this->getItemId(), $manager);

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
        $this->addJsonParam('hasDraftSupport', $manager->hasDraftSupport($itemDto));
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
        $this->addParam('possibleValues', $manager->getSelectionPossibleValues($itemDto, false, $this->getAdditionalDataToGetPossibleValues()));
        $this->addParam('relationValues', $manager->getRelativeSelectedValues($itemDto));

        $jsParams = ['itemId' => $this->args()->itemId, 'parentId' => $this->args()->parentId,
            'page' => $this->getCurrentPage(), 'limit' => $this->getLimit(), 'offset' => $this->getOffset(),
            'pagesShowed' => $this->getPagesShowed(), 'ordering' => $this->args()->ordering,
            'sorting' => $this->args()->sorting, 'searchKey' => $this->args()->searchKey];
        $this->addJsonParam('fromViewPage', !!$this->args()->fromViewPage);
        $this->addJsonParam('rowClickLoad', $manager->getRowClickLoad());
        $this->addJsonParam('fromListingPage', !!$this->args()->fromListingPage);
        $this->addJsonParam('pageParams', $jsParams);
        $this->addItemImagesProperties($itemDto);
        $this->addItemAttachedFilesProperties($itemDto);
        $this->afterCmsLoad($itemDto);
        $afterLoadEvent = new AfterLoadEventStructure(['id' => $this->getItemId()], get_class($this), $itemDto);
        $this->getEventManager()->dispatch($afterLoadEvent);
        $this->getLogger()->info($fieldsType . ' load finished ' . ($itemDto && $itemDto->getId() ? $itemDto->getId() : ""));

    }

    /**
     * redirects when item by id not found
     */
    protected function onNotFound() {
        $this->redirectTo('');
    }

    /**
     *
     * @param $itemId
     * @param $manager
     * @return void
     */
    public function addMainImage($itemId, $manager):void
    {
        if ($itemId && $itemId > 0 && $manager->hasImage()) {
            $image = MediasManager::getInstance()->getItemImageUrl($itemId, $manager->getMapper()->getTableName());
            $this->addParam('mainImage', $image);
        }
    }

    protected function getAdditionalDataToGetPossibleValues()
    {
        return [];
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
        $neededValidator = $this->args()->validator;
        $this->addParam("ngsValidator", true);
        if ($this->args()->itemId && isset($neededValidator['data'])) {
            $neededValidator['data']['item_id'] = $this->args()->itemId;
        }
        if ($this->args()->companyId && isset($neededValidator['data'])) {
            $neededValidator['data']['company_id'] = $this->args()->companyId;
        }
        $fieldsWithValidators = [];
        if ($this->args()->fieldName) {
            $fieldName = $this->args()->fieldName;
            $fieldValidators = $validators[$fieldName];

            if (!$fieldValidators) {
                $this->addParam('valid', true);
                return;
            }

            $foundValidator = $neededValidator;
            foreach ($fieldValidators as $fieldValidator) {
                if ($fieldValidator['class'] === $neededValidator['class']) {
                    $foundValidator = $fieldValidator;
                    if (isset($neededValidator['data']) && $neededValidator['data']) {
                        foreach ($neededValidator['data'] as $key => $value) {
                            $foundValidator['data'][$key] = $value;
                        }
                    }
                    break;
                }
            }

            $fieldsWithValidators = [$fieldName => [$foundValidator]];
        } else {
            $fieldNames = $this->args()->fieldNames;
            foreach ($fieldNames as $fieldName) {
                $fieldValidators = isset($validators[$fieldName]) ? $validators[$fieldName] : ValidateUtil::getVirtualFieldValidator($validators, $fieldName, $neededValidator['class']);
                if (!$fieldValidators) {
                    $this->addParam('valid', true);
                    return;
                }
                $modifiedValidator = null;
                foreach ($fieldValidators as $fieldValidator) {
                    if ($fieldValidator['class'] === $neededValidator['class']) {
                        $modifiedValidator = $neededValidator;
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
            if (!isset($neededValidator['as'])) {
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
        return $manager->getValidators($type, null);
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
     * before cms loaded
     */
    public function beforeCmsLoad(): void
    {

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
    private function getLanguages()
    {
        $languageManager = LanguageManager::getInstance();
        return $languageManager->getLanguagesList();
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


    protected function addItemImagesProperties($itemDto): void
    {
        $mediasManager = MediasManager::getInstance();
        if ($itemDto->getId() && $this->getManager()->hasImage()) {

            $itemImageProperties = $mediasManager->getItemImagesUrlsAndDescriptions($itemDto->getId(), $itemDto->getTableName());
            if ($itemImageProperties) {
                $this->addJsonParam('imagesUrls', $itemImageProperties);
            } else {
                $defaultUrl = [['url' => ['original' => $mediasManager->getDefaultImage($itemDto->getTableName())]]];
                $this->addJsonParam('imagesUrls', $defaultUrl);
                $this->addJsonParam('onlyDefaultImage', true);

            }
        }
    }


    /**
     *
     * @param $itemDto
     */
    protected function addItemAttachedFilesProperties($itemDto): void
    {
        $mediasManager = MediasManager::getInstance();
        if ($itemDto && $this->getManager()->hasAttachedFile()) {
            $itemAttachedFilesProperties = $mediasManager->getItemAttachedFilesProperties($itemDto->getId(), $this->getManager()->getMapper()->getTableName());
            if ($itemAttachedFilesProperties) {
                $this->addJsonParam('files', $itemAttachedFilesProperties);
            }
        }
    }
}
