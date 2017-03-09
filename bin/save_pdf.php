<?php
include_once __DIR__.'/../config/config.php';

session_start();

// Settings
$tmpDir = $CONF["uploaddir"];
$targetDir = $tmpDir.'/'.session_id().'/';


// Get a file name
if (isset($_REQUEST["name"])) {
  $fileName = $_REQUEST["name"];
} elseif (!empty($_FILES)) {
  $fileName = $_FILES["file"]["name"];
} else {
  $fileName = uniqid("file_");
}

// Create unique filename

if(file_exists($targetDir.$fileName)){
  $filePathInfo=pathinfo($fileName);
  $fileNameTrimed =$filePathInfo["filename"];
  for($i = 1; $i<999; $i++){
    $fileNameIterated = $fileNameTrimed."___".$i.".".$filePathInfo["extension"];
    if(!file_exists($targetDir.$fileNameIterated)){
      $fileName = $fileNameIterated;
      break;
    }
  }

}

// Upload Handler 

PluploadHandler::no_cache_headers();
PluploadHandler::cors_headers();
if (!$upload_return=PluploadHandler::handle(array(
  'target_dir' => $targetDir,
  'file_name'  => $fileName,
  'cb_check_file' => true
))) {
  out(json_encode(array(
    'OK' => 0,
    'error' => array(
      'code' => PluploadHandler::get_error_code(),
      'message' => PluploadHandler::get_error_message()
    )
  )),true);
  
} else {

  // Garbage collect
  
  cleanupOldTmpDirs($tmpDir, 10);

  $fileName=$upload_return["name"];

  // No file? -> exit
  
  if (!$fileName) die();

  // usage of pdfcheck is optional
  
  $checkResult = false;
  
  if ($CONF["pdfcheck"]) {
  
    $check = new pdfCheck($targetDir."/".$fileName);
    $check->analyze();
    file_put_contents($targetDir."/".$fileName.".json",json_encode($check->result));
    $checkResult = $check->result;
  }
  
  
  // Using JobQueue is recomended for large PDFs. 
  // This sends the rendering process to background using beanstalkd
  // requires start of bin/worker/worker.php in CLI mode
  
  if ($CONF["useJobQueue"]) {
    
    $queue = new jobQueue();
    $queue->submitJob("pdftool","genPreview",$targetDir.'/'.$fileName);
    file_put_contents($targetDir.'/'.$fileName.".queue","pdftool genPreview $targetDir.'/'.$fileName");
    
  } else {
    
    pdfPreview::genPreview($targetDir.'/'.$fileName);
    
  }
  
  $result = array ('result' => array('status' => 200),
                   'filename' => $fileName,
                   'dir' => $targetDir,
                   'pdfcheck' => $checkResult,
                   'pdfmatch' => array()
                  );
  out(json_encode($result),true);
  
}


function out($out,$exit=false) {

  if (!headers_sent()) header('Content-type: application/json');
  echo $out;
  if ($exit) exit();
}

function cleanupOldTmpDirs($tmpDir, $days) {
  $dir = scandir($tmpDir);
  $i = 1;
  foreach($dir as $elem) {
    if($elem == '.' || $elem == '..') continue;
    $backtime = time() - ($days * 60 * 60 * 24);
    $backtime = mktime(23, 59, 59, date('m', $backtime), date('d', $backtime), date('Y', $backtime));
    if(filemtime($tmpDir . '/' . $elem) < $backtime) {
      rrmdir($tmpDir . '/' . $elem);
    }
  }
}

function rrmdir($dir) {
  if(is_dir($dir) && $dir != "/") {
    $objects = scandir($dir);
    foreach($objects as $object) {
      if($object != "." && $object != "..") {
        if(filetype($dir . "/" . $object) == "dir") rrmdir($dir . "/" . $object); else unlink($dir . "/" . $object);
      }
    }
    reset($objects);
    rmdir($dir);
  }
}





?>
