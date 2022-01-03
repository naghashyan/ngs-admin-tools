<?php

/**
 * form block which is empty, to write content manually , but using smarty function plugins, like "ngsText" etc...
 */

namespace ngs\NgsAdminTools\templater\plugins\blocks;


class NgsFormWithoutContentBlockSmartyPlugin extends AbstractFormBlockSmartyPlugin
{

    /**
     * returns plugin name
     *
     * @return string
     */
    public function getPluginName(): string
    {
        return 'ngsFormWithoutContent';
    }

    /**
     * main function which will be called when plugin used in frontend
     * this function will be called twice, one when block tag opened, and second time when block tag closed
     * at first time $repeat parameter will be true, second time will be false
     *
     * @param $params
     * @param $content
     * @param $template
     * @param $repeat
     *
     * @return string|null
     */
    public function index($params, $content, $template, &$repeat): ?string
    {
        if (!$repeat) {
            $this->deletePluginVariables();

            return $this->getBlockTemplate(['content' => $content]);
        }

        $this->addPluginVariables($params);
        return null;
    }


    /**
     * returns content of block
     *
     * @param array $params
     *
     * @return string
     */
    protected function getBlockTemplate(array $params): string
    {

        return $params['content'];
    }


}
