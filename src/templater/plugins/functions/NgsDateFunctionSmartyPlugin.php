<?php

/**
 * date function plugin for smarty
 */

namespace ngs\AdminTools\templater\plugins\functions;

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
                        <label class="date-icon" style="cursor: pointer" for="'.  $params['element_id'] . '"><svg id="Layer_2" data-name="Layer 2" xmlns="http://www.w3.org/2000/svg" width="17.975" height="17.975" viewBox="0 0 17.975 17.975">
                            <path id="Path_70140" data-name="Path 70140" d="M17.519,21.542H6.457A3.452,3.452,0,0,1,3,18.1V8.446A3.452,3.452,0,0,1,6.457,5H17.519a3.452,3.452,0,0,1,3.457,3.446V18.1A3.452,3.452,0,0,1,17.519,21.542ZM6.457,6.379A2.071,2.071,0,0,0,4.383,8.446V18.1a2.071,2.071,0,0,0,2.074,2.068H17.519A2.071,2.071,0,0,0,19.593,18.1V8.446a2.071,2.071,0,0,0-2.074-2.068Z" transform="translate(-3 -3.567)"/>
                            <path id="Path_70141" data-name="Path 70141" d="M22.583,23.3H19.717A.717.717,0,0,1,19,22.583V19.717A.717.717,0,0,1,19.717,19h2.867a.717.717,0,0,1,.717.717v2.867A.717.717,0,0,1,22.583,23.3Zm-2.15-1.433h1.433V20.433H20.433Z" transform="translate(-8.059 -8.059)" stroke="#fff" stroke-width="0.5"/>
                            <path id="Path_70142" data-name="Path 70142" d="M20.284,12.433H3.691a.717.717,0,0,1,0-1.433H20.284a.717.717,0,0,1,0,1.433Z" transform="translate(-3 -5.486)"/>
                            <path id="Path_70143" data-name="Path 70143" d="M10.717,7.3A.717.717,0,0,1,10,6.583V3.717a.717.717,0,1,1,1.433,0V6.583A.717.717,0,0,1,10.717,7.3Z" transform="translate(-5.175 -3)"/>
                            <path id="Path_70144" data-name="Path 70144" d="M20.717,7.3A.717.717,0,0,1,20,6.583V3.717a.717.717,0,1,1,1.433,0V6.583A.717.717,0,0,1,20.717,7.3Z" transform="translate(-8.283 -3)"/>
                        </svg></label>
                        ' .$params['helpText']. ' 
                    </div>
                    
                    
                    
                </div>';
    }
}
