<?php
class CCsvDataSave implements IDataSave{
	private $fileName;
	private $log;
	function __construct($fileName = "",CLog $log){
		if(!empty($fileName)){
			$this->fileName = $fileName;
		}
		else{
			$this->fileName = dirname(dirname(__FILE__))."/data-".time().rand(1, 100000).".csv";
		}
		$dir = dirname($this->fileName);
		if(!is_dir($dir)) mkdir($dir,777,true);
		
		$this->log = $log;
	}
	
	function setFileName($fileName){
		$this->fileName = $fileName;
	}
	
	/**
	 * 将数据保以csv文件格式保存
	 * @param Array $data; 要求有title和value两个下标，title用于存放标题，可以为空，value用于存放具体值，不能为空  
	 * @see IDataSave::Save()
	 */
	public function Save($data) {
		// TODO: Auto-generated method stub
		$f = fopen($this->fileName,'a+');
		if(empty($data["value"])) return false;
		if(isset($data["title"]) && !@filesize($this->fileName)){
			if(!@fputcsv($f, $data["title"])){
				$this->log->PrintError("writeTitleError：".implode("|", $v));
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