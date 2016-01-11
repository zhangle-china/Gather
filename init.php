<?php
header("Content-type:text/html; charset=utf-8;");
if(!defined("DEBUG")) define("DEBUG",true);
if(DEBUG){
	 error_reporting(E_ALL^E_WARNING^E_NOTICE);
}	 
else{
	error_reporting(0);
}
define(ROOT, dirname(__FILE__));
require_once 'library/interface.php';
require_once 'library/datasave.class.php';
require_once 'library/log.class.php';
require_once 'library/parse.php';
require_once 'library/parsemsg.class.php';



set_time_limit(0);
function __autoload($classname){
	$classname = strtolower($classname);
	$classFile = ROOT."/library/".substr($classname, 1).".class.php";
	if(file_exists($classFile)){
		require_once $classFile;
	}
}

?>
