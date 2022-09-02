<?php
/**
 * JobStartedEventStructure class, called when job started
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

class JobStartedEventStructure extends AbstractEventStructure
{
    private ?JobDto $job;
    private ?string $jobTitle;

    public function __construct(array $params, ?JobDto $job, ?string $jobTitle)
    {
        parent::__construct($params);
        $this->job = $job;
        $this->jobTitle = $jobTitle;
    }

    public static function getEmptyInstance() :AbstractEventStructure {
        return new JobStartedEventStructure([], null, null, null);
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
            ]
        ];
    }
}