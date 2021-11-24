<?php
/**
 * General parent load for all NGS admin load classes
 *
 * @author Levon Naghashyan
 * @site   https://naghashyan.com
 * @email  levon@naghashyan.com
 * @year   2012-2019
 * @package ngs.cms.loads
 * @version 6.5.0
 *
 **/

namespace ngs\NgsAdminTools\loads;


use ngs\NgsAdminTools\managers\AbstractCmsManager;

abstract class CmsLoad extends AbstractCmsLoad
{

    public function initialize()
    {
        parent::initialize();
        $this->addParentParam('activeMenu', $this->getActiveMenu());
        $this->addJsonParam('cmsModal', $this->args()->cmsModal);
        $this->addParam('cmsUUID', $this->args()->cmsUUID);
        $this->addJsonParam('cmsUUID', $this->args()->cmsUUID);
    }

    public function getTemplate(): string
    {
        if ($this->args()->cmsModal) {
            //return NGS()->getTemplateDir('ngs-cms') . '/main_modal.tpl';
        }
        return NGS()->getTemplateDir('ngs-cms') . '/main_load.tpl';
    }

    protected function getActiveMenu()
    {
        return ['menu' => '', 'submenu' => ''];
    }


    /**
     * @return AbstractCmsManager|null
     */
    public function getManager()
    {
        return null;
    }


    /**
     * @return array
     */
    public function getDefaultLoads(): array
    {
        $manager = $this->getManager();
        if ($manager === null) {
            return [];
        }
        $loads = [];
        $args = [];
        if ($this->args()->parentId) {
            $args['parentId'] = $this->args()->parentId;
        }
        //TODO: check what is issue
        /*if($this->args()->cmsUUID) {
            $args['cmsUUID'] = $this->args()->cmsUUID;
        }*/

        $loads['items_content']['args'] = $args;
        $loads['items_content']['action'] = $manager->getListLoad();
        return $loads;
    }


    /**
     * @return string
     */
    public abstract function getSectionName(): string;

    /**
     * @return array
     */
    public abstract function getParentSections(): array;

    public final function load()
    {


        $currentUser = NGS()->getSessionManager()->getUser();
        $id = $currentUser->getId();
        //TODO: should check if such user exists in db, should be created dto mapper managers for user
        if (!$id) {
            $this->onNoAccess();
        }

        $manager = $this->getManager();

        $this->addParam('parentSections', $this->getParentSections());
        $this->addParam('sectionName', $this->getSectionName());
        $this->addJsonParam('addLoad', $manager->getAddLoad());
        $this->addJsonParam('mainLoad', $manager->getMainLoad());
        if ($this->args()->ngsContainer) {
            $this->addJsonParam('ngsContainer', $this->args()->ngsContainer);
        }

        $this->afterCmsLoad();
    }

    public function afterCmsLoad()
    {

    }

}
