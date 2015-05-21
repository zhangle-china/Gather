<?php
require_once 'init.php';
$config = new CConfig(ROOT."/data/config-shangdun.php");
$params = $config->Params();
intval($_REQUEST["s"]) && $params["startpage"] = intval($_REQUEST["s"]);
intval($_REQUEST["e"]) && $params["endpage"] = intval($_REQUEST["e"]);
$params["startpage"] ||$params["startpage"] = 1;
$params["endpage"] || $params["endpage"] = 10000;
if($params["endpage"] - $params["startpage"] === -1) $endFlag = true;
$type = $params["param"]["type"];
$type || $type = 0;
$status = array(
		array("label"=>"商标已注册","page"=>"86412"), 
		array("label"=>"商标已无效","page"=>"62394"), 
		array("label"=>"商标注册申请完成","page"=>""), 
		array("label"=>"商标注册申请等待受理通知书发文","page"=>""), 
		array("label"=>"商标转让完成","page"=>""), 
		array("label"=>"商标续展完成","page"=>""), 
		array("label"=>"商标变更完成","page"=>""), 
		array("label"=>"商标注册申请中","page"=>""), 
		array("label"=>"商标注册申请等待驳回复审","page"=>""), 
		array("label"=>"注册申请初步审定","page"=>""), 
		array("label"=>"驳回复审完成","page"=>""), 
		array("label"=>"注册申请部分驳回","page"=>""), 
		array("label"=>"国际领土延伸完成","page"=>""), 
		array("label"=>"商标异议完成","page"=>""), 
		array("label"=>"许可合同备案完成","page"=>""), 
		array("label"=>"驳回复审中","page"=>""), 
		array("label"=>"补发注册证完成","page"=>""), 
		array("label"=>"异议复审完成","page"=>""), 
		array("label"=>"商标异议申请中","page"=>""), 
		array("label"=>"商标注册申请等待驳回通知发文","page"=>""), 
		array("label"=>"收到异议申请或补充材料，待审","page"=>""), 
		array("label"=>"商标续展中","page"=>""), 
		array("label"=>"变更商标申请人/注册人名义/地址完成","page"=>""), 
		array("label"=>"撤销连续三-停止使用注册商标中","page"=>""), 
		array("label"=>"商标转让待审中","page"=>""), 
		array("label"=>"无效宣告完成","page"=>""), 
		array("label"=>"商标更正审查完成","page"=>""), 
		array("label"=>"开具注册证明完成","page"=>""), 
		array("label"=>"出具商标注册证明完成","page"=>""), 
		array("label"=>"补变转续证明完成","page"=>""), 
		array("label"=>"冻结商标中","page"=>""), 
		array("label"=>"撤销连续三-停止使用注册商标申请完成","page"=>""), 
		array("label"=>"领土延伸完成","page"=>""), 
		array("label"=>"商标异议申请完成","page"=>""), 
		array("label"=>"商标使用许可备案完成","page"=>""), 
		array("label"=>"撤销三-不使用审理完成","page"=>""), 
		array("label"=>"无效宣告中","page"=>""), 
		array("label"=>"商标变更待审中","page"=>""), 
		array("label"=>"特殊标志注册申请中","page"=>""), 
		array("label"=>"驳回复审待审中","page"=>""), 
		array("label"=>"领土延伸中","page"=>""), 
		array("label"=>"撤销三-不使用待审中","page"=>""), 
		array("label"=>"商标注销申请中","page"=>""), 
		array("label"=>"收到驳回复审申请或补充材料，待审","page"=>""), 
		array("label"=>"商标转让中","page"=>""), 
		array("label"=>"许可合同备案待审中","page"=>""), 
		array("label"=>"异议不予受理","page"=>""), 
		array("label"=>"打印异议结案通知书","page"=>""), 
		array("label"=>"异议复审待审中","page"=>""), 
		array("label"=>"变更商标代理人完成","page"=>""), 
		array("label"=>"变更商标代理人完成","page"=>"")
);


if($_POST){
	if($endFlag) exit("数据已采集完毕！");
	$log = new CLog();
	$log->SetOutputType(LogOutputType::FILE);
	echo str_pad("",4098);
	echo "----------------------------开始采集----------------------------------------<br>";
	$start = $params["startpage"];
	$end = $params["endpage"];
	$type = $status[$_POST["type"]]["label"];
	$parse = new CShangDunParse($start,$end);
	$parse->SetParam("type", $type);
	$csvfile = $params["datafile"];
	$csvfile = ROOT."/data/$type/data-".time()."-".rand(1, 100000).".csv";
	$datasave = new CCsvDataSave($csvfile,$log);
	$gather = new CNormalGather($log,$datasave,$parse);
	$observer = new CObserver($config);
	$gather->attach($observer);
	$process = new CProccessObserver();
	$gather->attach($process);
	
	$gather->Start();	
	die("<script>window.location.href='shangbiao.php';</script>");
}

?>
<form action="" method="post">
	<p>
	<select name="type">
		<?php 
			foreach($status  as $key=>$value){
				if($type == $value['label']){
					echo "<option value='$key' selected='selected'>".$value['label']."</option>";
				}
				else{
					echo "<option value='$key'>".$value['label']."</option>";
				}
			}
		?>
		
	</select>
	</p>
	<p>
		<label>开始页：</label><input name="s" type="input" value="<?php echo $params["startpage"]?>"  >
		<label>结束页：</label><input name="e" type="input" value="<?php echo $params["endpage"]?>" >
	<p>
	<p>
	<?php if($endFlag){ ?>
		<span>已完成采集！</span>
		<input type="submit" value="开始采集" >
	<?php }else{ ?>
		<input type="submit" value="开始采集" >
	<?php }?>
	</p>
		
	
</form>