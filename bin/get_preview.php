<?php
include (__DIR__."/../config/config.php");


$ratio = intval($_POST["size"][0])/intval($_POST["size"][1]);
$px = 200;
if($ratio<=0.6) $px = 300;
if($ratio<=0.4) $px = 320;
if($ratio>=1.8) $px = 320;

$preview = new pdfPreview($_POST["filename"],$_POST["dir"]);

$preview->render('all', $px, 'json');

?>
