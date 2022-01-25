<?php

/**
 * hidden function plugin for smarty
 */

namespace ngs\AdminTools\templater\plugins\functions;

class NgsHiddenFunctionSmartyPlugin extends AbstractFunctionSmartyPlugin
{

    /**
     * returns plugin name
     *
     * @return string
     */
    public function getPluginName(): string
    {
        return 'ngsHidden';
    }

    /**
     * main function which will be called when plugin used in frontend
     *
     * @param $params
     * @param $template
     *
     * @return string|null
     */
    public function index($params, $template): string
    {
        $dto = $this->getDtoFromVariables();

        $value = '';
        $fieldGetter = 'get' . $this->underlinesToCamelCase($params['name']);
        if($dto && $dto->$fieldGetter()) {
            $value = $dto->$fieldGetter();
        }

        if(!$value && isset($params['default_value'])) {
            $value = $params['default_value'];
        }

        $templateParams = [
            'name' => $params['name'],
            'value' => $value
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
        return '<input type="hidden" name="' . $params['name'] . '" value="' . $params['value'] . '">';
    }
}
