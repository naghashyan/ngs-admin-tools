<?php

/**
 * tab block plugin for smarty
 */

namespace ngs\NgsAdminTools\templater\plugins\blocks;


class NgsTabBlockSmartyPlugin extends AbstractTabBlockSmartyPlugin
{
    /**
     * returns plugin name
     *
     * @return string
     */
    public function getPluginName(): string
    {
        return 'ngsTab';
    }


    public function getParentPluginName(): string
    {
        return 'ngsTabbedForm';
    }


}
