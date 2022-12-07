<?php
/**
 * NGS predefined templater class
 * handle smarty and json responses
 *
 * @author Levon Naghashyan <levon@naghashyan.com>
 * @site http://naghashyan.com
 * @package ngs.framework.templater
 * @version 4.0.0
 * @year 2010-2020
 *
 * This file is part of the NGS package.
 *
 * @copyright Naghashyan Solutions LLC
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ngs\AdminTools\templater\plugins;


use ngs\AdminTools\managers\LanguageManager;
use ngs\AdminTools\dal\dto\AbstractCmsDto;
use ngs\AdminTools\managers\TranslationManager;
use ngs\AdminTools\templater\NgsSmartyTemplater;
use ngs\AdminTools\util\StringUtil;

abstract class AbstractSmartyPlugin
{
    private $ngsSmartyTemplater = null;
    private $translations = null;
    private $languages = null;

    public final function __construct(NgsSmartyTemplater $ngsSmartyTemplater)
    {
        $this->ngsSmartyTemplater = $ngsSmartyTemplater;
    }

    /**
     * returns type of the plugin
     * @return string
     */
    abstract public function getType(): string;

    /**
     * returns plugin name
     *
     * @return string
     */
    abstract public function getPluginName(): string;


    /**
     * returns instance of ngs smarty templater
     *
     * @return null
     */
    protected function getSmartyTemplater(): NgsSmartyTemplater
    {
        return $this->ngsSmartyTemplater;
    }


    /**
     * add new variables to templater
     *
     * @param array $variables
     * @param string $pluginName
     */
    protected function addPluginVariables(array $variables, string $pluginName = '')
    {
        if (!$pluginName) {
            $pluginName = $this->getPluginName();
        }

        $this->getSmartyTemplater()->addPluginVariables($pluginName, $variables);
    }


    /**
     * update plugin variables in templater
     *
     * @param array $variables
     * @param string $pluginName
     *
     * @return bool
     */
    protected function updatePluginVariables(array $variables, string $pluginName = '')
    {
        if (!$pluginName) {
            $pluginName = $this->getPluginName();
        }
        return $this->getSmartyTemplater()->updatePluginVariables($pluginName, $variables);
    }


    /**
     * remove plugin variables from templater
     *
     * @param string $pluginName
     */
    protected function deletePluginVariables(string $pluginName = '')
    {
        if (!$pluginName) {
            $pluginName = $this->getPluginName();
        }
        $this->getSmartyTemplater()->deletePluginVariables($pluginName);
    }


    /**
     * returns plugin variables
     *
     * @param $pluginName
     * @return array
     */
    protected function getPluginVariables(string $pluginName = '')
    {
        if (!$pluginName) {
            $pluginName = $this->getPluginName();
        }
        return $this->getSmartyTemplater()->getPluginVariables($pluginName);
    }


    /**
     * returns all variables from templater
     *
     * @return array
     */
    protected function getVariables()
    {
        return $this->getSmartyTemplater()->getVariables();
    }


    /**
     * returns dto from variables
     *
     * @return AbstractCmsDto|null
     */
    protected function getDtoFromVariables()
    {
        $variables = $this->getVariables();
        return isset($variables['dto']) ? $variables['dto'] : null;
    }

    /**
     * all help texts of current dto
     * @return mixed|null
     */
    protected function getHelpTextsFromVariables()
    {
        $variables = $this->getVariables();
        return isset($variables['helpTexts']) ? $variables['helpTexts'] : null;
    }


    /**
     * all default values of current dto
     * @return mixed|null
     */
    protected function getAllDefaultValuesFromVariables()
    {
        $variables = $this->getVariables();
        return isset($variables['defaultValues']) ? $variables['defaultValues'] : null;
    }


    /**
     * returns underline separated text as camel case
     * TODO: need to be moved in stringUtil
     *
     * @param $string
     * @param bool $capitalizeFirstCharacter
     *
     * @param bool $divideWithSpaces
     * @return string|string[]
     */
    protected function underlinesToCamelCase($string, $capitalizeFirstCharacter = true, $divideWithSpaces = false)
    {
        return StringUtil::underlinesToCamelCase($string, $capitalizeFirstCharacter, $divideWithSpaces);
    }

    protected final function getDisplayName($name)
    {
        return ucwords(str_replace('_', ' ', $name));
    }


    /**
     * for any type of form input (input, textarea long_text etc) will return its translations as hidden input
     * @param string $ngsPluginType
     * @param $params
     * @return string
     * @throws \Exception
     */
    protected function getTranslateInputsForField($ngsPluginType, $params)
    {

        $this->translations = $this->getTranslations();
        $this->languages = LanguageManager::getInstance()->getLanguagesList();

        switch ($ngsPluginType) {
            case 'ngsText' :
                return $this->getTranslateInputsForTextField($params);
            case 'ngsTextarea' :
                return $this->getTranslateInputsForTextareaField($params);
            case 'ngsLongText' :
                return $this->getTranslateInputsForLongTextField($params);
            default :
                return '';
        }
    }

    /**
     * @return array|null
     */
    private function getTranslations()
    {
        $translationManager = TranslationManager::getInstance();
        $dto = $this->getDtoFromVariables();
        $translations = $translationManager->getItemsAllTranslations($dto);
        return $translations;
    }


    /**
     * translation inputs for ngsText Smarty Plugin
     * @param $params
     * @return string
     */
    private function getTranslateInputsForTextField($params): string
    {
        $inputName = $params['field_name'];
        $displayName = $params['display_name'];
        $viewMode = $params['is_view_mode'];

        $res = '';
        foreach ($this->languages as $languageId => $properties) {

            $valueText = "";
            $valueAttribute = "";

            if ($this->translations) {
                if (isset($this->translations[$languageId])) {
                    $valueText = json_decode($this->translations[$languageId]['value'], true)[$inputName] ?? '';
                }
            }

            if ($valueText !== "") {
                if (!$viewMode) {
                    $valueAttribute = 'value = "' . $valueText . '"';
                } else {
                    $valueAttribute = $valueText;
                }
            }

            $inputId = $inputName . '-' . strtolower($properties['name']) . '-translate';
            $placeholder = $displayName . ' (' . $properties['name'] . ')';
            $name = 'translations[' . $languageId . '][' . $inputName . ']' . '"';


            if (!$viewMode) {
                $input = '<input type="text" language-id="' . $languageId . '" id="' . $inputId . '" ' . $valueAttribute . 'placeholder="' . $placeholder . '" 
                                       class="f_translatable-field is_hidden" name="' . $name . '>';


            } else {
                $input = '<span language-id="' . $languageId . '"  class="view-text f_form-item-view-mode f_translatable-field is_hidden">' . $valueAttribute . '</span>';
            }

            $res .= $input;
        }

        return $res;
    }


    /**
     * translation inputs for ngsTextarea and ngsViewTextarea Smarty Plugin
     * @param $params
     * @return string
     */
    private function getTranslateInputsForTextareaField($params): string
    {
        $inputName = $params['field_name'];
        $displayName = $params['display_name'];
        $viewMode = $params['is_view_mode'];

        $res = '';
        foreach ($this->languages as $languageId => $properties) {
            $valueText = "";

            if ($this->translations) {
                if (isset($this->translations[$languageId])) {
                    $valueText = json_decode($this->translations[$languageId]['value'], true)[$inputName] ?? '';
                }
            }

            $inputId = $inputName . '-' . strtolower($properties['name']) . '-translate';
            $placeholder = $displayName . ' (' . $properties['name'] . ')';
            $name = 'translations[' . $languageId . '][' . $inputName . ']' . '"';

            if (!$viewMode) {
                $input = '<textarea language-id="' . $languageId . '" id="' . $inputId . '" value="' . $valueText . '" placeholder="' . $placeholder . '" 
                                       class="f_translatable-field is_hidden" name="' . $name . '>' . $valueText . '</textarea>';
            } else {
                $input = '<span language-id="' . $languageId . '"  class="view-textarea f_form-item-view-mode f_translatable-field is_hidden">' . $valueText . '</span>';
            }

            $res .= $input;
        }

        return $res;
    }


    /**
     * translation inputs for ngsLongText Smarty Plugin
     * @param $params
     * @return string
     */
    private function getTranslateInputsForLongTextField($params): string
    {
        $inputName = $params['field_name'];
        $displayName = $params['display_name'];
        $syncIcon = '';

        if($params['sync_icon_should_be']) {
            $syncIcon .= '<i class="icon-sage-logo-svg syncable-field-icon"><div class="tooltip">Sage field</div></i>';
        }
        if($params['sync_h2_icon_should_be']) {
            $syncIcon .= '<i class="icon-sage-logo-svg syncable-field-icon"><div class="tooltip">Catalog master field</div></i>';
        }

        $res = '';

        foreach ($this->languages as $languageId => $properties) {
            $valueText = "";

            if ($this->translations) {
                if (isset($this->translations[$languageId])) {
                    $valueText = json_decode($this->translations[$languageId]['value'], true)[$inputName] ?? '';
                }
            }

            $inputId = $inputName . '-' . strtolower($properties['name']) . '-translate';
            $placeholder = $displayName . ' (' . $properties['name'] . ')';
            $name = 'translations[' . $languageId . '][' . $inputName . ']' . '"';

            $input = '<div language-id="' . $languageId . '" class="f_translatable-field is_hidden">
                            <label>' . $displayName . '</label>'.
                                $syncIcon
                            .'<textarea id="' . $inputId . '" 
                                  name="' . $name . '
                                  class="f_tinymce"
                                  placeholder="' . $placeholder . '">'
                                . $valueText . '
                            </textarea>             
                      </div>';

            $res .= $input;
        }

        return $res;
    }

}
