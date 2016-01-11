<?php 
	require '../init.php';
	require 'dianping.php';
	$action	= $_REQUEST["action"];
	$action || $action = "onDefault";
	$Dianping = new Dianping();
	call_user_func(array($Dianping,$action));
	//call_user_method($action, $low);
	die("done!");