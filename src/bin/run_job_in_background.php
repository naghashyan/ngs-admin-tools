<?php

include("MainIlyovBin.php");

use ngs\AdminTools\managers\jobs\JobsManager;

$args = \ngs\util\NgsArgs::getInstance()->getArgs();
if(!isset($args['job_id'])) {
    echo 'job id not provided';
    exit;
}
try {
    $jobId = $args['job_id'];
    $jobManager = JobsManager::getInstance();
    $jobManager->executeJobById($jobId);
}
catch(\Exception $exp) {
    echo $exp->getMessage();
}
