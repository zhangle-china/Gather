<?php
class CConfig{
	private $params = array();
	private $configFile;
	
	function __construct($configFile = ""){
		if(empty($configFile)){
			$this->configFile = $this->NewFile();
		}
		$this->configFile = $configFile;
		if(!($dir = dir($this->configFile))){
			mkdir($dir,777,true);
		}
	}
	function FileName(){
		return $this->configFile;
	}
    function Clear(){
    	unlink($this->$configFile);
    }
	private function NewFile(){
		$dir = ROOT."/data/";
		return $dir."config.php";
	}
	
	function SetParams($params){
		$this->params = $params;
	}
	function Params(){
		if(empty($this->params)) $this->params = $this->ReadConfig();
		return $this->params;
	}
	function ReadConfig(){
		if(!file_exists($this->configFile)) return false;
		$content = file_get_contents($this->configFile);
		return unserialize($content);
	}
	
	function WriteConfig($params){
		
		if(empty($params)) return false;
		$h = fopen($this->configFile, "w");
		if(!$h) return false;
		fwrite($h, serialize($params));
		fclose($h);
		return true;
	}
}