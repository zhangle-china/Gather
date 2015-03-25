<?php
require_once 'library/gather.class.php';
require_once 'library/datasave.class.php';
require_once 'library/log.class.php';
require_once 'library/parse.php';
function __autoload($classname){
	$classFile = "library/".substr($classname, 1).".class.php";
	if(file_exists($classFile)){
		require_once $classFile;
	}
}
?>