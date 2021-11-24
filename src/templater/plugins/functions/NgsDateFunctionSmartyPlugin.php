<?php

/**
 * date function plugin for smarty
 */

namespace ngs\NgsAdminTools\templater\plugins\functions;

class NgsDateFunctionSmartyPlugin extends AbstractFunctionSmartyPlugin
{

    /**
     * returns plugin name
     *
     * @return string
     */
    public function getPluginName(): string
    {
        return 'ngsDate';
    }


    /**
     * main function which will be called when plugin used in frontend
     *
     * @param $params
     * @param $template
     * @return string
     * @throws \ngs\exceptions\DebugException
     */
    public function index($params, $template): string
    {
        $dto = $this->getDtoFromVariables();
        if(!$dto->hasReadAccess($params['name'])){
            return "";
        }
        $helpText = $this->getHelpText($params);
        $innerDate = '';
        $classToFormItem = isset($params['class_form_item']) ? " " .$params['class_form_item'] : " ";
        $classToInputField = isset($params['class_input_field']) ? " " .$params['class_input_field'] : " ";
        $containerId = isset($params['id']) ? 'id="' . $params['id'] .'" ' : " ";
        $fieldGetter = 'get' . $this->underlinesToCamelCase($params['name']);

        if($dto->$fieldGetter()) {
            $innerDate = $dto->$fieldGetter();
        }
        $templateParams = [
            'name' => $params['name'],
            'sage_sync' => isset($params['sync_with_sage']) && $params['sync_with_sage'],
            'helpText' => $helpText,
            'displayName' => isset($params['display_name']) ? $params['display_name'] : $this->getDisplayName($params['name']),
            'innerDate' => $innerDate,
            'class_to_form_item' => $classToFormItem,
            'class_to_input_field' => $classToInputField,
            'container_id' => $containerId,
            'element_id' => $dto->getTableName() . '_' . $params['name'] . '_input'
        ];

        return $this->getFunctionTemplate($templateParams);
    }


    /**
     * this function should returns string which will be used in function plugin
     *
     * @param array $params
     * @return string
     */
    protected function getFunctionTemplate(array $params): string
    {
        $syncSage = $params['sage_sync'] ? '<i class="icon-sage-logo-svg syncable-field-icon"><div class="tooltip">Sage field</div></i>' : '';

        return '<div' . $params['container_id'] . ' class="form-item date-form-item' .$params['class_to_form_item'] .'">
                    <div class="input-field'. $params['class_to_input_field'] .'">
                        <label>'. $params['displayName'] . '</label>' . $syncSage . '
                            <input id="' . $params['element_id'] . '"
                               name="' . $params['name'] . '" type="text"
                               class="f_flatpickr-datepicker"
                               placeholder="' . $params['displayName'] . '"
                               value="' . $params['innerDate'] . '">
                    </div>
                    <div class="icons-box">
                        <label style="cursor: pointer" for="'.  $params['element_id'] . '"><span class="icon-calendar"></span></label>
                        ' .$params['helpText']. ' 
                    </div>
                    
                    
                    
                </div>';
    }
}