<?php
class pdfPreview {

  function __construct($filename,$dir) {
    $this->file = $filename;
    $this->dir = $dir;

  }

  public static function genPreview($file) {
   
    global $CONF;

    if (!is_file($CONF["thumbnailer_bin"])) return -1;

    exec($CONF["thumbnailer_bin"]." $file");
  
    return $CONF["thumbnailer_bin"]." $file";
  }
  
  public function isUserhome() {

    $thumb = $this->dir."/th/".$this->file.".jpg";
    $isUserObjectName = preg_match('/^o[0-9]{1,9}/', $this->file);
    $isUserhome = (is_file($thumb) && $isUserObjectName); // wenn Titelbild als Thumbnail (Namensschema: o123.jpg) in Userverzeichnis liegt, dann handelt es sich um ein bereits importiertes PDF

    return $isUserhome;
  }

  public function checkState() {

    logger::append("checkState ".$this->dir."/".$this->file,3);
    if (is_file($this->dir."/".$this->file.".ok")) {
      return "ok";
    }
    if (is_file($this->dir."/".$this->file.".error")) {
      return "error";
    }
    if (is_file($this->dir."/".$this->file.".lock")) {
       $pid = trim(file_get_contents($this->dir."/".$this->file.".lock"));
       if (file_exists( "/proc/$pid")) {
        return "pid:$pid";
      }
    }
    if (is_file($this->dir."/".$this->file.".queue")) {       
        return "queue";
    }

    return "error:".is_file($this->dir."/".$this->file.".lock").$this->dir."/".$this->file.".lock";

  }

  public function getPreview($page,$size) {

    logger::append("getPreview page $page $size");
    if ($page=="all" || (int)$page>0) {
      $rows = array();
      if($this->isUserhome()){
        $status["userhome"] = 1;
        for($i = 1; $i<=100; $i++){
          $filename = $this->file.".".$i.".jpg";
          if(is_file($this->dir."/th/".$filename)){
            $rows[] = $filename;
          }
        }
      } else {
        $okfile = file_get_contents($this->dir."/".$this->file.".ok");
        if($okfile){
          $status["okfile"] = 1;
          $rows = explode("\n",$okfile);
        }
      }

      if (count($rows)) {
        $preview64 = array();
        foreach ($rows as $i => $row) {
          if($page!='all' && (int)$page!=$i+1){
            continue;
          }
          logger::append("pdfPreview->getPreview $row",3);
          if($status["okfile"]){
            $col = explode (" ",$row);
            $file_path = $col[0];
            if (!$file_path) continue;
            $dir = dirname($file_path);
            $filename = basename($file_path);
          } else if($status["userhome"]){
            $dir = $this->dir;
            $filename = $row;
          }

          $th_path = "$dir/th/$filename";
          $gal_path = "$dir/gal/$filename";
          logger::append("th_path $th_path",3);
          $status["path"][$i] = $th_path;
          $preview64[$i]["th_path"] = $th_path;
          $preview64[$i]["gal_path"] = $gal_path;
          if (is_file($gal_path)) {
            $img = imagecreatefromjpeg($gal_path);
            $img_w = imagesx($img);
            $img_h = imagesy($img);

            if($img_w > $img_h){
              $new_w = $size;
              $new_h = round(($img_h * $size) / $img_w);
            } else {
              $new_w = round(($img_w * $size) / $img_h);
              $new_h = $size;
            }
            $img_sized = imagecreatetruecolor ($new_w, $new_h);
            imagecopyresampled($img_sized, $img, 0, 0, 0, 0, $new_w, $new_h, $img_w, $img_h);

            ob_start();
            imagejpeg($img_sized);
            $img_buffered = ob_get_contents();
            ob_end_clean();

            logger::append("resize gal_th $img_w $img_h to $new_w $new_h",3);
            logger::append("encoding to base64 $gal_path",3);
            $preview64[$i]["src"] = "data:image/jpeg;base64,".base64_encode($img_buffered);
          }
          else if (is_file($th_path)) {
             logger::append("encoding to base64 $th_path",3);
             $preview64[$i]["src"] = "data:image/jpeg;base64,".base64_encode(file_get_contents($th_path));
          }

        }
      }
      return $preview64;
    }

  }

  public function waitFinish() {
     set_time_limit(300);

     $state = $this->checkState();
     logger::append("function waitFinish() state $state",3);
     $ST = explode(":",$state);
     logger::append("Waiting for PID ".$ST[1]." to finish",3);
     while ($ST[0] == "pid" || $ST[0] == "queue") {
       sleep(1);
       $ST = explode(":",$this->checkState());
     }

     return join(":",$ST);
  }

  public function render($page,$size,$format="json") {

    if($this->isUserhome()){
      $state = 'load from userhome';
      logger::append("load from userhome",3);
    } else {
      $state = $this->waitFinish();
    }

    $image = $this->getPreview($page,$size);

    logger::append("image rendered. format:$format",3);
    
    if ($format == "json") {
      header('Content-type: application/json');
      $out = json_encode(array(
        "state" => $state,
        "image" => $image
      ));
    } else if ($format == "image") {
      header('Content-type: image/jpeg');
      $out = $image[$page-1]["image"];
    }

    echo $out;
    exit;

  }
}


?>
