<?php
define("DEBUG",true);
require_once 'init.php';
$db = new CMysql("localhost","root","xtqiqi","dt_trademark");
$config = new CConfig(ROOT."/data/config-tmcategory.php");
$tmCategory = new CTmCategory($db,$config);
$tmCategory -> Start();
?>