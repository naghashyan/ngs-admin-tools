<?php

/**
 * checkbox function plugin for smarty
 */
namespace ngs\AdminTools\templater\plugins\functions;

class NgsCheckboxFunctionSmartyPlugin extends AbstractFunctionSmartyPlugin
{

    /**
     * returns plugin name
     *
     * @return string
     */
    public function getPluginName(): string
    {
        return 'ngsCheckbox';
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
        $viewMode = false;
        $dto = $this->getDtoFromVariables();
        if(!$dto->hasReadAccess($params['name'])) {
            return "";
        }

        if((!$dto->hasWriteAccess($params['name'])) || isset($params['view_mode']) && $params['view_mode']){
            $viewMode = true;
        }

        $helpText = $this->getHelpText($params);

        $classToFormItem = isset($params['class_form_item']) ? " " .$params['class_form_item'] : " ";
        $classToInputField = isset($params['class_input_field']) ? " " .$params['class_input_field'] : " ";
        $containerId = isset($params['id']) ? 'id="' . $params['id'] .'" ' : " ";
        $fieldGetter = 'get' . $this->underlinesToCamelCase($params['name']);



        $templateParams = [
            'name' => $params['name'],
            'sage_sync' => isset($params['sync_with_sage']) && $params['sync_with_sage'],
            'h2_sync' => isset($params['sync_with_h2']) && $params['sync_with_h2'],
            'displayName' => isset($params['display_name']) ? $params['display_name'] : $this->getDisplayName($params['name']),
            'helpText' => $helpText,
            'class_to_form_item' => $classToFormItem,
            'class_to_input_field' => $classToInputField,
            'container_id' => $containerId,
            'viewMode' => $viewMode,
            'element_id' => $dto->getTableName() . '_' . $params['name'] . '_input',
            'is_disabled' => isset($params['is_disabled']) && $params['is_disabled']
        ];

        if(($dto->getId() || $dto->getTempId())) {
            if($dto->$fieldGetter()){
                $templateParams['checked'] = ($viewMode)? ' checked' : 'checked="checked"';
            }else{
                $templateParams['checked'] = " ";
            }
        }else{
            if($this->isCheckboxDefaultChecked($params['name'])) {
                $templateParams['checked'] = ' checked';
            }else {
                $templateParams['checked'] = ' ';
            }
        }

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
        $syncSage .= $params['h2_sync'] ? '<i class="icon-master-icon master-field-icon"><div class="tooltip">Catalog master field</div></i>' : '';
        $disabled = $params['is_disabled'] ? ' disabled ' : ' ';

        if(!$params['viewMode']){
            return '<div' . $params['container_id']. 'class="form-item' .$params['class_to_form_item'] .'">
                    <div class="checkbox-item">
                        <label for="' . $params['element_id'] . '">
                            <input' . $disabled . 'type="checkbox" ' . $params['checked'] . ' id="' . $params['element_id'] . '" name="' . $params['name'] . '" 
                            class="filled-in check-item' . $params['class_to_input_field'] . '">
                            <span></span>
                                ' . $params['displayName'] . '
                        </label>' . $syncSage . '
                    </div>
                    <div class="icons-box">
                        ' .$params['helpText']. '
                    </div>
                </div>';
        }else{
            return '<div class="form-item view-mode">
                   <div class="checkbox-item">
                   
                    <label>
                     <span class="view-checkbox' .$params['checked']. '">
                        <i class="icon-svg257 not-checked"></i>
                     </span>'
                . $params['displayName'] .
                '</label>' . $syncSage . '
                </div>
                    <div class="icons-box">
                        ' .$params['helpText']. '
                    </div>
                </div>';
        }

    }

}
