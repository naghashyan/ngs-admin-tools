<?php

/**
 * text function plugin for smarty
 */

namespace ngs\AdminTools\templater\plugins\functions;

class NgsViewTextareaFunctionSmartyPlugin extends AbstractFunctionSmartyPlugin
{

    /**
     * returns plugin name
     *
     * @return string
     */
    public function getPluginName(): string
    {
        return 'ngsViewTextarea';
    }

//todo: need to join this class to ngsTextarea, and there divide to add/edit mode or view mode


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
        $helpText = $this->getHelpText($params);
        $fieldGetter = 'get' . $this->underlinesToCamelCase($params['name']);
        $dto = $this->getDtoFromVariables();
        $classToFormItem = isset($params['class_form_item']) ? " " .$params['class_form_item'] : " ";
        $classToInputField = isset($params['class_input_field']) ? " " .$params['class_input_field'] : " ";
        if(!$dto->hasReadAccess($params['name'])){
            return "";
        }

        $isTranslatable = ($this->isFieldTranslatable($params['name']));
        $translateInputs = "";
        if($isTranslatable){
            $paramsForTranslations = [
                'field_name' => $params['name'],
                'display_name' => $params['display_name'],
                'is_view_mode' => true
            ];
            $translateInputs = $this->getTranslateInputsForField('ngsTextarea', $paramsForTranslations);
        }

        $templateParams = [
            'sage_sync' => isset($params['sync_with_sage']) && $params['sync_with_sage'],
            'displayName' => isset($params['display_name']) ? $params['display_name'] : $this->getDisplayName($params['name']),
            'class_to_form_item' => $classToFormItem,
            'class_to_input_field' => $classToInputField,
            'innerText' =>  ($dto) ? $this->getDtoFromVariables()->$fieldGetter() : "",
            'helpText' => $helpText,
            'isTranslatable' => $isTranslatable,
            'translations' => $translateInputs
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
        $originalLanguage = $params['isTranslatable']? ' language-id="original" ' : ' ';

        return '<div class="form-item view-mode' .$params['class_to_form_item'] .'">
                    
                    <div class="input-field' . $params['class_to_input_field'] . '">
                        <label>' .$params['displayName']. '</label>' . $syncSage . '
                        <span' . $originalLanguage . ' class="view-textarea f_form-item-view-mode">' .$params['innerText']. '</span>'.
                        $params['translations']
                    .'</div>
                    <div class="icons-box">
                    ' .$params['helpText']. '
                    </div>
                </div>';
    }
}
