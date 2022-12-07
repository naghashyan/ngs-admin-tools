<?php

/**
 * text function plugin for smarty
 */

namespace ngs\AdminTools\templater\plugins\functions;

class NgsSelectFunctionSmartyPlugin extends AbstractFunctionSmartyPlugin
{

    /**
     * returns plugin name
     *
     * @return string
     */
    public function getPluginName(): string
    {
        return 'ngsSelect';
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
        $viewMode = false;
        $dto = $this->getDtoFromVariables();
        if((!$dto->hasWriteAccess($params['name'])) || isset($params['view_mode']) && $params['view_mode']){
            $viewMode = true;
        }
        if(!$dto->hasReadAccess($params['name'])){
            return "";
        }

        $customText = isset($params['help_text']) ? $params['help_text'] : '';
        $helpText = $this->getHelpText($params, $customText);
        $classToFormItem = isset($params['class_form_item']) ? " " .$params['class_form_item'] : " ";
        $classToInputField = isset($params['class_input_field']) ? " " .$params['class_input_field'] : " ";
        $containerId = isset($params['id']) ? 'id="' . $params['id'] .'" ' : " ";
        $multiple = (isset($params['multiple']) && $params['multiple'] != false) ? " multiple" : " ";

        if(isset($params['removable'])) {
            $removable = $params['removable'] ? true : false;
        }else {
            $removable = !trim($multiple) ? false : true;
        }
        $name = ($multiple == " ") ? $params['name'] : $params['name'] . '[]';

        $innerText = "";
        if($viewMode){
            if(isset($params['values'])){
                $innerText = $this->getInnerTextFromRelatedTable($params);
            }else{
                $innerText = $this->getInnerTextFromDto($params);
            }
        }
        $options = $this->getOptionTags($params);


        $templateParams = [
            'name' => $name,
            'sage_sync' => isset($params['sync_with_sage']) && $params['sync_with_sage'],
            'h2_sync' => isset($params['sync_with_h2']) && $params['sync_with_h2'],
            'displayName' => isset($params['display_name']) ? $params['display_name'] : $this->getDisplayName($params['name']),
            'helpText' => $helpText,
            'options' => $options,
            'searchable' => (count($params['possible_values']) > 5) ? true : false,
            'not_choice' => isset($params['not_choice']) && $params['not_choice'] ? true : false,
            'removable' => $removable,
            'multiple' => $multiple,
            'class_to_form_item' => $classToFormItem,
            'class_to_input_field' => $classToInputField,
            'container_id' => $containerId,
            'element_id' => $dto->getTableName() . '_' . $params['name'] . '_input',
            'without_placeholder' => isset($params['without_placeholder']) && $params['without_placeholder'],
            'innerText' => $innerText,
            'viewModeClass' => $viewMode? " view-mode" : " "
        ];

        $templateParams['innerHTML'] = $this->addInputFieldInnerHTML($viewMode, $templateParams);

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

        return '<div '.$params['container_id'].' class="form-item' .$params['class_to_form_item'] . $params['viewModeClass'] . '">
                    <div class="input-field' .$params['class_to_input_field']. 'f_select_' . $params['name'] . '">'.
            $params['innerHTML']
            .'</div>
                </div>';
    }

    /**
     * @param array $params
     * @return string
     */
    private function getOptionTags(array $params):string {
        $possibleValues = $this->getPossibleValues($params);
        $options = "";
        $dto = $this->getDtoFromVariables();

        if(!isset($params['values'])){
            $fieldGetter = "get" . $this->underlinesToCamelCase($params['name']);
            foreach ($possibleValues as $option){
                $innerValue = $option['value'];
                $id = $option['id'];
                $selected = "";
                $defaultValue = isset($option['is_default']);
                if($dto->getId() > 0 || $dto->getTempId() > 0) {
                    $selected = $dto && ($id == $dto->$fieldGetter()) ? "selected" : "";
                }else {
                    if($defaultValue) {
                        $selected = "selected";
                    }
                }

                $options .= "<option $selected  value=$id >" .$innerValue. "</option>";
            }

        }else{
            foreach ($possibleValues as $option){
                $innerValue = $option['value'];
                $id = $option['id'];
                $default = isset($option['is_default']);
                $selected = "";
                if($dto->getId() > 0) {
                    if(in_array($id, $params['values']) && $this->getDtoFromVariables()){
                        $selected = "selected";
                    }
                }else {
                    if($default) {
                        $selected = "selected";
                    }
                }

                $options .= "<option $selected  value=$id >" .$innerValue. "</option>";
            }
        }
        return $options;

    }

    /**
     * @param array $params
     * @return array
     */
    private function getPossibleValues(array $params):array {
        $possibleValues = [];
        foreach($params['possible_values'] as $value){
            $possibleValues[] = $value;
        }
        return $possibleValues;
    }


    /**
     * @param array $params
     * @return string
     */
    private function getInnerTextFromDto(array $params): string {

        $dto = $this->getDtoFromVariables();
        if(!$dto){
            return "";
        }
        $fieldGetter = "get" . $this->underlinesToCamelCase($params['name']);
        $possibleValues = $this->getPossibleValues($params);
        foreach ($possibleValues as $option){
            if($option['id'] == $dto->$fieldGetter()){
                return $option['value'];
            }
        }
        return "";
    }

    private function getInnerTextFromRelatedTable($params) {
        $possibleValues = $this->getPossibleValues($params);
        $res = [];
        foreach ($possibleValues as $option){
            if(in_array($option['id'], $params['values']) && $this->getDtoFromVariables()){
                $res[] = $option['value'];
            }

        }
        return implode(', ', $res);

    }

    private function addInputFieldInnerHTML($viewMode, $params){
        $syncSage = $params['sage_sync'] ? '<i class="icon-sage-logo-svg syncable-field-icon"><div class="tooltip">Sage field</div></i>' : '';
        $syncSage .= $params['h2_sync'] ? '<i class="icon-master-icon master-field-icon"><div class="tooltip">Catalog master field</div></i>' : '';
        $placeholder = !$params['without_placeholder'] ? ' <option value="">Please select</option> ' : ' ';

        if(!$viewMode){

            $searchable = $params['searchable'] ? 'true' : 'false';
            $removable = $params['removable'] ? 'true' : 'false';
            $choiceClass = !$params['not_choice'] ? 'ngs-choice' : '';

            return '<div class="icons-box">' . $params['helpText']. '</div>
                        <label for="' . $params['element_id'] . '">'. $params['displayName'] . '</label>' . $syncSage . '   
                            <select ' . $params['multiple'].' searchable="Search" class="' . $choiceClass . '" data-ngs-remove="' . $removable . '" data-ngs-searchable="' . $searchable . '" id="' . $params['element_id'] . '"
                               name="' . $params['name'] . '">' .
                                 $placeholder . $params['options'] .'
                            </select>';
        }else{
            return '<div class="icons-box">' . $params['helpText'] . '</div><label>' .$params['displayName']. '</label>' . $syncSage . '
                    <span class="view-text f_form-item-view-mode">' .$params['innerText']. '</span>';
        }
    }


}