<?php
use Pheanstalk\Pheanstalk;

class jobQueue {

  public $queue = false;

  public function connect($target) { 
    $this->queue = new Pheanstalk('127.0.0.1');
  }
 
  public function submitJob($tube,$func,$data) {
  
    if (!$this->queue) $this->connect();
  
    $job = new stdClass();  
    // the function to run  
    $job->function = $func;  
    // our user entered data  
    $job->user_data = $data;

    $job_data = json_encode($job);

    $this->queue->useTube($tube)  
      ->put($job_data);
  }

}