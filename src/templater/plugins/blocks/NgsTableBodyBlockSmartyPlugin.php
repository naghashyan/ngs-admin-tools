<?php

/**
 * table body block plugin for smarty
 */

namespace ngs\NgsAdminTools\templater\plugins\blocks;


class NgsTableBodyBlockSmartyPlugin extends AbstractBlockSmartyPlugin
{

    /**
     * returns plugin name
     *
     * @return string
     */
    public function getPluginName(): string
    {
        return 'ngsTableBody';
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
        return '<div class="table-box-inner">'. $params['content'] .'</div>';
    }
}
