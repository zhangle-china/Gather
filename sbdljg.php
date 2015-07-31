<?php
require 'init.php';
$sourcePath = "http://sbsq.saic.gov.cn:9080/tmoas/agentInfo_getAgentDljg.xhtml";
$source = array(
	//	0=>array("fname"=>"备案代理机构总名单","url"=>"http://sbsq.saic.gov.cn:9080/tmoas/agentInfo_getAgentDljg.xhtml","startpage"=>1,"endpage"=>275,"pagesize"=>50),
		0=>array("fname"=>"备案代理机构(律所)名单","url"=>"http://sbsq.saic.gov.cn:9080/tmoas/agentInfo_getAgentDlsws.xhtml","startpage"=>1,"endpage"=>171,"pagesize"=>50),
	//	0=>array("fname"=>"代理机构注销备案名单","url"=>"http://sbsq.saic.gov.cn:9080/tmoas/agentInfo_getAgentDlzx.xhtml","startpage"=>1,"endpage"=>3,"pagesize"=>50),
);

foreach($source as $s){
	$parse = new CSbDljgParse($s["url"], $s["startpage"], $s["endpage"],$s["pagesize"]);
	$fname = iconv("utf-8","GBK",$s["fname"]);
	$log = new CLog($fname);
	$objDataSave = new CCsvDataSave($fname."csv",$log);
	$gather = new CNormalGather($log, $objDataSave, $parse);
	$observer =new CProccessObserver();
	$gather->attach($observer);
	$gather->Start();
}

