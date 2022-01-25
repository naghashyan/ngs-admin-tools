<?php
/**
 * AbstractSageSyncableManager manager class
 * managers which are related with sage sync should be extended from this class
 *
 * @author Mikael Mkrtchyan
 * @site http://naghashyan.com
 * @mail mikael.mkrtchyan@naghashyan.com
 * @year 2020
 * @package ngs.AdminTools.managers.executors
 * @version 1.0.0
 *
 */

namespace ngs\AdminTools\managers\executors;

use Closure;
use Monolog\Logger;
use ngs\AdminTools\util\LoggerFactory;


abstract class AbstractJobExecutor
{
    protected array $params = [];
    private ?Logger $logger = null;
    private string $resultMessage = "";

    /**
     * indicates if execution should be asynchronous
     * @return bool
     */
    public function isAsync(): bool {
        return true;
    }


    public function __construct(array $params)
    {
        $this->params = $params;
        $this->logger = LoggerFactory::getLogger(get_class($this), get_class($this));
    }


    /**
     * if true, will send notification to user who started job
     *
     * @return bool
     */
    public function isNotifiable() :bool
    {
        return false;
    }

    /**
     * returns current job name
     * @return string
     */
    public function getJobName() :string
    {
        return "";
    }


    /**
     * @return Logger|null
     */
    public function getLogger() :Logger {
        return $this->logger;
    }

    /**
     * run job function, will return result of execution
     *
     * Closure $progressTracker
     *
     * @return array
     */
    public function runJob(Closure $progressTracker = null) :array {
        $result = $this->execute($progressTracker);
        return $result;
    }


    /**
     * result of the running job
     * @return string
     */
    public function getResultMessage() :string
    {
        return $this->resultMessage;
    }


    /**
     * set result message to job
     *
     * @param string $message
     */
    protected function setResultMessage(string $message)
    {
        $this->resultMessage = $message;
    }





    /**
     * override function to handle execution
     *
     * @param Closure $progressTracker
     *
     * @return array
     */
    protected abstract function execute( Closure $progressTracker = null) :array;
}
