<?php

/**
 * new line function plugin for smarty
 */

namespace ngs\AdminTools\templater\plugins\functions;

class NgsNewLineFunctionSmartyPlugin extends AbstractFunctionSmartyPlugin
{
    /**
     * returns plugin name
     *
     * @return string
     */
    public function getPluginName(): string
    {
        return 'ngsNewLine';
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
        return $this->getFunctionTemplate(['class' => isset($params['class_form_item']) ? " " .$params['class_form_item'] : ""]);
    }


    /**
     * this function should returns string which will be used in function plugin
     *
     * @param array $params
     * @return string
     */
    protected function getFunctionTemplate(array $params): string
    {
        return '<div class="new-line' . $params['class'] . '"></div>';
    }
}
