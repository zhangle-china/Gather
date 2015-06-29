<?php
header("Content-type:text/html; charset=utf-8;");
define("DEBUG",true);
if(DEBUG){
	 error_reporting(E_ALL^E_WARNING^E_NOTICE);
}	 
else{
	error_reporting(0);
}
define(ROOT, dirname(__FILE__));
require_once 'library/gather.class.php';
require_once 'library/datasave.class.php';
require_once 'library/log.class.php';
require_once 'library/parse.php';
require_once 'library/interface.php';

set_time_limit(0);
function __autoload($classname){
	$classFile = "library/".substr($classname, 1).".class.php";
	$classFile = strtolower($classFile);
	if(file_exists($classFile)){
		require_once $classFile;
	}
}

?>
