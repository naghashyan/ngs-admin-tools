<?php

/**
 * add button function plugin for smarty
 */

namespace ngs\AdminTools\templater\plugins\functions;


class NgsAddButtonFunctionSmartyPlugin extends AbstractFunctionSmartyPlugin
{

    /**
     * returns plugin name
     *
     * @return string
     */
    public function getPluginName(): string
    {
        return 'ngsAddButton';
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
        $templateParams = [
            'name' => $params['name'],
            'specific_class' => isset($params['specific_class'])? ' ' . $params['specific_class'] : ''
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

        return '<a class="button basic primary with-icon f_addItemBtn addItemBtn' . $params['specific_class'] . '" href="javascript:void(0);"
                   title="ADD NEW ' . strtoupper($params['name']) . '">
                    <i class="icon-svg179 left-icon"></i>
                    <span>' .  strtoupper($params['name']) . '</span>
                </a>';
    }

}
