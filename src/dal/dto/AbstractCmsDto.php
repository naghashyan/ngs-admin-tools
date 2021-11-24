<?php
/**
 * @author Mikael Mkrtchyan
 * @site http://naghashyan.com
 * @mail mikael.mkrtchyan@naghashyan.com
 * @year 2017
 * @package ngs.NgsAdminTools.dal.dto
 * @version 7.0
 *
 */

namespace ngs\NgsAdminTools\dal\dto;


use ngs\NgsAdminTools\dal\dto\AbstractSecureDto;
use ngs\NgsAdminTools\util\StringUtil;

abstract class AbstractCmsDto extends AbstractSecureDto
{

    protected string $tableName = '';
    private $_cmsParentObject = null;
    protected array $mapArray = [];
    protected array $cmsDefaultMapArrayValues = [
        'tab' => 'Main',
        'group_name' => 'General',
        'type' => 'text',//number, text, select, checkbox, email, date, time
        'display_name' => 'ID',
        'field_name' => 'id',
        'virtual' => false,
        'visible' => true,
        'sortable' => false,
        'actions' => [],
        'required_in' => []
    ];

    /**
     * @param bool $withoutCreators
     * @return array
     */
    public function getMapArray($withoutCreators = false): array
    {
        $result = [];
        foreach ($this->mapArray as $key => $value) {
            if($withoutCreators && in_array($key, ['created_by_name', 'updated_by_name'])) {
                continue;
            }
            if (isset($value['relative']) && $value['relative']) {
                continue;
            }
            $value['field_name'] = $value['field_name'] ?? preg_replace_callback('/_([a-z0-9])/', static function ($property): string {
                    if (!isset($property[1])) {
                        return '';
                    }
                    return ucfirst($property[1]);
                }, ($key));
            if (isset($value['virtual']) && $value['virtual'] === true) {
                continue;
            }
            $result[$key] = $value['field_name'];
        }
        return $result;
    }

    /**
     * @param bool $withoutCreators
     * @return array
     */
    public function getCmsMapArray($withoutCreators = false): array
    {
        $result = [];
        foreach ($this->mapArray as $key => $value) {
            if($withoutCreators && in_array($key, ['created_by_name', 'updated_by_name'])) {
                continue;
            }
            $value['display_name'] = $value['display_name'] ?? ucwords(str_replace('_', ' ', $key));
            $value['field_name'] = $value['field_name'] ?? StringUtil::getElementFunctionByName($key, '');
            $value['virtual'] = $value['virtual'] ?? $this->cmsDefaultMapArrayValues['virtual'];
            if ($value['virtual'] === true) {
                continue;
            }
            $value['type'] = $value['type'] ?? $this->cmsDefaultMapArrayValues['type'];
            $value['rule'] = isset($value['rule']) && $value['rule'] ? $value['rule'] : null;
            $value['action_type'] = $value['type'];
            $value['sortable'] = $value['sortable'] ?? $this->cmsDefaultMapArrayValues['sortable'];
            $value['required_in'] = $value['required_in'] ?? $this->cmsDefaultMapArrayValues['required_in'];
            $value['actions'] = $value['actions'] ?? $this->cmsDefaultMapArrayValues['actions'];
            $value['security_configurable'] = $value['security_configurable'] ?? true;
            $result[$key] = $value;
        }
        return $result;
    }

    /**
     * returns dto translatable fields
     *
     * @return array
     */
    public function getTranslatableFields():array {
        $result = [];

        foreach ($this->mapArray as $key => $value) {
            if(isset($value['translatable']) && $value['translatable']) {
                $result[] = $key;
            }
        }

        return $result;
    }

    public function getTableName(): string
    {
        return $this->tableName;
    }

    public function __call($m, $a)
    {
        return parent::__call($m, $a);
    }

