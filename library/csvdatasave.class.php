<?php
class CCsvDataSave implements IDataSave{
	private $fileName;
	private $log;
	private $status;
	function __construct($fileName = "",CLog $log,$dirName=""){
		if(!empty($fileName)){
			$this->fileName = $fileName;
		}
		else{
			$this->fileName = dirname(dirname(__FILE__))."/data/data-".time().rand(1, 100000).".csv";
		}
		$dir = dirname($this->fileName);
		if(!is_dir($dir)){
			mkdir($dir,777,true);
		}
		
		$file =  $this->fileName;
		$this->log = $log;
		$this->status["filename"] = $file ;
	}
	
	function setFileName($fileName){
		$this->fileName = $fileName;
	}
	
	function SetStatus($status){
		$this->status = $status;
		if($status["filename"])  $this->fileName = $status["filename"];
	}
	
	function GetStatus(){
		return $this->status;
	}
	function CreatePartFile($basefilename){
		if(!function_exists("ch")){
			function ch($str,$num){
				return $str.($num+1);
			}
		}
		$index = strrpos($basefilename, ".");
		$filename = substr($basefilename,0,$index);
		$ext = substr($basefilename,$index);
		$num = 1;
		$newfile = preg_replace("~(\S+_)(\d+)$~iseU","ch('$1',$2)", $filename);
		if($newfile == $filename){
			$newfile .= "_1";
		}
		$newfile .= $ext;
		if(file_exists($newfile)) $this->CreatePartFile($newfile);
		return $newfile;
	}
	/**
	 * 将数据保以csv文件格式保存
	 * @param Array $data; 要求有title和value两个下标，title用于存放标题，可以为空，value用于存放具体值，不能为空  
	 * @see IDataSave::Save()
	 */
	public function Save($data) {
		
		// TODO: Auto-generated method stub
		clearstatcache();
		$filesize = filesize($this->fileName);
		if($filesize/pow(1024,2) > 10){ //如果文件大小大于10M，自动生成下一个文件
			$this->fileName = $this->CreatePartFile($this->fileName);
			$this->status["filename"] = $this->fileName;
		}
		
		$f = fopen($this->fileName,'a+');
		if(!$f){
			die(iconv("gbk","utf-8",$this->fileName));throw new Exception("打开目标文件失败，数据无法保存！");
		}
		if(empty($data["value"])) return false;
		if(isset($data["title"]) && !$filesize){
			if(!@fputcsv($f, $data["title"])){
				$this->log->PrintError("writeTitleError：".implode("|", $data["title"]));
			}
		}
		foreach ($data["value"] as $v){
			if(!@fputcsv($f, $v)){
				$this->log->PrintError("saveError：".implode("|", $v));
			}		
		}
		fclose($f);
		return true;
	}
	
}
