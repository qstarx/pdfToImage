<?php

spl_autoload_register(function ($class_name) {
   
    global $CONF;

    $filename = $CONF["libdir"] . '/' . str_replace('\\', '/', $class_name) . '.php';

    include($filename);
    
});
?>