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

namespace ngs\AdminTools\loads;


use ngs\AdminTools\dal\dto\UserDto;
use ngs\AdminTools\managers\AbstractCmsManager;
use ngs\AdminTools\managers\UserManager;
use ngs\AdminTools\managers\MediasManager;

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
            //return NGS()->getTemplateDir('ngs-AdminTools') . '/main_modal.tpl';
        }
        return NGS()->getTemplateDir('ngs-AdminTools') . '/main_load.tpl';
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
        if (!$id) {
            $this->onNoAccess();
        }

        /** @var UserDto $userDto */
        $userDto = UserManager::getInstance()->getUserById($id);

        if(!$userDto) {
            $this->onNoAccess();
        }

        $profileImage = MediasManager::getInstance()->getItemImagesUrlsAndDescriptions($id, 'users');
        if(!empty($profileImage)){
            $profileImage = $profileImage[0]['url']['original'];
        }else{
            $profileImage = NGS()->getDefinedValue('MY_HOST') . '/streamer/images/users/0';
        }

        $this->addParam('firstName', $userDto->getFirstName());
        $this->addParam('lastName', $userDto->getLastName());
        $this->addParam('userName', $userDto->getUserName());
        $this->addParam('profileImage', $profileImage);

        $manager = $this->getManager();
        $this->addParam('hasAddButton', $manager->loadShouldHaveAddButton());
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
