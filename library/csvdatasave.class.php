<?php
class CCsvDataSave implements IDataSave{
	private $fileName;
	private $log;
	function __construct($fileName = "",CLog $log){
		if(!empty($fileName)){
			$this->fileName = $fileName;
		}
		else{
			$this->fileName = dirname(dirname(__FILE__))."/data/data-".time().rand(1, 100000).".csv";
		}
		$dir = dirname($this->fileName);
		if(!is_dir($dir)) mkdir($dir,777,true);
		
		$this->log = $log;
	}
	
	function setFileName($fileName){
		$this->fileName = $fileName;
	}
	
	function GetDataFile(){
		return $this->fileName;
	}
	function CreatePartFile($basefilename){
		function ch($str,$num){
			return $str.($num+1);
		}
		$index = strrpos($basefilename, ".");
		$filename = substr($basefilename,0,$index);
		$ext = substr($basefilename,$index);
		$num = 1;
		$newfile = preg_replace("~(\S+_)(\d+)$~iseU","ch('$1',$2)", $filename);
		;
		if($newfile == $filename){
			$newfile .= "_1";
		}
		$newfile .= $ext;
		if(file_exists($newfile)) $this->CreatePartFile($newfile);
		return $newfile;
	}
	/**
	 * �����ݱ���csv�ļ���ʽ����
	 * @param Array $data; Ҫ����title��value�����±꣬title���ڴ�ű��⣬����Ϊ�գ�value���ڴ�ž���ֵ������Ϊ��  
	 * @see IDataSave::Save()
	 */
	public function Save($data) {
		// TODO: Auto-generated method stub
		if(filesize($this->fileName)/pow(1024,2) > 5){ //����ļ���С����5M���Զ�������һ���ļ�
			$this->fileName = $this->CreatePartFile($this->fileName);
		}
		$f = fopen($this->fileName,'a+');
		if(empty($data["value"])) return false;
		if(isset($data["title"]) && !@filesize($this->fileName)){
			if(!@fputcsv($f, $data["title"])){
				$this->log->PrintError("writeTitleError��".implode("|", $v));
			}
		}
		foreach ($data["value"] as $v){
			if(!@fputcsv($f, $v)){
				$this->log->PrintError("saveError��".implode("|", $v));
			}		
		}
		fclose($f);
		return true;
	}
	
}