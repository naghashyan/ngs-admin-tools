<?php

/**
 * text function plugin for smarty
 */

namespace ngs\AdminTools\templater\plugins\functions;

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
        $viewMode = false;
        $helpText = $this->getHelpText($params);
        $innerText = "";
        $classToFormItem = isset($params['class_form_item']) ? " " . $params['class_form_item'] : " ";
        $classToInputField = isset($params['class_input_field']) ? " " . $params['class_input_field'] : " ";
        $containerId = isset($params['id']) ? 'id="' . $params['id'] . '" ' : " ";
        $needToFormat = isset($params['need_to_format']) ? $params['need_to_format'] : true;

        $dto = $this->getDtoFromVariables();
        if (!$dto->hasReadAccess($params['name'])) {
            return "";
        }

        if ((($dto->getId() || $dto->getTempId()) && !$dto->hasWriteAccess($params['name'])) || isset($params['view_mode']) && $params['view_mode']) {
            $viewMode = true;
        }

        $fieldGetter = 'get' . $this->underlinesToCamelCase($params['name']);

        if (($dto->getId() || $dto->getTempId())) {
            $innerText = $this->formatValue($dto->$fieldGetter(), $needToFormat);
        }

        $templateParams = [
            'name' => $params['name'],
            'sage_sync' => isset($params['sync_with_sage']) && $params['sync_with_sage'],
            'h2_sync' => isset($params['sync_with_h2']) && $params['sync_with_h2'],
            'has_error_field' => isset($params['has_error_field']) && $params['has_error_field'],
            'displayName' => isset($params['display_name']) ? $params['display_name'] : $this->getDisplayName($params['name']),
            'innerText' => $innerText,
            'additional_html_after_input' => isset($params['additional_html_after_input']) && $params['additional_html_after_input'] ? $params['additional_html_after_input'] : ' ',
            'helpText' => $helpText,
            'class_to_form_item' => $classToFormItem,
            'class_to_input_field' => $classToInputField,
            'container_id' => $containerId,
            'only_positive' => (isset($params['only_positive']) && $params['only_positive']) ? ' only_positive=true ' : '',
            'element_id' => $dto->getTableName() . '_' . $params['name'] . '_input',
            'viewModeClass' => $viewMode ? " view-mode" : " ",
        ];

        $templateParams['innerHTML'] = $this->addInputFieldInnerHTML($viewMode, $templateParams);

        return $this->getFunctionTemplate($templateParams);
    }


    /**
     * this function should returns string which will be used in function plugin
     * @param array $params
     * @return string
     */
    protected function getFunctionTemplate(array $params): string
    {

        return '<div ' . $params['container_id'] . ' class="form-item' . $params['viewModeClass'] . $params['class_to_form_item'] . '">
                    <div class="input-field' . $params['class_to_input_field'] . '">' .
            $params['innerHTML']
            . '</div>
                    <div class="icons-box">
                        ' .  $params['helpText'] . '
                   </div>
                </div>';
    }

    private function addInputFieldInnerHTML($viewMode, $params)
    {
        $syncSage = $params['sage_sync'] ? '<i class="icon-sage-logo-svg syncable-field-icon"><div class="tooltip">Sage field</div></i>' : '';
        $syncSage .= $params['h2_sync'] ? '<i class="icon-master-icon master-field-icon"><div class="tooltip">Catalog master field</div></i>' : '';
        $additionalHTMLAfterInput = $params['additional_html_after_input'];

        if (!$viewMode) {
            $errorField = $params['has_error_field'] ? '<span class="f_field-error is_hidden field-error"></span>' : '';

            return '<label for="' . $params['element_id'] . '">' . $params['displayName'] . '</label>' . $syncSage . '
                    <input '  .$params['only_positive'] . 'id="' . $params['element_id'] . '"
                       name="' . $params['name'] . '" type="number"  
                       placeholder="' . $params['displayName'] . '" 
                       value="' . $params['innerText'] . '"' . '>'   .$additionalHTMLAfterInput. $errorField;
        } else {
            return '<label>' . $params['displayName'] . '</label>' . $syncSage . '
                    <span is_not_required="true"'  . 'class="view-text f_form-item-view-mode" id="' . $params['element_id'] . '"> ' . $params['innerText'] . '</span>';
        }
    }



    /**
     * @param mixd $value
     * @return mixed
     */
    protected function formatValue(mixed $value, bool $needToFormat): mixed
    {

        if (!$needToFormat) {
            return $value;
        }
        $float_value = (float)$value;

        if (strval($float_value) == $value) {
            $value = number_format($float_value, 2, '.', '');
        }

        return $value;
    }


}
