<?php
require_once 'init.php';
$log = new CLog();
$log->SetOutputType(LogOutputType::FILE);
echo "----------------------------��ʼ�ɼ�-----------------------------------------<br>";
$start = intval($_REQUEST["s"]);
$end = intval($_REQUEST["e"]);
$parse = new CShangDunParse($start,$end);
$datasave = new CCsvDataSave("",$log);
$gather = new CNormalGather($log,$datasave,$parse );
$gather->Start();
echo "<br>".$start."-".$end."ҳ�ɼ���ɣ�";
