<?php
require_once 'init.php';
$parse = new CDlgl_Sipo_Gov_Cn();
$log = new CLog();
$objDataSave = new CMysqlDataSave("localhost", "root", "xtqiqi", "caiji", "sb_daili_company");
$gather = new CNormalGather($log, $objDataSave, $parse);
$observer =new CProccessObserver();
$gather->attach($observer);
$gather->Start();