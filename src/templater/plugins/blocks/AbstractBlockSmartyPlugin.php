<?php
/**
 * abstract class for block smarty plugins,
 * all created block type plugins should extend this class
 */

namespace ngs\NgsAdminTools\templater\plugins\blocks;


use ngs\NgsAdminTools\templater\plugins\AbstractSmartyPlugin;

//TODO: MJ: API docs
abstract class AbstractBlockSmartyPlugin extends AbstractSmartyPlugin
{

    /**
     * returns type of the plugin
     *
     * @return string
     */
    public final function getType(): string
    {
        return "block";
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
    abstract public function index($params, $content, $template, &$repeat): ?string;

    //TODO: MJ: rename the function and add docs
    public final function handler($params, $content, $template, &$repeat) :?string{
        return $this->index($params, $content, $template, $repeat);
    }

    /**
     * this function should returns string which will be used in block plugin
     *
     * @param array $params
     * @return string
     */
    abstract protected function getBlockTemplate(array $params): string;
}
