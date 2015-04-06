<?php
error_reporting(E_ALL^E_WARNING^E_NOTICE);
define(ROOT, dirname(__FILE__));
require_once 'library/gather.class.php';
require_once 'library/datasave.class.php';
require_once 'library/log.class.php';
require_once 'library/parse.php';
require_once 'library/interface.php';

set_time_limit(0);
function __autoload($classname){
	$classFile = "library/".substr($classname, 1).".class.php";
	if(file_exists($classFile)){
		require_once $classFile;
	}
}
?>