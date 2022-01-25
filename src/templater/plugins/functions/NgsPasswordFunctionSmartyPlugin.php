<?php

/**
 * text function plugin for smarty
 */

namespace ngs\AdminTools\templater\plugins\functions;

class NgsPasswordFunctionSmartyPlugin extends AbstractFunctionSmartyPlugin
{

    /**
     * returns plugin name
     *
     * @return string
     */
    public function getPluginName(): string
    {
        return 'ngsPassword';
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
            'displayName' => isset($params['display_name']) ? $params['display_name'] : $this->getDisplayName($params['name']),
            'innerText' => $innerText,
            'helpText' => $helpText,
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
        return '<div '.$params['container_id'].' class="form-item' .$params['class_to_form_item'] .'">
                    <div class="input-field'. $params['class_to_input_field'] .'">
                        
                        <label for="' . $params['element_id'] . '">'. $params['displayName'] . '</label>
                            <input id="' . $params['element_id'] . '"
                               name="' . $params['name'] . '" type="password"
                               placeholder="' . $params['displayName'] . '"
                               value="' . $params['innerText'] . '"' . '>
                    </div>
                    <div class="icons-box">
                        ' .$params['helpText']. '
                    </div>
                </div>';
    }
}
