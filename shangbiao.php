<?php
require_once 'init.php';
$log = new CLog();
$log->SetOutputType(LogOutputType::FILE);
echo "----------------------------开始采集-----------------------------------------<br>";
$start = intval($_REQUEST["s"]);
$end = intval($_REQUEST["e"]);
$parse = new CShangDunParse($start,$end);
$datasave = new CCsvDataSave("",$log);
$gather = new CNormalGather($log,$datasave,$parse );
$gather->Start();
echo "<br>".$start."-".$end."页采集完成！";
