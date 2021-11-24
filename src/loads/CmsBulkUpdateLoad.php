<?php
/**
 * General parent load for all NGS admin bulk load classes
 *
 * @author Mikael Mkrtchyan
 * @site   http://naghashyan.com
 * @email  mikael.mkrtchyan@naghashyan.com
 * @year   2021
 * @package ngs.NgsAdminTools.loads
 * @version 1.0
 *
 **/

namespace ngs\NgsAdminTools\loads;

use ngs\exceptions\NgsErrorException;

abstract class CmsBulkUpdateLoad extends AbstractCmsLoad
{


    const NGS_CMS_EDIT_ACTION_TYPE_POPUP = "popup";
    const NGS_CMS_EDIT_ACTION_TYPE_INPLACE = "inplace";


    public function getTemplate(): string
    {
        return NGS()->getTemplateDir('ngs-cms') . "/bulk_update.tpl";
    }

    /**
     * returns get edit action type
     * @return string
     */
    public function getEditActionType(): string
    {
        return "";
    }


    /**
     *
     */
    public final function load()
    {
        if (!$this->args()->item_ids) {
            throw new NgsErrorException("Items not selected.");
        }
        $editActionType = $this->getEditActionType();
        if ($editActionType == self::NGS_CMS_EDIT_ACTION_TYPE_POPUP) {
            $editActionType = self::NGS_CMS_EDIT_ACTION_TYPE_POPUP;
        } else {
            $editActionType = self::NGS_CMS_EDIT_ACTION_TYPE_INPLACE;
        }
        $this->addJsonParam("editActionType", $editActionType);
        $this->addParam("itemIds", $this->args()->item_ids);
        $this->addJsonParam("cancelLoad", $this->getCancelLoad());
        if ($this->args()->parentId) {
            $this->addJsonParam("parentId", $this->args()->parentId);
        }

        $this->addJsonParam("saveAction", $this->getSaveAction());

        $this->afterCmsLoad();
    }

    public abstract function getCancelLoad(): string;

    public abstract function getSaveAction(): string;

    /**
     * called after load
     */
    public function afterCmsLoad()
    {

    }

}
