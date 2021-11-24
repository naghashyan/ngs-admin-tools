<?php

/**
 * text function plugin for smarty
 */

namespace ngs\NgsAdminTools\templater\plugins\functions;

class NgsNumberFunctionSmartyPlugin extends AbstractFunctionSmartyPlugin
{

    /**
     * returns plugin name
     *
     * @return string
     */
    public function getPluginName(): string
    {
        return 'ngsNumber';
    }


    /**
     *
     * main function which will be called when plugin used in frontend
     *
     * @param $params
     * @param $template
     * @return string
     * @throws \ngs\exceptions\DebugException
     */
    public function index($params, $template): string
    {
        $helpText = $this->getHelpText($params);
        $innerText = "";
        $classToFormItem = isset($params['class_form_item']) ? " " .$params['class_form_item'] : " ";
        $classToInputField = isset($params['class_input_field']) ? " " .$params['class_input_field'] : " ";
        $containerId = isset($params['id']) ? 'id="' . $params['id'] .'" ' : " ";

        $dto = $this->getDtoFromVariables();
        if(!$dto->hasReadAccess($params['name'])){
            return "";
        }

        $fieldGetter = 'get' . $this->underlinesToCamelCase($params['name']);
        if(($dto->getId() || $dto->getTempId())) {
            $innerText = $dto->$fieldGetter();
        }
        $templateParams = [
            'name' => $params['name'],
            'sage_sync' => isset($params['sync_with_sage']) && $params['sync_with_sage'],
            'has_error_field' => isset($params['has_error_field']) && $params['has_error_field'],
            'displayName' => isset($params['display_name']) ? $params['display_name'] : $this->getDisplayName($params['name']),
            'innerText' => $innerText,
            'additional_html_after_input' => isset($params['additional_html_after_input']) && $params['additional_html_after_input'] ? $params['additional_html_after_input'] : ' ',
            'helpText' => $helpText,
            'class_to_form_item' => $classToFormItem,
            'class_to_input_field' => $classToInputField,
            'container_id' => $containerId,
            'only_positive' => (isset($params['only_positive']) && $params['only_positive'])? ' only_positive=true ' : '',
            'element_id' => $dto->getTableName() . '_' . $params['name'] . '_input'
        ];

        return $this->getFunctionTemplate($templateParams);
    }


    /**
     * this function should returns string which will be used in function plugin
     * @param array $params
     * @return string
     */
    protected function getFunctionTemplate(array $params): string
    {
        $additionalHTMLAfterInput = $params['additional_html_after_input'];
        $syncSage = $params['sage_sync'] ? '<i class="icon-sage-logo-svg syncable-field-icon"><div class="tooltip">Sage field</div></i>' : '';
        $errorField = $params['has_error_field'] ?  '<span class="f_field-error is_hidden field-error"></span>' : '';

        return '<div '.$params['container_id'].' class="form-item' .$params['class_to_form_item'] .'">
                    <div class="input-field' . $params['class_to_input_field'] . '">
                    
                        <label for="' . $params['element_id'] . '">'. $params['displayName'] . '</label>' . $syncSage . '
                            <input id="' . $params['element_id'] . '"
                               name="' . $params['name'] . '" type="number"
                               placeholder="' . $params['displayName'] . '"'
                                . $params['only_positive']
                                .'value="' . $params['innerText'] . '">'
                                . $additionalHTMLAfterInput
                                . $errorField . '
                    </div>
                    <div class="icons-box">
                        ' .$params['helpText']. '
                    </div>
                </div>';
    }
}
