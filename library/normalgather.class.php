<?php
class CNormalGather extends CGather implements ISubject{
	private $observerLsit;

	
	
	/* (non-PHPdoc)
	 * @see CGather::Start()
	 */
	public function Start() {
		// TODO: Auto-generated method stub
		try{
			$pages = $this->objParse->ListUrlParse();
		}
		catch(Exception $e){
			$this->objLog->PrintError("获取url列表失败！原因：".$e->getMessage());
			die();
		}
		$this->status["listIndex"] || $this->status["listIndex"] = 0;
		$i = 0;
		foreach($pages as $pageKey => $url){
			if($this->status["listIndex"]>0 && $pageKey <= $this->status["listIndex"]) continue; //上次采集到的位置；断点续传；
			try{
				$arcUrls = $this->objParse->ArcUrlParse("",$url);
			}
			catch (Exception $e){
				$i++;
				$msg = "目标地址索引：".$this->status['listindex']."；解析文章地址出错； 错误原因：".$e->getMessage()."目标地址：".$url;
				$this->objLog->PrintError($msg);
				if($i>$this->allowErrNum) die($msg);
				continue;
			}
			$num = 0;
			settype($arcUrls, "array");
			$this->status["arcListIndex"] || $this->status["arcListIndex"] = 0;
			foreach($arcUrls as $arcKey=>$acrUrl){  
				if($this->status["arcListIndex"] > 0 && $arcKey <= $this->status["arcListIndex"]) continue; //上次采集到的位置；断点续传；
				$datalist = array();   
				try{
					$res =  $this->objParse->ArcContentParse("",$acrUrl);
				}
				catch (Exception $e){
					$msg = "文章地址索引：".$this->status["arcListIndex"] ." 错误：".$e->getMessage().";  arcurl:".$acrUrl;
					$this->objLog->PrintError($msg);
					$num++;
					if($num > $this->allowErrNum) die($msg);
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
						die($e->getMessage());
					}
					if($id && intval($id)){
						$this->status["masterid"] = $id;  //主从数据采集时，主数据几记录的ID;
					}
					$this->status["sourceurl"] = $acrUrl;
					$this->status = array_merge($this->status,$this->objDataSave->GetStatus());
					$this->status["arcListIndex"]++;
					$this->notifiy();
				}
				catch(Exception $e){
					$this->objLog->PrintError($e->getMessage()." 第 ".$this->status["startpage"]."页");
				}
				
			}
			$this->status["arcListIndex"] = 0;
			$this->status["listIndex"] ++;	
		}
	}
	
	protected function FilterStatus($status){
		$result = array();
		is_numeric($status["arcListIndex"]) && $result["arcListIndex"] = $status["arcListIndex"];
		$result = array_merge($result,parent::FilterStatus($status));
		return $result;
	}
	
	function attach($observer){
		$this->observerLsit[] = $observer;
	}

	function deAttch($observer){
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