    /**
     * @return array
     */
    public function getVisibleFields(): array
    {
        $result = [];
        foreach ($this->getCmsMapArray() as $key => $value) {
            if (isset($value['visible']) && $value['visible']) {
                $result[$value['field_name']] = ['type' => $value['type'], 'display_name' => $value['display_name'],
                    'data_field_name' => $key, 'sortable' => false, 'default_value' => null];
                if (isset($value['sortable']) && $value['sortable'] === true) {
                    $result[$value['field_name']]['sortable'] = true;
                }
                if (isset($value['default_value'])) {
                    $result[$value['field_name']]['default_value'] = $value['default_value'];
                }
            }

        }
        return $result;
    }

    private function _getParentDto()
    {
        if ($this->_cmsParentObject) {
            return $this->_cmsParentObject;
        }
        $this->_cmsParentObject = $this->getParentDto();
        return $this->_cmsParentObject;
    }

    protected function getParentDto()
    {

        return null;
    }


    /**
     * get field rule name if exists, otherwise returns null
     *
     * @param string $fieldKey
     *
     * @return string|null
     */
    public function getFieldRule(string $fieldKey) {
        $mapArray = $this->getCmsMapArray();
        $fieldData = isset($mapArray[$fieldKey]) ? $mapArray[$fieldKey] : null;
        if(!$fieldData) {
            return null;
        }

        return isset($fieldData['rule']) ? $fieldData['rule'] : null;
    }


    /**
     * @return array
     */
    public function getVisibleFieldsMethods(): array
    {
        $visibleFieldsGetters = [];
        $visibleFields = $this->getVisibleFields();
        foreach ($visibleFields as $key => $value) {
            $visibleFieldsGetters['get' . ucfirst($key)] = $value;
        }
        return $visibleFieldsGetters;
    }

    /**
     * @param string $actionType
     * @return array
     */
    public function getAddEditFields(string $actionType): array
    {
        $actionType = $actionType === 'add' ? $actionType : 'edit';
        $result = [];
        foreach ($this->getCmsMapArray() as $key => $value) {
            if (isset($value['actions']) && in_array($actionType, $value['actions'], true)) {

                $validators = isset($value['validators']) ? $value['validators'] : [];
                if($validators && method_exists($this, 'getId') && $this->getId() > 0) {
                    foreach($validators as $validatorKey => $validator) {
                        if(!isset($validator['data'])) {
                            $validators[$validatorKey]['data'] = [];
                        }
                        $validators[$validatorKey]['data']['item_id'] = $this->getId();
                        $validators[$validatorKey]['data']['company_id'] = null;
                        if(method_exists($this, 'getCompanyId')) {
                            $validators[$validatorKey]['data']['company_id'] = $this->getCompanyId();
                        }
                    }
                }

                $result[$value['field_name']] = [
                    'action_type' => $value['type'],
                    'type' => '',
                    'display_name' => $value['display_name'],
                    'data_field_name' => $key,
                    'data' => isset($value['data']) ? $value['data'] : null,
                    'is_new_line' => isset($value['is_new_line']) && $value['is_new_line'],
                    'help_text' => isset($value['help_text']) && $value['help_text'] ? $value['help_text'] : "",
                    'tab' => $value['tab'] ?? 'main',
                    'validators' => $validators,
                    'rule' => isset($value['rule']) && $value['rule']? $value['rule'] : null,
                    'group_name' => $value['group_name'] ?? $value['display_name'],
                    'default_value' => $value['default_value'] ?? null,
                    'required' => isset($value['required_in']) && in_array($actionType, $value['required_in'], true),
                    'relative' => isset($value['relative']) && $value['relative'] ? true : false,
                    'need_validation' => isset($value['need_validation']) && $value['need_validation'] ? true : false
                ];
            }
        }
        return $result;
    }

    public function getNgsCmsTabsArray(): array
    {
        return [];
    }

    /**
     * @param string $actionType
     * @return array
     */
    public function getAddEditFieldsMethods(string $actionType): array
    {
        $visibleFieldsGetters = [];
        $visibleFields = $this->getAddEditFields($actionType);
        foreach ($visibleFields as $key => $value) {
            $visibleFieldsGetters['get' . ucfirst($key)] = $value;
        }
        return $visibleFieldsGetters;
    }

}