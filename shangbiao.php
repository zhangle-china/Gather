<?php
require_once 'init.php';
$log = new CLog();
$parse = new CShangDunParse();
$datasave = new CCsvDataSave();
$gather = new CNormalGather($log,$datasave,$parse );
$gather->Start();