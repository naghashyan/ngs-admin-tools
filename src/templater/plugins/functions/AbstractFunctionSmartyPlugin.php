<?php

/**
 * abstract class for function smarty plugins,
 * all created function type plugins should extend this class
 */

namespace ngs\AdminTools\templater\plugins\functions;


use ngs\AdminTools\dal\dto\AbstractCmsDto;
use ngs\AdminTools\templater\plugins\AbstractSmartyPlugin;
use ngs\AdminTools\util\StringUtil;

abstract class AbstractFunctionSmartyPlugin extends AbstractSmartyPlugin
{

    /**
     * returns type of the plugin
     * @return string
     */
    public final function getType(): string
    {
        return "function";
    }


    /**
     * main function which will be called when plugin used in frontend
     *
     * @param $params
     * @param $template
     *
     * @return string|null
     */
    abstract public function index($params, $template): ?string;

    public final function handler($params, $template) :?string{
        return $this->index($params, $template);
    }

    /**
     * this function should returns string which will be used in function plugin
     *
     * @param array $params
     * @return string
     */
    abstract protected function getFunctionTemplate(array $params): string;

    /**
     * @param $params
     * @param string $customTextForHelpTextIconsInnerValue
     * @return string
     */
    protected function getHelpText($params, $customTextForHelpTextIconsInnerValue = ''):string {

        $innerValue = '';
        if($customTextForHelpTextIconsInnerValue) {
            $innerValue = $customTextForHelpTextIconsInnerValue;
        }else {
            $allHelpTexts = $this->getHelpTextsFromVariables();
            if($allHelpTexts) {
                $names = explode(",", $params['name']);
                $name = $names[0];
                $innerValue = isset($allHelpTexts[$name]) ? $allHelpTexts[$name] : null;
            }
        }

        if($innerValue != null){
            return '<span class="icon-tooltip f_help-text">
                        <i class="icon-svg31"></i>
                        <span class="tooltip">'
                . $innerValue .
                '</span>
                </span>';
        }
        return '';
    }

    /**
     * get default value for current input (only for text type) if exists and empty string if no
     * @param $inputName
     * @return string
     */
    protected function getDefaultValueForTextType($inputName): string {
        $defaultValue = '';

        $allDefaultValuesOfDto = $this->getAllDefaultValuesFromVariables();
        if($allDefaultValuesOfDto) {
            $defaultValue = isset($allDefaultValuesOfDto[$inputName]) ? $allDefaultValuesOfDto[$inputName] : '';
        }
        return $defaultValue;
    }


    /**
     * determines whether checkbox should be checked by default or no
     * @param $inputName
     * @return bool
     */
    protected function isCheckboxDefaultChecked($inputName): bool {
        $allDefaultValuesOfDto = $this->getAllDefaultValuesFromVariables();
        if($allDefaultValuesOfDto) {
            return isset($allDefaultValuesOfDto[$inputName]) && $allDefaultValuesOfDto[$inputName] ? true : false;
        }
        return false;
    }


    /**
     * returns field rule name
     *
     * @param $params
     * @return string
     */
    protected function getRule($params):string {

        /** @var AbstractCmsDto $dto */
        $dto = $this->getDtoFromVariables();
        $fieldName = $params['name'];
        $rule = "";
        if($dto != null){
            $rule = $dto->getFieldRule($fieldName);
        }
        if(!$rule) {
            return "";
        }
        $ruleDisplayName = StringUtil::underlinesToCamelCase($rule, true, true);

        return '<span class="icon-tooltip rule-btn f_rule-btn" data-rule-name="' . $rule . '">
                        <i class="icon-svg30"></i>
                        <span class="tooltip">'
            . $ruleDisplayName .
            '</span>
        </span>';
    }

    /**
     * finds the fieldName in translatable field names array
     * @param $fieldName
     * @return bool
     */
    protected function isFieldTranslatable($fieldName): bool{
        $dto = $this->getDtoFromVariables();
        $translatableFields = $dto->getTranslatableFields();
        if(!$translatableFields){
            return false;
        }
        return in_array($fieldName, $translatableFields);
    }
}
