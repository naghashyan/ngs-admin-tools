<?php
/**
 * General parent cms delete action.
 *
 *
 * @author Mikael Mkrtcyan
 * @site http://naghashyan.com
 * @mail mikael.mkrtchyan@naghashyan.com
 * @year 2010-2019
 * @package ngs.AdminTools.actions
 * @version 9.0.0
 *
 */

namespace ngs\AdminTools\actions;

use ngs\AdminTools\managers\AbstractCmsManager;
use ngs\AdminTools\managers\TranslationManager;
use ngs\request\AbstractAction;
use ngs\exceptions\NgsErrorException;

abstract class AbstractDeleteAction extends AbsctractCmsAction
{

    /**
     * called before service function
     *
     * @param $itemDto
     */
    public function beforeService()
    {
    }


    /**
     * @throws \Exception
     */
    public final function service()
    {
        try {
            $this->getLogger()->info('delete action started ' . $this->args()->itemId);
            $this->beforeService();
            /** @var AbstractCmsManager $manager */
            $manager = $this->getManager();

            if ($this->args()->itemId) {
                $itemId = $this->args()->itemId;
                $itemDto = $manager->getItemById($itemId);
                if (!$itemDto) {
                    throw new NgsErrorException('Item not found.');
                }
                $hasDeleteProblem = $manager->getDeleteProblems($this->args()->itemId);
                if ($hasDeleteProblem && (!$this->args()->confirmationMessage || $this->args()->confirmationMessage !== $hasDeleteProblem['confirmation_text'])) {
                    $errorParams = $hasDeleteProblem;
                    $errorParams['confirmation_required'] = true;
                    throw new NgsErrorException('confirmation message is incorrect!', -1, $errorParams);
                }

                $manager->deleteItemById($itemId);

                $this->deleteTranslations($itemDto);
            } else {
                throw new NgsErrorException('item id incorrect');
            }
            $this->addParam('afterActionLoad', $this->getAfterActionLoad());
            $this->addPagingParameters();

            $this->afterService($itemDto);
            $this->loggerActionEnd($itemDto);
            $this->getLogger()->info('delete action finished ' . $this->args()->itemId);
        } catch (\Exception $exp) {
            $this->getLogger()->error('delete action failed ' . $this->args()->itemId . ' ' . $exp->getMessage());
            throw $exp;
        }
    }

    /**
     * called after service function, gets in parameter deleted item DTO
     *
     * @param $itemDto
     */
    public function afterService($itemDto)
    {
    }

    /**
     * returns load which will called after action
     * @return string
     */
    public function getAfterActionLoad(): string
    {
        return '';
    }


    /**
     * add paging params to response
     */
    protected function addPagingParameters()
    {
        $result = [];
        $page = $this->args()->page ? $this->args()->page : 1;
        $result['page'] = $page;
        if ($this->args()->limit) {
            $result['limit'] = $this->args()->limit;
        }
        if ($this->args()->search_key) {
            $result['search_key'] = $this->args()->search_key;
        }
        if ($this->args()->sorting) {
            $result['sorting'] = $this->args()->sorting;
        }
        if ($this->args()->ordering) {
            $result['ordering'] = $this->args()->ordering;
        }
        if ($this->args()->filter) {
            $result['filter'] = $this->args()->filter;
        }
        
        $this->addParam('afterActionParams', $result);
    }

    /**
     * returns manager
     *
     * @return mixed
     */
    public abstract function getManager();


    private function deleteTranslations($itemDto)
    {
        $translationsManager = TranslationManager::getInstance();
        $translationsManager->deleteItemsTranslations($itemDto);
    }
}