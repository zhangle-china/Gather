<?php
class CLog{
	protected $outputType;
	protected $logDir;
	function __construct($logDir){
		$this->outputType = LogOutputType::SCREEN;
		if($logDir !== "" && is_dir($logDir))  
			$this->logDir = rtrim($logDir,"/")."/"; 
		else
			throw ("错误的日志目录");
	}
	
	/**
	 * �����������
	 * @param const $value
	 * @return null
	 */
	function SetOutputType($value){
		if(!in_array($value,LogOutputType::TypeList())) return false;
		$this->outputType = $value;
	}
	/**
	 * ȡ�õ�ǰ����־�������
	 * @return Ambigous <const, number>
	 */
	function GetOutputType(){
		return $this->outputType;
	}
	function PrintError($msg){
		$this->PrintLog($msg,LogType::ERROR);
	}
	function PrintNormal($msg){
		$this->PrintLog($msg,LogType::NORMAL);
	}
	function PrintLog($msg,$type=null){
		
		switch ($this->outputType){
			case LogOutputType:: SCREEN :
				$this->OutputScreen($msg,$type);
			break;
			case LogOutputType::FILE:
				$this->OutputFile($msg,$type);
			break;
		}
	}
	
	private function OutputFile($msg,$type= LogType::NORMAL){
		$fileName = $this->logDir."log-".date("Ymd",time()).".log";
		$hf = fopen($fileName, "a+");
		if(!$hf) return false;
		fwrite($hf, "[".LogType::ToString($type)."]".date("Y-m-d H:i:s -> ").$msg."\r\n");
		fclose($hf);
	}
	
	private function OutputScreen($msg,$type=LogType::NORMAL){
		if(!in_array($type,LogType::TypeList())) $type = LogType::OTHER;
		switch ($type){
			case LogType::ERROR:
				$color = "#F70404";
			break;
			case LogType::WARNING:
				$color = "#FCF802";
			break;
			case LogType::OTHER:
				$color = "#1C08D3";
			break;
			default:
				$color = "#000000";
		}
		echo "<p style='color:$color'>$msg</p>";
		ob_flush();
		flush();
	}
}


class LogOutputType{
	const  SCREEN = 10010;
	const  FILE = 10011;
	
	/**
	 * ���������б�
	 * @return array
	 */
	static function TypeList(){
		return array(self::SCREEN,self::FILE);
	}
}
class LogType{
	const ERROR = 20010;
	const NORMAL = 20011;
	const WARNING = 20012;
	const OTHER = 20013;
	private static $StringList = array(self::ERROR=>"error",self::NORMAL=>"normal",self::WARNING=>"warning",self::OTHER=>"other");
	/**
	 * ���������б�
	 * @return array
	 */
	static function TypeList(){
		return array_keys(self::$StringList);
// 		return array(self::ERROR,self::NORMAL,self::WARNING,self::OTHER);
	}
	
	/**
	 * ������־���͵��ַ��ʶ��
	 * @param const $type ��־����
	 * @return multitype:string |boolean �Żض�Ӧ���͵ı�ʶ���紫������Ͳ��Ϸ�������false;
	 */
	static function ToString($type){
		if(isset(self::$StringList[$type])) return self::$StringList[$type];
		return false; 
	}
}
