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
		$this->status["param"] = $this->objParse->GetParam();
		$this->status["pageindex"]  = $i = 0;
	
		foreach($pages as $url){
			$curPage = $this->status["startpage"];
			$this->status["startpage"] = $this->status["startpage"] +1;
			$datalsit = array();
			$i = 0;
			while(!($content = $this->objParse->getUrlContent($url))){
				$i++;
				if($i>50) die("服务其无法访问");			
			}
			$arcUrls = $this->objParse->ArcUrlParse($content,$url);
			if(!$arcUrls){
				die("服务器无法打开 或 页面结构发生变化导致无法解析出文章地址！");
				$this->objLog->PrintError("页码：$curPage 获取文章url失败！listUrl:".$url);
				continue;
			}
			$num = 0;
			foreach($arcUrls as $acrUrl){
				$i = 0;
				while(!($arcContent = $this->objParse->getUrlContent($acrUrl))){
					$i++;
					if($i>50) die("服务其无法访问");
				}
				if(!$arcContent){
					$this->objLog->PrintError("页码：$curPage 获取文章内容失败！arcurl:".$acrUrl);
					continue;
				}
				
				$res =  $this->objParse->ArcContentParse($arcContent,$acrUrl);
				if(!$res){
					$this->objLog->PrintError("页码：$curPage 解析文章内容失败！arcurl:".$acrUrl);
					$num++;
					if($num > 50) die("服务器无法打开 或 页面结构发生变化导致无法解析出 需要的数据！");
					continue;
				}
				$num = 0;
				if(!empty($res["title"]) && empty($datalsit["title"])) $datalsit["title"] = $res["title"];
				$datalsit["value"][] = $res["value"];
			}	
			try{	
				$this->objDataSave->Save($datalsit);
				$this->status["successpage"][] = $this->status["startpage"];
				$this->status["datafile"] = $this->objDataSave->GetDataFile();
				$this->notifiy();
			}
			catch(Exception $e){
				$this->objLog->PrintError($e->getMessage()." 第 ".$this->status["startpage"]."页");
			}
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
