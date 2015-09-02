<?php 
	require '../init.php';
	require 'low.php';
	$action	= $_REQUEST["action"];
	$action || $action = "onDefault";
	
	$low = new Low();

	call_user_func(array($low,$action));
	//call_user_method($action, $low);
	die("done!");
?>
