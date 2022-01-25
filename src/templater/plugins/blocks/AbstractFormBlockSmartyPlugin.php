<?php

/**
 * form block plugin for smarty
 */

namespace ngs\AdminTools\templater\plugins\blocks;


abstract class AbstractFormBlockSmartyPlugin extends AbstractBlockSmartyPlugin
{

    /**
     * returns plugin name
     *
     * @return string
     */
    abstract public function getPluginName(): string;


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



    /**
     * add group if no group exist
     *
     * @param $params
     * @return string
     */
    protected function addGroupIfNotExists($params) {
        return '<li id="item-main-group"
                        class="bgweb3 form-content-item form-content-count-' . $params['fieldsCount'] . '"><div class="form-items-container' .$params['flexClass']. '">' .
            $params['content'] .
            '</div></li>';
    }


    /**
     * if form block contains group in it
     *
     * @param $content
     *
     * @return bool
     */
    protected function formHasInnerGroup($content): bool
    {
        return strpos($content, 'form-content-item');
    }


    /**
     * returns count of group items
     *
     * @param $content
     * @return int
     */
    protected function getCountOfGroupItems($content): int
    {
        return substr_count($content, "form-item");
    }
}
