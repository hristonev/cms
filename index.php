<?php
session_start();
date_default_timezone_set("Etc/GMT+2");
error_reporting(E_ALL);
ini_set("display_errors", 1);

chdir(__DIR__);

include("../../htse.conf.php");
include("include/base.php");
include("include/database.php");
include("include/xml.php");
database::setOpt($conf['db']);
$sql = new database();
unset($sql);
unset($conf);

define("TABLE_ML_SUFFIX", "ML");	//multilanguage suffix

include("include/user.php");
include("include/manager.php");
$mng = new manager();
$code = $mng->render();
if($mng->ajax){
	echo $code;
}else{
	$dom = new DOMDocument(5, 'UTF-8');
	$dom->preserveWhiteSpace = false;
	$dom->formatOutput = true;
	$dom->loadHTML($code);
	$dom->encoding='UTF-8';
	echo $dom->saveHTML();
}

?>