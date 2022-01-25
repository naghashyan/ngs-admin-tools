<?php

/**
 * tabbed form block plugin for smarty
 */

namespace ngs\AdminTools\templater\plugins\blocks;


class NgsViewTabbedFormBlockSmartyPlugin extends AbstractTabbedFormBlockSmartyPlugin
{

    /**
     * returns plugin name
     *
     * @return string
     */
    public function getPluginName(): string
    {
        return 'ngsViewTabbedForm';
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
            $variables = $this->getPluginVariables();
            $tabs = $variables['tabs'];
            if (!$tabs) {
                return "";
            }

            $tabsTags = $this->getTabsContent($tabs);

            $templateParams = [
                'content' => $content,
                'tabsTags' => $tabsTags
            ];

            $this->deletePluginVariables();
            return $this->getBlockTemplate($templateParams);
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
        return '<form onsubmit="return false;" class="g_scrolable-fixed-inner-box row g-content edit-form f_addUpdateForm">
                    ' . $params['tabsTags'] . '
                        <div class="g-content-item vertical-tabs-content col-auto f_vertical-tabs-content"><div class="g-content-item-wrapper"><div class="f_g-content-item-inner g-content-item-inner g_overflow-y-auto">' . $params['content'] .
                       '</div></div></div>
                    
                </form>';
    }


}
