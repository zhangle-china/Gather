<?php
class CCsvDataSave implements IDataSave{
	private $fileName;
	function __construct($fileName = ""){
		if(!empty($fileName)){
			$this->fileName = $fileName;
		}
		else{
			$this->fileName = dirname(dirname(__FILE__))."/data-".time().rand(1, 100000).".csv";
		}
		$dir = dirname($this->fileName);
		if(!is_dir($dir)) mkdir($dir,777,true);
	}
	
	function setFileName($fileName){
		$this->fileName = $fileName;
	}
	
	/**
	 * �����ݱ���csv�ļ���ʽ����
	 * @param Array $data; Ҫ����title��value�����±꣬title���ڴ�ű��⣬����Ϊ�գ�value���ڴ�ž���ֵ������Ϊ��  
	 * @see IDataSave::Save()
	 */
	public function Save($data) {
		// TODO: Auto-generated method stub
		$f = fopen($this->fileName,'a+');
		if(empty($data["value"])) return false;
		if(isset($data["title"]) && !@filesize($this->fileName)){
			fputcsv($f, $data["title"]);
		}
		foreach ($data["value"] as $v){
				fputcsv($f, $v);		
		}
		fclose($f);
		return true;
	}
	
}