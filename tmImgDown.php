<?php
define("DEBUG",false);
require_once 'init.php';
$config = new CConfig(ROOT."/data/config-downimage.php");
$log = new CLog("downimage");
$log->SetOutputType(LogOutputType::FILE);
$csvsave = new CCsvDataSave(ROOT."/data/failddown/failddown.csv",$log);
$download = new Download($config, $csvsave,$log ,ROOT."/data/downimagedata/");
$download->Start();



class Download{
	var $sourceDir;
	var $config;
	var $log;
	var $csvSave;
	public function __construct(CConfig $config,CCsvDataSave $csvsave,CLog $log,$sourceDir){
		$this->config = $config;
		$this->log = $log;
		$this->csvSave = $csvsave;
		if(!is_dir($sourceDir)) throw new Exception("错误的数据源目录！");
		$this->sourceDir = $sourceDir;
	}
	function Start(){
		$dh = opendir($this->sourceDir);
		if(!$dh) die("打开数据源失败！");
		$param = $this->config->Params();
		while($file = readdir($dh)){
			if(in_array($file,array(".",".."))) continue;
			if(strtolower(substr($file, 0,5)) == "over_") continue; //已下载过的文件；
			$file = trim($this->sourceDir,"/")."/".$file;
			$type = strrchr($file,".");
			if($type != ".csv"){
				$this->log->PrintError("非法的csv文件: $file");
				continue;	
			}
			if(is_dir($file)) continue;
		
			
			$fh = fopen($file, "r");
			if(!$fh){
				$this->log->PrintError("打开源文件失败：$file");
				continue;
			}
			$line = 0;
			$errorNum = 0;
			while($data = fgetcsv($fh)){
				$line++;
				$tmName = iconv("GBK","utf-8",$data[0]);
				if($tmName == "商标名称") continue;
				$tmNum = $data[1];
				$tmType = $data[2];
				$poto = trim($data[8]);
				$poto = str_replace(".chaxun9.com/", ".shangdun.org/", $poto);
				if(empty($tmName) || !is_numeric($tmType) || empty($tmNum) || !is_numeric($tmNum)) {
					$this->log->PrintError("[$tmName-$tmNum-$tmType] 商标基本信息不合法:".$file);
					continue;
				} 
				if(empty($poto)){
					$this->log->PrintError("图像为空: $tmName-$tmNum-$tmType");
					continue;
				}
				try{
					$filename = md5(md5($tmNum).md5($tmType));
					$this->DownloadImg($poto,$filename,$tmNum);
					echo $line."[$tmName] <br>";
					ob_flush();
					flush();
					$errorNum = 0;
				}
				catch (Exception $e){
					$csvdata = null;
					$csvdata["title"] = array("商标名称","注册号申请号","类别","图片地址","新文件名","错误代码");
					$csvdata["value"][0] = array($tmName,$tmNum,$tmType,$poto,$filename,$e->getCode());
					$this->csvSave->Save($csvdata);
					$this->log->PrintError($e->getCode()."[$tmName-$tmNum-$tmType]".$e->getMessage());
					
					if($e->getCode() === 10003) $errorNum++;
					//连续10次打不开源程序时，停止程序
					if($errorNum > 10){
						while(true){
							$source = "http://img.shangdun.org/ImgShow.asp?R=5494164&T=14";
							if(@getimagesize($source)) break;
							sleep($seconds);
						}
						$errorNum = 0;
					} 
					continue;
				}
			}
			if($fh){
				$basename = basename($file);
				$newname = "over_".$basename;
				$newname = dirname($file)."/".$newname;
				rename($file, $newname);
			}
			fclose($fh);
		}
			
			
	}

	
	private function DownloadImg($from,$toFileName,$tmNum){
		$targetDir = dirname(dirname("__FILE__"))."/data/download/image";
		$last5 = substr($tmNum,-5);
		$index = intval($last5 / 50000)+1;
		$dirName = substr($tmNum, 0,strlen($tmNum)-5).$index;
		$targetDir = $targetDir."/".$dirName;
		CImage::CopyImage($from,$targetDir,$toFileName);
		return $value;
	}
}