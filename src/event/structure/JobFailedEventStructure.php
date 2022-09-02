<?php
/**
 * AfterActionEventStructure class, can call after action
 *
 * @author Mikael Mkrtchyan
 * @site https://naghashyan.com
 * @mail miakel.mkrtchyan@naghashyan.com
 * @year 2022
 * @package ngs.AdminTools.managers.event.structure
 * @version 2.0.0
 *
 */

namespace ngs\AdminTools\event\structure;

use ngs\AdminTools\dal\dto\AbstractCmsDto;
use ngs\AdminTools\dal\dto\job\JobDto;
use ngs\event\structure\AbstractEventStructure;

class JobFailedEventStructure extends AbstractEventStructure
{
    private ?JobDto $job;
    private ?string $jobTitle;
    private ?string $failMessage;

    public function __construct(array $params, ?JobDto $job, ?string $jobTitle, ?string $failMessage)
    {
        parent::__construct($params);
        $this->job = $job;
        $this->jobTitle = $jobTitle;
        $this->failMessage = $failMessage;
    }

    public static function getEmptyInstance() :AbstractEventStructure {
        return new JobFailedEventStructure([], null, null, null);
    }

    /**
     * can be added notification from UI
     *
     * @return bool
     */
    public function isVisible() :bool {
        return true;
    }

    /**
     * @return JobDto|null
     */
    public function getJob(): ?JobDto
    {
        return $this->job;
    }

    /**
     * @param JobDto $job
     */
    public function setJob(JobDto $job): void
    {
        $this->job = $job;
    }

    /**
     * @return string
     */
    public function getJobTitle(): ?string
    {
        return $this->jobTitle;
    }

    /**
     * @param string $jobTitle
     */
    public function setJobTitle(string $jobTitle): void
    {
        $this->jobTitle = $jobTitle;
    }

    /**
     * @return string
     */
    public function getFailMessage(): ?string
    {
        return $this->failMessage;
    }

    /**
     * @param string $failMessage
     */
    public function setFailMessage(string $failMessage): void
    {
        $this->failMessage = $failMessage;
    }

    /**
     * returns list of varialbes which can be used in notification template
     *
     * @return array
     */
    public function getAvailableVariables() :array
    {
        return [
            "jobTitle" => [
                "type" => "text",
                "value" => $this->getJobTitle()
            ],
            "errorMessage" => [
                "type" => "text",
                "value" => $this->getFailMessage()
            ]
        ];
    }
}