<?php

/**
 * date function plugin for smarty
 */

namespace ngs\AdminTools\templater\plugins\functions;

class NgsTimeFunctionSmartyPlugin extends AbstractFunctionSmartyPlugin
{

    /**
     * returns plugin name
     *
     * @return string
     */
    public function getPluginName(): string
    {
        return 'ngsTime';
    }

    /**
     * main function which will be called when plugin used in frontend
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
        $innerDate = '';
        $classToFormItem = isset($params['class_form_item']) ? " " .$params['class_form_item'] : " ";
        $classToInputField = isset($params['class_input_field']) ? " " .$params['class_input_field'] : " ";
        $containerId = isset($params['id']) ? 'id="' . $params['id'] .'" ' : " ";
        $fieldGetter = 'get' . $this->underlinesToCamelCase($params['name']);
        if($dto->$fieldGetter()) {
            $innerDate = strftime('%H:%M', strtotime($dto->$fieldGetter()));
        }
        $templateParams = [
            'name' => $params['name'],
            'sage_sync' => isset($params['sync_with_sage']) && $params['sync_with_sage'],
            'h2_sync' => isset($params['sync_with_h2']) && $params['sync_with_h2'],
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
        $syncSage .= $params['h2_sync'] ? '<i class="icon-master-icon master-field-icon"><div class="tooltip">Catalog master field</div></i>' : '';

        return '<div '.$params['container_id'].' class="form-item' .$params['class_to_form_item'] .'">
                    <div class="input-field'. $params['class_to_input_field'] .'">
                        <label for="'. $params['element_id'] .'">'. $params['displayName'] . '</label>' . $syncSage . '
                            <input id="' . $params['element_id'] . '"
                               name="' . $params['name'] . '" type="text"
                               class="f_flatpickr-timepicker"
                               placeholder="' . $params['displayName'] . '"
                               value="' . $params['innerDate'] . '"
                               data-ngs-validate="string">
                    </div>
                </div>';
    }
}