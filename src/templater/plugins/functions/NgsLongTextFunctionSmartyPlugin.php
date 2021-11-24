<?php

/**
 * long text function plugin for smarty
 */

namespace ngs\NgsAdminTools\templater\plugins\functions;

class NgsLongTextFunctionSmartyPlugin extends AbstractFunctionSmartyPlugin
{

    /**
     * returns plugin name
     *
     * @return string
     */
    public function getPluginName(): string
    {
        return 'ngsLongText';
    }


    /**
     * main function which will be called when plugin used in frontend
     *
     * @param $params
     * @param $template
     * @return string
     * @throws \ngs\exceptions\DebugException
     * @throws \Exception
     */
    public function index($params, $template): string
    {
        $dto = $this->getDtoFromVariables();
        $innerText = '';
        $classToFormItem = isset($params['class_form_item']) ? " " .$params['class_form_item'] : " ";
        $classToInputField = isset($params['class_input_field']) ? " " .$params['class_input_field'] : " ";
        $containerId = isset($params['id']) ? 'id="' . $params['id'] .'" ' : " ";
        $helpText = $this->getHelpText($params);
        $fieldGetter = 'get' . $this->underlinesToCamelCase($params['name']);

        if(!$dto->hasReadAccess($params['name'])){
            return "";
        }

        if(($dto->getId() || $dto->getTempId())) {
            $innerText = $dto->$fieldGetter();
        }

        $isTranslatable = ($this->isFieldTranslatable($params['name']));
        $translateInputs = "";
        if($isTranslatable){
            $paramsForTranslations = [
                'field_name' => $params['name'],
                'display_name' => $params['display_name'],
                'sync_icon_should_be' => isset($params['sync_with_sage']) && $params['sync_with_sage']
            ];
            $translateInputs = $this->getTranslateInputsForField('ngsLongText', $paramsForTranslations);
        }

        $templateParams = [
            'name' => $params['name'],
            'sage_sync' => isset($params['sync_with_sage']) && $params['sync_with_sage'],
            'displayName' => isset($params['display_name']) ? $params['display_name'] : $this->getDisplayName($params['name']),
            'innerText' => $innerText,
            'helpText' => $helpText,
            'class_to_form_item' => $classToFormItem,
            'class_to_input_field' => $classToInputField,
            'container_id' => $containerId,
            'element_id' => $dto->getTableName() . '_' . $params['name'] . '_input',
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

        return '<div '.$params['container_id'].' class="form-item richtext' .$params['class_to_form_item'] .'">
                    <div class="form-group">
                        <div class="input-field date-box col s6'. $params['class_to_input_field'] .'">
                        <div' . $originalLanguage . '>
                                <label>' . $params['displayName'] . '</label>' . $syncSage .'
                                    <textarea id="' . $params['element_id'] . '"
                                      name="' . $params['name'] . '"
                                      class="f_tinymce"
                                      placeholder="' . $params['displayName'] . '" '
                                     . '>' . $params['innerText'] . '
                                    </textarea>
                            </div>'
                            . $params['translations'] .
                        '</div>
                        
                        <div class="icons-box">
                            ' .$params['helpText']. '
                        </div>
                    </div>
                </div>';
    }
}
