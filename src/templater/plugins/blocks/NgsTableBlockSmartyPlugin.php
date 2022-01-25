<?php

/**
 * table block plugin for smarty
 */

namespace ngs\AdminTools\templater\plugins\blocks;


class NgsTableBlockSmartyPlugin extends AbstractBlockSmartyPlugin
{

    /**
     * returns plugin name
     *
     * @return string
     */
    public function getPluginName(): string
    {
        return 'ngsTable';
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
            $templateParams = [
                'content' => $content
            ];
            return $this->getBlockTemplate($templateParams);
        }

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
        return '<div class="g_scrolable-section action-tables table-box card-box bgweb3">'. $params['content'] .'</div>';
    }
}
