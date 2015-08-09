<?php 
	require '../init.php';
	require 'low.php';
	
	
	$action	= $_REQUEST["action"];
	$action || $action = "onDefault";
	
	$low = new Low();
	call_user_method($action, $low);
?>