<?php

/**
 * text function plugin for smarty
 */

namespace ngs\NgsAdminTools\templater\plugins\functions;


class NgsTextFunctionSmartyPlugin extends AbstractFunctionSmartyPlugin
{

    /**
     * returns plugin name
     *
     * @return string
     */
    public function getPluginName(): string
    {
        return 'ngsText';
    }


    /**
     * main function which will be called when plugin used in frontend
     * @param $params
     * @param $template
     * @return string
     * @throws \ngs\exceptions\DebugException
     * @throws \Exception
     */
    public function index($params, $template): string
    {
        $viewMode =  false;
        $dto = $this->getDtoFromVariables();
        $names = explode(",", $params['name']);
        $name = $names[0];

        if((($dto->getId() || $dto->getTempId()) && !$dto->hasWriteAccess($name)) || isset($params['view_mode']) && $params['view_mode']){
            $viewMode = true;
        }

        if(($dto->getId() || $dto->getTempId()) && !$dto->hasReadAccess($name)){
            return "";
        }

        $isTranslatable = ($this->isFieldTranslatable($name));
        $translateInputs = "";
        if($isTranslatable){
            $paramsForTranslations = [
                'field_name' => $name,
                'display_name' => $params['display_name'],
                'is_view_mode' => $viewMode
            ];
            $translateInputs = $this->getTranslateInputsForField('ngsText', $paramsForTranslations);
        }


        $classToFormItem = isset($params['class_form_item']) ? " " .$params['class_form_item'] : " ";
        $classToInputField = isset($params['class_input_field']) ? " " .$params['class_input_field'] : " ";
        $containerId = isset($params['id']) ? 'id="' . $params['id'] .'" ' : " ";
        $helpText = $this->getHelpText($params);
        $rule = $this->getRule($params);

        $fieldGetter = 'get' . $this->underlinesToCamelCase($name);
        if($dto->getId() || $dto->getTempId()) {
            if($viewMode) {
                $innerTexts = [];
                foreach($names as $singleName) {
                    $fieldGetter = 'get' . $this->underlinesToCamelCase($singleName);
                    $innerTexts[] = $dto->$fieldGetter();
                }
                $innerText = implode(" - ", $innerTexts);
            }
            else {
                $innerText = $dto->$fieldGetter();
            }
        }else {
            $innerText = $this->getDefaultValueForTextType($name);
        }

        $attributesForInputTag = '';
        if(isset($params['attributes_for_input_tag']) && !empty($params['attributes_for_input_tag'])) {
            foreach ($params['attributes_for_input_tag'] as $attributeName => $attributeValue) {
                $attributesForInputTag .= $attributeName . '="' . $attributeValue . '" ';
            }
        }

        $templateParams = [
            'name' => $name,
            'sage_sync' => isset($params['sync_with_sage']) && $params['sync_with_sage'],
            'has_error_field' => isset($params['has_error_field']) && $params['has_error_field'],
            'displayName' => isset($params['display_name']) ? $params['display_name'] : $this->getDisplayName($name),
            'innerText' => $innerText,
            'helpText' => $helpText,
            'rule' => $rule,
            'class_to_form_item' => $classToFormItem,
            'class_to_input_field' => $classToInputField,
            'container_id' => $containerId,
            'viewModeClass' => $viewMode? " view-mode" : " ",
            'attributes_for_input_tag' => $attributesForInputTag,
            'isTranslatable' => $isTranslatable,
            'translations' => $translateInputs,
            'element_id' => $dto->getTableName() . '_' . $name . '_input'
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

        return '<div '.$params['container_id'].' class="form-item'.$params['viewModeClass'] .$params['class_to_form_item'] .'">
                    <div class="input-field'. $params['class_to_input_field'] .'">' .
            $params['innerHTML']
            .'</div>
                    <div class="icons-box">
                        ' .$params['rule'] . $params['helpText'] . '
                   </div>
                </div>';
    }




    private function addInputFieldInnerHTML($viewMode, $params){
        $syncSage = $params['sage_sync'] ? '<i class="icon-sage-logo-svg syncable-field-icon"><div class="tooltip">Sage field</div></i>' : '';

        $originalLanguage = $params['isTranslatable']? ' language-id="original" ' : ' ';
        if(!$viewMode){
            $errorField = $params['has_error_field'] ?  '<span class="f_field-error is_hidden field-error"></span>' : '';

            return '<label for="'. $params['element_id'] .'">'. $params['displayName'] . '</label>' . $syncSage . '
                    <input '. $params['attributes_for_input_tag'] . $originalLanguage . 'id="' . $params['element_id'] . '"
                       name="' . $params['name'] . '" type="text"
                       placeholder="' . $params['displayName'] . '"
                       value="' . $params['innerText'] . '"' . '>' . $errorField . $params['translations'];
        }else{
            return '<label>' .$params['displayName']. '</label>' . $syncSage . '
                    <span is_not_required="true"' . $originalLanguage . 'class="view-text f_form-item-view-mode" id="' . $params['element_id'] . '"> ' .$params['innerText']. '</span>' . $params['translations'];
        }
    }





}
