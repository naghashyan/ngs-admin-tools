<?php
/**
 * NGS predefined templater class
 * handle smarty and json responses
 *
 * @author Levon Naghashyan <levon@naghashyan.com>
 * @site http://naghashyan.com
 * @package ngs.framework.templater
 * @version 4.0.0
 * @year 2010-2020
 *
 * This file is part of the NGS package.
 *
 * @copyright Naghashyan Solutions LLC
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ngs\AdminTools\templater;

use ngs\AdminTools\templater\plugins\AbstractSmartyPlugin;
use ngs\AdminTools\templater\plugins\blocks\NgsFormBlockSmartyPlugin;
use ngs\AdminTools\templater\plugins\blocks\NgsFormWithoutContentBlockSmartyPlugin;
use ngs\AdminTools\templater\plugins\blocks\NgsGroupBlockSmartyPlugin;
use ngs\AdminTools\templater\plugins\blocks\NgsPopupFormBlockSmartyPlugin;
use ngs\AdminTools\templater\plugins\blocks\NgsFlexibleHeightFormBlockSmartyPlugin;
use ngs\AdminTools\templater\plugins\blocks\NgsTabbedFormBlockSmartyPlugin;
use ngs\AdminTools\templater\plugins\blocks\NgsTabBlockSmartyPlugin;
use ngs\AdminTools\templater\plugins\blocks\NgsTableBlockSmartyPlugin;
use ngs\AdminTools\templater\plugins\blocks\NgsTableHeaderBlockSmartyPlugin;
use ngs\AdminTools\templater\plugins\blocks\NgsTableBodyBlockSmartyPlugin;

use ngs\AdminTools\templater\plugins\blocks\NgsViewFormBlockSmartyPlugin;
use ngs\AdminTools\templater\plugins\blocks\NgsViewPopupFormBlockSmartyPlugin;
use ngs\AdminTools\templater\plugins\blocks\NgsViewTabbedFormBlockSmartyPlugin;
use ngs\AdminTools\templater\plugins\blocks\NgsViewTabBlockSmartyPlugin;

use ngs\AdminTools\templater\plugins\functions\NgsCheckboxFunctionSmartyPlugin;
use ngs\AdminTools\templater\plugins\functions\NgsAddButtonFunctionSmartyPlugin;
use ngs\AdminTools\templater\plugins\functions\NgsHiddenFunctionSmartyPlugin;
use ngs\AdminTools\templater\plugins\functions\NgsListFunctionSmartyPlugin;
use ngs\AdminTools\templater\plugins\functions\NgsLongTextFunctionSmartyPlugin;
use ngs\AdminTools\templater\plugins\functions\NgsNewLineFunctionSmartyPlugin;
use ngs\AdminTools\templater\plugins\functions\NgsTableBulkActionsFunctionSmartyPlugin;
use ngs\AdminTools\templater\plugins\functions\NgsTableFilterFunctionSmartyPlugin;
use ngs\AdminTools\templater\plugins\functions\NgsTextFunctionSmartyPlugin;
use ngs\AdminTools\templater\plugins\functions\NgsNumberFunctionSmartyPlugin;
use ngs\AdminTools\templater\plugins\functions\NgsSelectFunctionSmartyPlugin;
use ngs\AdminTools\templater\plugins\functions\NgsEmailFunctionSmartyPlugin;
use ngs\AdminTools\templater\plugins\functions\NgsDateFunctionSmartyPlugin;
use ngs\AdminTools\templater\plugins\functions\NgsTimeFunctionSmartyPlugin;
use ngs\AdminTools\templater\plugins\functions\NgsTextareaFunctionSmartyPlugin;
use ngs\AdminTools\templater\plugins\functions\NgsPasswordFunctionSmartyPlugin;
use ngs\AdminTools\templater\plugins\functions\NgsDropzoneFunctionSmartyPlugin;
use ngs\AdminTools\templater\plugins\functions\NgsPaginationFunctionSmartyPlugin;
use ngs\AdminTools\templater\plugins\functions\NgsViewDateFunctionSmartyPlugin;
use ngs\AdminTools\templater\plugins\functions\NgsViewTextareaFunctionSmartyPlugin;

class NgsSmartyTemplater extends \ngs\templater\NgsSmartyTemplater
{

    private $blocksVariables = [];

    /**
     * NgsSmartyTemplater constructor.
     * @param bool $isHtml
     * @throws \SmartyException
     * @throws \ngs\exceptions\DebugException
     */
    public function __construct(bool $isHtml = true)
    {
        parent::__construct($isHtml);
        $this->registerCmsPlugins();
    }


    /**
     * TODO: MJ: API docs
     *
     * @param string $blockName
     * @param array $variables
     */
    public function addPluginVariables(string $blockName, array $variables)
    {
        $this->blocksVariables[] = ['blockName' => $blockName, 'variables' => $variables];
    }


    /**
     * TODO: MJ: API docs
     * @param string $blockName
     * @param array $variables
     *
     * @return bool
     */
    public function updatePluginVariables(string $blockName, array $variables) {
        foreach($this->blocksVariables as $key => $blocksVariables) {
            if($blocksVariables['blockName'] === $blockName) {
                $blocksVariables['variables'] = $variables;
                $this->blocksVariables[$key] = $blocksVariables;

                return true;
            }
        }

        return false;
    }


    /**
     * TODO: MJ: API docs
     * @param string $blockName
     */
    public function deletePluginVariables(string $blockName)
    {
        $newVariables = [];
        foreach($this->blocksVariables as $blocksVariables) {
            if($blocksVariables['blockName'] === $blockName) {
                return;
            }

            $newVariables[] = $blocksVariables;
        }

        $this->blocksVariables = $newVariables;
    }


    /**
     * TODO: MJ: API docs
     * @param $blockName
     * @return array
     */
    public function getPluginVariables(string $blockName)
    {
        foreach ($this->blocksVariables as $blocksVariables) {
            if($blocksVariables['blockName'] === $blockName) {
                return $blocksVariables['variables'];
            }
        }

        return null;
    }

    /**
     * TODO: MJ: API docs
     * @return array
     */
    public function getVariables()
    {
        $result = [];

        foreach ($this->blocksVariables as $blocksVariables) {
            $result = array_merge($result, $blocksVariables['variables']);
        }

        return $result;
    }


    /**
     * TODO: MJ: API docs
     * @param $className
     */
    public function registerNgsPlugin($className) {
        try {
            /** @var AbstractSmartyPlugin $pluginItem */
            $pluginItem = new $className($this);

            if(!$pluginItem instanceof AbstractSmartyPlugin) {
                //TODO: MJ: use die function
                var_dump('wrong plugin class ' . $className);exit;
            }

            $this->registerPlugin($pluginItem->getType(), $pluginItem->getPluginName(), [$pluginItem, 'handler']);
        }
        catch(\Exception $exc) {
            //TODO: MJ: use die
            var_dump($exc->getMessage());exit;
        }
    }


    /**
     * add all CMS necessary plugins
     */
    private function registerCmsPlugins()
    {
        $this->registerNgsPlugin(NgsFormBlockSmartyPlugin::class);
        $this->registerNgsPlugin(NgsFormWithoutContentBlockSmartyPlugin::class);
        $this->registerNgsPlugin(NgsPopupFormBlockSmartyPlugin::class);
        $this->registerNgsPlugin(NgsFlexibleHeightFormBlockSmartyPlugin::class);
        $this->registerNgsPlugin(NgsTabbedFormBlockSmartyPlugin::class);
        $this->registerNgsPlugin(NgsGroupBlockSmartyPlugin::class);
        $this->registerNgsPlugin(NgsTabBlockSmartyPlugin::class);
        $this->registerNgsPlugin(NgsTableBlockSmartyPlugin::class);
        $this->registerNgsPlugin(NgsTableHeaderBlockSmartyPlugin::class);
        $this->registerNgsPlugin(NgsTableBodyBlockSmartyPlugin::class);
        $this->registerNgsPlugin(NgsViewFormBlockSmartyPlugin::class);
        $this->registerNgsPlugin(NgsViewPopupFormBlockSmartyPlugin::class);
        $this->registerNgsPlugin(NgsViewTabbedFormBlockSmartyPlugin::class);
        $this->registerNgsPlugin(NgsViewTabBlockSmartyPlugin::class);


        $this->registerNgsPlugin(NgsAddButtonFunctionSmartyPlugin::class);
        $this->registerNgsPlugin(NgsCheckboxFunctionSmartyPlugin::class);
        $this->registerNgsPlugin(NgsTextFunctionSmartyPlugin::class);
        $this->registerNgsPlugin(NgsLongTextFunctionSmartyPlugin::class);
        $this->registerNgsPlugin(NgsNewLineFunctionSmartyPlugin::class);
        $this->registerNgsPlugin(NgsHiddenFunctionSmartyPlugin::class);
        $this->registerNgsPlugin(NgsListFunctionSmartyPlugin::class);
        $this->registerNgsPlugin(NgsTableBulkActionsFunctionSmartyPlugin::class);
        $this->registerNgsPlugin(NgsTableFilterFunctionSmartyPlugin::class);
        $this->registerNgsPlugin(NgsNumberFunctionSmartyPlugin::class);
        $this->registerNgsPlugin(NgsSelectFunctionSmartyPlugin::class);
        $this->registerNgsPlugin(NgsEmailFunctionSmartyPlugin::class);
        $this->registerNgsPlugin(NgsDateFunctionSmartyPlugin::class);
        $this->registerNgsPlugin(NgsTimeFunctionSmartyPlugin::class);
        $this->registerNgsPlugin(NgsTextareaFunctionSmartyPlugin::class);
        $this->registerNgsPlugin(NgsPasswordFunctionSmartyPlugin::class);
        $this->registerNgsPlugin(NgsDropzoneFunctionSmartyPlugin::class);
        $this->registerNgsPlugin(NgsPaginationFunctionSmartyPlugin::class);
        $this->registerNgsPlugin(NgsViewDateFunctionSmartyPlugin::class);
        $this->registerNgsPlugin(NgsViewTextareaFunctionSmartyPlugin::class);
      
    }
}
