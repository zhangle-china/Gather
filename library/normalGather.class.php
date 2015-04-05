<?php
class CNormalGather extends CGather{
	
	/* (non-PHPdoc)
	 * @see CGather::Start()
	 */
	public function Start() {
		// TODO: Auto-generated method stub
		if(!($pages = $this->objParse->ListUrlParse())){
			$this->objLog->PrintError("获取url列表失败！");
			die();
		}
		$i = 0;
		foreach($pages as $url){
			$i++;
			echo "$i,";
			if($i%30==0) echo "<br>";
			ob_flush();
			flush();
			$datalsit = array();
			$content = $this->objParse->getUrlContent($url);
			$arcUrls = $this->objParse->ArcUrlParse($content);
			if(!$arcUrls){
				$this->objLog->PrintError("获取文章url失败！listUrl:".$url);
				continue;
			}
			
			foreach($arcUrls as $acrUrl){
				$arcContent = $this->objParse->getUrlContent($acrUrl);
				if(!$arcContent){
					$this->objLog->PrintError("获取文章内容失败！arcurl:".$url);
					continue;
				}
				$res =  $this->objParse->ArcContentParse($arcContent);
				if(!$res){
					$this->objLog->PrintError("解析文章内容失败！arcurl:".$url);
					continue;
				}
				if(!empty($res["title"]) && empty($datalsit["title"])) $datalsit["title"] = $res["title"];
				$datalsit["value"][] = $res["value"];
			}
			$this->objLog->PrintNormal("成功解析".$i."页");
			$this->objDataSave->Save($datalsit);
		}
		
	}

}