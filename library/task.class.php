<?php
/**
 * 任务类; 
 * 实现观察者接口，用于观察 采集器 状态；
 * @author zhangle
 *
 */
class CTask implements ITask, ISubject{
	private $observerLsit;
	var $tasDir;
	var $config;
	var $objLog;
	var $objDataSave;
	var $objParse;
	var $key; //任务的唯一标示;两个任务不能使用同一个Key
	/**
	 * 采集器的状态值；
	 * @var Array  // 有效建值： listIndex  记录了目标地址在目标地址列表中的索引号，用于下次启动采集其实，继续上次的采集；
	 */
	protected $status ;
	
	/**
	 * 允许连续失败的次数，如果连续出现错误次数超过此值，采集器将停止；
	 * @var int
	 */
	protected $allowErrNum = 0;
    function __construct($key,CParse $parse){
    	$this->taskDir = ROOT."/task/".$key;
    	mkdir($this->taskDir,777,true);
    	$this->key = $key;
    	$this->config = new CConfig("task/$key/config.php");
    	$this->objLog = new CLog($this->taskDir);
    	$this->objLog->SetOutputType(LogOutputType::FILE);
		$this->status = $this->config ->Params();
		$this->status || $this->status = array();
		$this->SetParse($parse);
		$downloadDir = $this->taskDir."/download";
		mkdir($downloadDir,"777",true);
		$this->objParse->SetDownloadDir($downloadDir);
		$this->objParse->SetStatus($this->status); //将解析器设置为上次采集结束时的状态；
	}
	
	function SetStatus($status){
		$this->status = array_merge($this->status,$status);
	}
	
	function GetSataus(){
		return $this->status;
	}
	
	/**
	 * 设置容错数量
	 * @param  $num
	 * @return boolean
	 */
	function SetAllowErrNum($num){
		if(!is_numeric($num)) return false;
		$this->allowErrNum = $num;
	}
	
	
	function SetDataSave(IDataSave $dataSave ){
		$dataSave ->SetStatus($this->status);
		$this->objDataSave = $dataSave;
	}
	
	function SetParse(CParse $parse){
		$this->objParse = $parse;
		$cache = $this->taskDir."/cache/";
		mkdir($cache,777,true);
		$this->objParse->SetCacheFile($cache);
		$this->objParse->SetStatus($this->status);
	}
	
	/**
	 * 启动任务；
	 */
	function Run(IDataSave $dataSave = null){
		$dataSave && $this->SetDataSave($dataSave);

		try{
			ob_start();
			if(DEBUG)   echo "<br>开始获取采集地址<br>";
			$pages = $this->objParse->ListUrlParse();
			
			$logMsg = ob_get_contents();
			ob_end_clean();
			if($logMsg){
				$this->objLog->PrintError(logMsg);
			}
			
			$this->status = array_merge($this->status,$this->objParse->GetStatus());
			$this->config->WriteConfig($this->status);
		}
		
		catch(Exception $e){
			$this->objLog->PrintError("获取url列表失败！原因：".$e->getMessage());
			die($e->getMessage());
		}
		
		$this->status["listIndex"] || $this->status["listIndex"] = 0;
		$i = 0;
		foreach($pages as $pageKey => $url){
			if($pageKey < $this->status["listIndex"]) continue; //上次采集到的位置；断点续传；
			try{
				$arcUrls = $this->objParse->ArcUrlParse("",$url);
			}
			catch (Exception $e){
				$i++;
				$msg = "目标地址索引：".$this->status['listindex']."；解析文章地址出错； 错误原因：".$e->getMessage()."目标地址：".$url;
				$this->objLog->PrintError($msg);
				if($i>$this->allowErrNum) die($msg);
				$this->status["listIndex"]++;
				$this->config->WriteConfig($this->status); //存储状态
				continue;
			}
			$num = 0;
			settype($arcUrls, "array");
			$this->status["arcListIndex"] || $this->status["arcListIndex"] = 0;
			foreach($arcUrls as $arcKey=>$acrUrl){ 
				if($arcKey < $this->status["arcListIndex"]) continue; //上次采集到的位置；断点续传；
				$datalist = array();   
				try{
					$res =  $this->objParse->ArcContentParse("",$acrUrl);
				}
				catch (Exception $e){
					$msg = "文章地址索引：".$this->status["arcListIndex"] ." 错误：".$e->getMessage().";  arcurl:".$acrUrl;
					$this->objLog->PrintError($msg);
					$num++;
					if($num > $this->allowErrNum) die($msg);
					$this->status["arcListIndex"]++;
					$this->config->WriteConfig($this->status); //存储状态
					continue;
				}
				$num = 0;
				if(!empty($res["title"]) && empty($datalist["title"])) $datalist["title"] = $res["title"];
				if($this->objParse->GetContentPageStyle() == "ARTICLE"){
					$datalist["value"][] = $res["value"];
				}else{
					foreach($res["value"] as $v){
						$datalist["value"][] = $v;
					}
				}
				try{
					try{
						$id = $this->objDataSave->Save($datalist);  //每一个文章页采集到的数据存储一次
					}catch(Exception $e){
						die($e->getMessage()."arcurl:".$acrUrl);
					}
					if($id && intval($id)){
						$this->status["masterid"] = $id;  //主从数据采集时，主数据几记录的ID;
					}
					$this->status["sourceurl"] = $acrUrl;
					$this->status = array_merge($this->status,$this->objDataSave->GetStatus());
					$this->status["arcListIndex"]++;
					$this->config->WriteConfig($this->status); //存储状态
					$this->notifiy(); //发送状态变化通知；
				}
				catch(Exception $e){
					$this->objLog->PrintError($e->getMessage()." 第 ".($this->status["listIndex"]+1)."页 第 ".($this->status["arcListIndex"] + 1)."篇文章");
				}
			}
			$this->status["arcListIndex"] = 0;
			$this->status["listIndex"] ++;	
			$this->config->WriteConfig($this->status);
		}
		
		/** 采集完毕，恢复初始状态！ */
		$this->status["arcListIndex"] = 0 ;
		$this->status["listIndex"]  = 0;
		$this->status["sourceurl"] = "";
		$this->status["arcListIndex"] = "";
		$this->objParse->InitStatus();
		$this->status = array_merge($this->status,$this->objParse->GetStatus());
		$this->config->WriteConfig($this->status); 
	}
	
	public function update($status){
		$this->param = array_merge($this->param,$status);
		$this->config->WriteConfig($this->param);
	}
	
	function GetParse(){
		return $this->objParse;
	}
	
	
	function attach(IObserver $observer){
		if($observer instanceof IObserver)	$this->observerLsit[] = $observer;
	}
	
	function deAttch(IObserver $observer){
		foreach($this->observerLsit as $key=>$o){
			if($o == $observer) unset($this->observerLsit[$key]);
		}
	}
	
	/**
	 * 状态更新通知；没采集一页，更新一次采集状态
	 * @see ISubject::notifiy()
	 */
	function notifiy(){
		foreach($this->observerLsit as $observer){
			$observer->update($this->status);
		}
	}

		
}