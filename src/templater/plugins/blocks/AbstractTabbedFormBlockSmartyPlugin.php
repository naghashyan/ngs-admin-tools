<?php

/**
 * form block plugin for smarty
 */

namespace ngs\NgsAdminTools\templater\plugins\blocks;


abstract class AbstractTabbedFormBlockSmartyPlugin extends AbstractBlockSmartyPlugin
{

    /**
     * returns plugin name
     *
     * @return string
     */
    abstract public function getPluginName(): string;




    protected function getTabsContent($tabs): string
    {
        $isLast = "";
        $len = count($tabs);
        $tabsTags = '<div class="g-content-item col-left-fixed"><div class="g-content-item-wrapper"><div class="g-content-item-inner g_overflow-y-auto"><ul class="f_cms_vertical-tabs v-tabs medium1 light bgweb3 vertical-tabs">';
        foreach ($tabs as $index => $tab) {
            if($index == $len - 1) {
              $isLast = "is_last";
            }
            $tabName = str_replace(' ', '_', $tab);
            $tabsTags .= '<li class="tab col s3 ' . $isLast . '"><a href="#' . $tabName . '_tab" class="f_tabTitle"
                                                          id="' . $tabName . '_tab_title">' . $tab . '<i class="icon-svg33"></i></a></li>';
        }
        $tabsTags .= '</ul></div></div></div>';

        return $tabsTags;
    }
}
