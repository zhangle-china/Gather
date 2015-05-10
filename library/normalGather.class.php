<?php
class CNormalGather extends CGather implements ISubject{
	private $observerLsit;
	private $status;
	/* (non-PHPdoc)
	 * @see CGather::Start()
	 */
	public function Start() {
		// TODO: Auto-generated method stub
		if(!($pages = $this->objParse->ListUrlParse())){
			$this->objLog->PrintError("获取url列表失败！");
			die();
		}
		$this->status["startpage"] = $this->objParse->getStartPageNum();
		$this->status["endpage"] = $this->objParse->getEndPageNum();
		$this->status["pageindex"]  = $i = 0;
	
		foreach($pages as $url){
			$this->status["pageindex"]++;
			$datalsit = array();
			$content = $this->objParse->getUrlContent($url);
			$arcUrls = $this->objParse->ArcUrlParse($content,$url);
			if(!$arcUrls){
				$this->objLog->PrintError("获取文章url失败！listUrl:".$url);
				continue;
			}
			
			foreach($arcUrls as $acrUrl){
				$arcContent = $this->objParse->getUrlContent($acrUrl);
				if(!$arcContent){
					$this->objLog->PrintError("获取文章内容失败！arcurl:".$acrUrl);
					continue;
				}
				$res =  $this->objParse->ArcContentParse($arcContent,$acrUrl);
				if(!$res){
					$this->objLog->PrintError("解析文章内容失败！arcurl:".$acrUrl);
					continue;
				}
				if(!empty($res["title"]) && empty($datalsit["title"])) $datalsit["title"] = $res["title"];
				$datalsit["value"][] = $res["value"];
			}		
			$this->objDataSave->Save($datalsit);
			$this->objLog->PrintNormal("成功采集到第".$this->status["startpage"]."页");
			$this->status["startpage"] = $this->status["startpage"] +1;
			$this->status["datafile"] = $this->objDataSave->GetDataFile();
			$this->notifiy();
		}
	}
	
	function attach($observer){
		$this->observerLsit[] = $observer;
	}

	function deAttch($observer){
		foreach($this->observerLsit as $key=>$o){
			if($o == $observer) unset($this->observerLsit[$key]);
		}
	}
	
	function notifiy(){
		foreach($this->observerLsit as $observer){
			$observer->update($this->status);
		}
	}
}