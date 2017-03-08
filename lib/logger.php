<?php

class logger {

  static function append($mess,$level,$errfile="", $errline="", $logprefix="") {
  
    global $CONF;
    
    // Der Loglevel wird in der config.php definiert
    // Konvention fuer das level: 1 = Fehler, 2 = Info, 3 = Debug
    // Im Standardbetrieb sollte ein Dienst auf Loglevel 1 oder 2 laufen
    
    if ($level>$CONF["loglevel"]) return;
    if ($CONF["logdir"]) $logdir = $CONF["logdir"];
    else $logdir = "/var/log";
    
    $pid=getmypid();
    if ($_SERVER["HTTP_HOST"]) {
      $logname=$logprefix.$_SERVER["HTTP_HOST"].".log";
    } else {
      $logname=$logprefix."php_shell.log";
    }
    $logfile="$logdir/$logname";
    $session_id=session_id();
    
    if ($session_id=="") $session_id="-";
    $rec = "[".date("d.m.Y H:i:s")."] $level";
    $rec .= " ".$_SERVER["PHP_SELF"];
    if ($_SESSION["adminuser"]) $rec .= " ".$_SESSION["adminuser"];
    else $rec.= " -";
    if ($_SESSION["verified_user"]) $rec .= " ".$_SESSION["verified_user"];
    elseif ($_SESSION["username"]) $rec .= " ".$_SESSION["username"];
    else $rec.= " -";

    $rec .= " $session_id $pid $errfile:$errline $mess ";

    error_log (trim($rec)."\n", 3, $logfile);

    
  }

}