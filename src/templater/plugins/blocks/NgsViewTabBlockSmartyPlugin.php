<?php

/**
 * tab block plugin for smarty
 */

namespace ngs\AdminTools\templater\plugins\blocks;


class NgsViewTabBlockSmartyPlugin extends AbstractTabBlockSmartyPlugin
{
    /**
     * returns plugin name
     *
     * @return string
     */
    public function getPluginName(): string
    {
        return 'ngsViewTab';
    }

    public function getParentPluginName(): string
    {
        return 'ngsViewTabbedForm';
    }


}
