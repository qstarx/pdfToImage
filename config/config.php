<?php

$CONF = array(
  "libdir" => realpath(__DIR__."/../lib"),
  "basedir" => realpath(__DIR__."/../"),
  "vendordir" => realpath(__DIR__."/../vendor"),
  "uploaddir" => realpath(__DIR__.'/../tmp/uploads'),
  "thumbnailer_bin" => realpath(__DIR__.'/../bin/worker/gen_pdf_preview.sh'),
  "loglevel" => 3,
  "logdir" => "/var/log",
  "pdfcheck" => 1,
  "pdfcheck_bin" => "/opt/pdfcheck/pdfcheck",
  "useJobQueue" => 1
);

include_once ($CONF["libdir"]."/autoload.php");
include_once ($CONF["vendordir"]."/autoload.php");

?>