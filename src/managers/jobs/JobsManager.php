<?php
/**
 * JobsManager manager class
 * for handle jobs
 *
 * @author Mikael Mkrtchyan
 * @site http://naghashyan.com
 * @mail mikael.mkrtchyan@naghashyan.com
 * @year 2021
 * @package ngs.NgsAdminTools.managers.jobs
 * @version 1.0.0
 *
 */

namespace ngs\NgsAdminTools\managers\jobs;

use ngs\NgsAdminTools\dal\dto\job\JobDto;
use ngs\NgsAdminTools\dal\mappers\job\JobMapper;
use ngs\NgsAdminTools\exceptions\NgsJobException;
use ngs\NgsAdminTools\managers\executors\AbstractJobExecutor;
use ngs\NgsAdminTools\managers\notification\NotificationsManager;
use ngs\AbstractManager;

class JobsManager extends AbstractManager {

    const TO_EXECUTE_STATUS = 'to_execute';
    const RUNNING_STATUS = 'running';
    const FINISHED_STATUS = 'finished';

    private static ?JobsManager $instance = null;

    /**
     * Returns an singleton instance of this class
     *
     * @return JobsManager Object
     */
    public static function getInstance(): JobsManager {
        if (self::$instance === null){
            self::$instance = new self();
        }
        return self::$instance;
    }


    /**
     * returns job by id
     *
     * @param int $id
     * @return JobDto|null
     */
    public function getById(int $id) :?JobDto {
        $mapper = $this->getMapper();
        /** @var JobDto $job */
        $job = $mapper->getById($id);

        return $job;
    }


    /**
     * create new job instance
     *
     * @param $name
     * @param $params
     * @param $executor
     * @return JobDto|null
     */
    public function createJob($name, $params, $executor) {
        $mapper = $this->getMapper();
        $jobDto = $mapper->createDto();

        $jobDto->setName($name);
        $jobDto->setParams(json_encode($params));
        $jobDto->setExecutor($executor);
        $jobDto->setStatus('to_execute');

        $userId = NGS()->getSessionManager()->getUser() ? NGS()->getSessionManager()->getUser()->getId() : null;
        if($userId) {
            $jobDto->setUserId($userId);
        }

        try {
            $id = $mapper->insertDto($jobDto);
            if($id) {
                $jobDto->setId($id);
                return $jobDto;
            }

            return null;
        }
        catch (\Exception $exp) {
            return null;
        }
    }


    /**
     * @param int $jobId
     * @return array
     */
    public function getJobStatus(int $jobId) {
        $job = $this->getById($jobId);
        if(!$job) {
            return ['success' => false, 'message' => 'job not found'];
        }

        return [
            'success' => true,
            'status' => $job->getStatus(),
            'progress' => $job->getProgress(),
            'data' => $job->getResult() ? json_decode($job->getResult(), true) : []
        ];
    }


    /**
     * use this function to execute job
     *
     * @param JobDto $job
     * @throws NgsJobException
     */
    public function executeJob(JobDto $job) {
        if($job->getStatus() !== self::TO_EXECUTE_STATUS) {
            throw new NgsJobException('job should be in to_execute state');
        }

        $executor = $job->getExecutor();
        if(!$executor) {
            throw new NgsJobException('executor is not set');
        }

        try {
            $params = $job->getParams();
            if($params) {
                $params = json_decode($params, true);
            }
            else {
                $params = [];
            }

            /** @var AbstractJobExecutor $executorObject */
            $executorObject = new $executor($params);
            if(!$executorObject instanceof AbstractJobExecutor) {
                throw new NgsJobException('executor should be extend AbstractJobExecutor class');
            }

            $mapper = $this->getMapper();

            $job->setStatus(self::RUNNING_STATUS);
            $job->setProgress(0);
            $updateStatusResult = $mapper->updateByPK($job);

            if(!$updateStatusResult) {
                throw new NgsJobException('something went wrong');
            }

            if($executorObject->isAsync()) {
                $this->runJobAsync($job->getId());
            }
            else {
                $result = $executorObject->runJob();
                $job->setStatus(self::FINISHED_STATUS);
                $job->setResult(json_encode($result));
                $job->setProgress(100);
                $updateStatusResult = $mapper->updateByPK($job);

                if(!$updateStatusResult) {
                    throw new NgsJobException('something went wrong');
                }
            }

        }
        catch(\Exception $exp) {
            throw new NgsJobException($exp->getMessage());
        }
    }


    /**
     * this function should be called from process, do not call this function from code
     *
     * @param int $jobId
     *
     * @throws NgsJobException
     */
    public function executeJobById(int $jobId) {

        /** @var JobDto $job */
        $job = $this->getById($jobId);
        if(!$job) {
            throw new NgsJobException('job not found');
        }

        if($job->getStatus() !== self::RUNNING_STATUS) {
            throw new NgsJobException('job is in wrong state');
        }

        if($job->getProgress() > 0) {
            throw new NgsJobException('job is already running');
        }

        $executor = $job->getExecutor();
        if(!$executor) {
            throw new NgsJobException('executor is not set');
        }

        $params = $job->getParams();
        if($params) {
            $params = json_decode($params, true);
        }
        else {
            $params = [];
        }

        /** @var AbstractJobExecutor $executorObject */
        $executorObject = new $executor($params);
        if(!$executorObject instanceof AbstractJobExecutor) {
            throw new NgsJobException('executor should be extend AbstractJobExecutor class');
        }

        $mapper = $this->getMapper();
        $notificationManager = NotificationsManager::getInstance();
        $notification = null;

        if($executorObject->isNotifiable() && $job->getUserId()) {
            $notification = $notificationManager->createNotification($job->getUserId(), $executorObject->getJobName(), "processing...", true);
        }

        $result = $executorObject->runJob(function(int $progress) use ($job, $mapper, $notification, $notificationManager) {
            $job->setProgress($progress);
            $mapper->updateByPK($job);
            if($notification) {
                $notificationManager->updateNotification($notification->getId(), $progress);
            }
        });


        try {
            $job->setStatus(self::FINISHED_STATUS);
            $job->setResult(json_encode($result));
            $job->setProgress(100);
            $updateStatusResult = $mapper->updateByPK($job);

            if($notification) {
                $resultMessage = $executorObject->getResultMessage();
                $notificationManager->updateNotification($notification->getId(), 100, $resultMessage);
            }
        }
        catch (\Exception $exp) {
            if($notification) {
                $notificationManager->updateNotification($notification->getId(), 100, $exp->getMessage());
            }
            throw new NgsJobException($exp->getMessage());
        }
    }


    /**
     * @return JobMapper
     */
    public function getMapper() {
        return JobMapper::getInstance();
    }


    /**
     * run job in background
     *
     * @param int $jobId
     */
    private function runJobAsync(int $jobId) {
        $os = substr(php_uname(), 0, 7);
        $backgroundExecutor = 'php ' .  __DIR__ . '/../../../bin/run_job_in_background.php -job_id=' . $jobId . ' -env=' . NGS()->getEnvironment();
        if ($os == "Windows"){
            pclose(popen("start /B ". $backgroundExecutor, "r"));
        }
        else {
            exec($backgroundExecutor . " > /dev/null &");
        }
    }
}