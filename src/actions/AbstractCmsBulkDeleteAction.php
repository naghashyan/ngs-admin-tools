<?php
/**
 * general parent action to bulk deleting items
 *
 * @author Mikael Mkrtchyan
 * @site   http://naghashyan.com
 * @email  mikael.mkrtchyan@naghashyan.com
 * @year   2020
 * @package ngs.AdminTools.actions
 * @version 1.0.0
 *
 **/

namespace ngs\AdminTools\actions;


abstract class AbstractCmsBulkDeleteAction extends CmsBulkAction
{


    /**
     * @throws \ngs\exceptions\DebugException
     */
    public final function service()
    {

        $this->beforeCmsAction();

        $manager = $this->getManager();
        $itemDto = $manager->createDto();
        $this->initializeVisibleFieldsMethods($itemDto);
        $paramsBin = $this->getNgsListBinParams();
        $this->getLogger()->info('bulk delete action started', (array) $paramsBin);
        $deleteResult = false;
        if($paramsBin !== null) {
            $deleteResult = $manager->deleteByParams($paramsBin);
        }
        $this->addParam('success', $deleteResult);
        $this->addParam('afterActionLoad', $manager->getListLoad());
        $this->addParam('afterBulkDeleteActionLoad', $manager->getListLoad());

        $this->afterCmsAction();
        if($deleteResult) {
            $this->getLogger()->info('bulk delete action finished');
        }
        else {
            $this->getLogger()->error('bulk delete action failed');
        }

    }
}