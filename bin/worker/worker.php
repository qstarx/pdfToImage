<?php
include_once (__DIR__."/../../config/config.php");

use Pheanstalk\Pheanstalk;

function genPreview($data) {

   pdfPreview::genPreview($data);
}

$queue = new Pheanstalk('127.0.0.1');

while (true) {  
    // grab the next job off the queue and reserve it  
    $job = $queue->watch('pdftool')  
        ->ignore('default')  
        ->reserve();

    // decode the json data  
    $job_data = json_decode($job->getData(), false);

    $function = $job_data->function;  
    $data = $job_data->user_data;

    // run the function  
    $function($data);

    // remove the job from the queue  
    $queue->delete($job);  
}  

?>