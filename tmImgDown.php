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
		$this->scan($this->sourceDir);
			
	}

	private function scan($dir){
		$dh = opendir($dir);
		if(!$dh) die("打开数据源失败！");
		$param = $this->config->Params();
		while(($file = readdir($dh)) !== false){
			if(in_array($file,array(".",".."))) continue;
			if(strtolower(substr($file, 0,5)) == "over_") continue; //已下载过的文件；
			$file = trim($dir,"/")."/".$file;
			$type = strrchr($file,".");
			if(is_dir($file)) $this->scan($file);
			
			if($type != ".csv"){
				continue;
			}
			
		
			$breakLine = 0;
			if(md5($file) == $param["file"] ) $breakLine= @intval($param["line"]);
			$utfFile = iconv("GB2312","utf-8",$file);
			$fh = fopen($file, "r");
		
			if(!$fh){
				$this->log->PrintError("打开源文件失败：$utfFile");
				continue;
			}
			$line = 0;
			$errorNum =  0;
			echo "<p><B>$utfFile</B> 开始采集 ；</p>";
			$this->log->PrintNormal("开始采集:$utfFile");
			while(($data = fgetcsv($fh)) !== FALSE){
				$line++;
				if($breakLine && $line <= $breakLine) continue; //上次程序中断时已采集到的行
				if(!$data)  $this->log->PrintError("读取第$line行数据失败 :".$utfFile);
				$tmName = iconv("GBK","utf-8",$data[0]);
				if($tmName == "商标名称") continue;
				$tmNum = $data[1];
				$tmType = $data[2];
				$poto = trim($data[8]);


				if(empty($poto)){
					if(!($poto =  $this->GetRemoteImage($tmNum,$tmType))){
						die("图像为空: $tmName-$tmNum-$tmType");
						$this->log->PrintError("图像为空: $tmName-$tmNum-$tmType");
						continue;
					}
				}

				$poto = str_replace(".chaxun9.com/", ".shangdun.org/", $poto);
				if(empty($tmName) || !is_numeric($tmType) || empty($tmNum) || !is_numeric($tmNum)) {
					$this->log->PrintError("[$tmName-$tmNum-$tmType] 商标基本信息不合法:".$file);
					continue;
				} 
				
				try{
					$filename = md5(md5($tmNum).md5($tmType));
					$this->DownloadImg($poto,$filename,$tmNum);
					$params["file"]= md5($file);
					$params["line"] = $line;
					$this->config->WriteConfig($params);
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
						die("图片服务器无法访问，请稍后重试！".$utfFile);
						while(true){
							$source = "http://img.shangdun.org/ImgShow.asp?R=5494164&T=14";
							if(@getimagesize($source)) break;
							sleep(6000);
						}
						$errorNum = 0;
					} 
					continue;
				}
			}
			fclose($fh);
			if($fh){
				$basename = basename($file);
				$newname = "over_".$basename;
				$newname = dirname($file)."/".$newname;
				rename($file, $newname);
			}
		}
	}

	private function GetRemoteImage($tmNum,$tmType){

		$parse = new CShangDunParse();

		$url = "http://www.shangdun.org/show/";
		$url .= "?NowIdOn=$tmNum&NowCLOn=$tmType";
		$content = $parse->getUrlContent($url);
		$data = $parse->ArcContentParse($content,$url);
		if(!$data) return "";
		return $data["value"][8];
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