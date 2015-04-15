<?php
require_once 'init.php';
$config = new CConfig(ROOT."/data/config-shangdun.php");
$params = $config->Params();
intval($_REQUEST["s"]) && $params["startpage"] = intval($_REQUEST["s"]);
intval($_REQUEST["e"]) && $params["endpage"] = intval($_REQUEST["e"]);
$params["startpage"] ||$params["startpage"] = 1;
$params["endpage"] || $params["endpage"] = 10000;
if($params["endpage"] - $params["startpage"] === -1) $endFlag = true;

if($_POST){
	if($endFlag) exit("数据已采集完毕！");
	$log = new CLog();
	$log->SetOutputType(LogOutputType::FILE);
	echo str_pad("",4098);
	echo "----------------------------开始采集-----------------------------------------<br>";
	$start = $params["startpage"];
	$end = $params["endpage"];
	$parse = new CShangDunParse($start,$end);
	$datasave = new CCsvDataSave($params["datafile"],$log);
	$gather = new CNormalGather($log,$datasave,$parse);
	$observer = new CObserver($config);
	$gather->attach($observer);
	$gather->Start();	
	die("<script>window.location.href='shangbiao.php';</script>");
}

?>
<form action="" method="post">
	<p>
		<label>开始页：</label><input name="s" type="input" value="<?php echo $params["startpage"]?>"  >
		<label>结束页：</label><input name="e" type="input" value="<?php echo $params["endpage"]?>" >
	<p>
	<p>
	<?php if($endFlag){ ?>
		<span>已完成采集！</span>
	<?php }else{ ?>
		<input type="submit" value="开始采集" >
	<?php }?>
	</p>
		
	
</form>