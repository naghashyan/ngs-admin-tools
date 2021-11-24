<?php
/**
 * general parent action to bulk export items
 *
 * @author Mikael Mkrtchyan
 * @site   http://naghashyan.com
 * @email  mikael.mkrtchyan@naghashyan.com
 * @year   2020
 * @package ngs.cms.actions
 * @version 1.0.0
 *
 **/

namespace ngs\NgsAdminTools\actions;

use ngs\NgsAdminTools\managers\executors\ExcelExportExecutor;
use ngs\NgsAdminTools\managers\jobs\JobsManager;
use PhpOffice\PhpSpreadsheet\IOFactory;


abstract class AbstractCmsExportExcelAction extends CmsBulkAction
{
    /**
     * @throws \ngs\exceptions\DebugException
     */
    public final function service()
    {
        try {
            $jobManager = JobsManager::getInstance();
            $params = $this->modifyParams($this->getExportParams());
            $job = $jobManager->createJob('export_excel', $params, $this->getExportExecutorClass());
            $jobManager->executeJob($job);
            $this->addParam('jobId', $job->getId());
        } catch (\Exception $exp) {
            $this->addParam('error', $exp->getMessage());
        }
    }


    /**
     * returns executor class name to execute export
     *
     * @return string
     */
    protected function getExportExecutorClass(): string
    {
        return ExcelExportExecutor::class;
    }


    /**
     * @param array $params
     * @return array
     */
    protected function modifyParams(array $params): array
    {
        return $params;
    }


    /**
     * @return array
     * @throws \ngs\exceptions\DebugException
     */
    private function getExportParams()
    {
        return [
            'user_id' => NGS()->getSessionManager()->getUser()->getId(),
            'manager' => get_class($this->getManager()),
            'fields' => $this->args()->fields,
            'ordering' => $this->args()->ordering,
            'sorting' => $this->args()->sorting,
            'filter' => $this->args()->filter,
            'totalSelection' => $this->args()->totalSelection,
            'unCheckedElements' => $this->args()->unCheckedElements,
            'checkedElements' => $this->args()->checkedElements
        ];
    }

}