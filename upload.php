<?php
if(session_status() !== PHP_SESSION_ACTIVE){
	session_start();
}
date_default_timezone_set("Etc/GMT+2");
error_reporting(E_ALL);
ini_set("display_errors", 1);

chdir(__DIR__);

include("../../htse.conf.php");
include("include/base.php");
include("include/database.php");
include("include/xml.php");
include("include/siteMap.php");
include("include/object.php");
database::setOpt($conf['db']);
$sql = new database();
unset($sql);
foreach ($conf as $key => $value){
	if(is_numeric($value) || is_string($value) || is_bool($value)){
		define($key, $value);
	}
}
unset($conf);
include("include/user.php");
include("template/fileManager.php");
$fm = new fileManager();
$fm->upload();