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
	if($endFlag) exit("�����Ѳɼ���ϣ�");
	$log = new CLog();
	$log->SetOutputType(LogOutputType::FILE);
	echo str_pad("",4098);
	echo "----------------------------��ʼ�ɼ�-----------------------------------------<br>";
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
		<label>��ʼҳ��</label><input name="s" type="input" value="<?php echo $params["startpage"]?>"  >
		<label>����ҳ��</label><input name="e" type="input" value="<?php echo $params["endpage"]?>" >
	<p>
	<p>
	<?php if($endFlag){ ?>
		<span>����ɲɼ���</span>
	<?php }else{ ?>
		<input type="submit" value="��ʼ�ɼ�" >
	<?php }?>
	</p>
		
	
</form>