<?php
class CNormalGather extends CGather{
	
	/* (non-PHPdoc)
	 * @see CGather::Start()
	 */
	public function Start() {
		// TODO: Auto-generated method stub
		$runtime = new CRunTime();
		if(!($pages = $this->objParse->ListUrlParse())){
			$this->objLog->PrintError("获取url列表失败！");
			die();
		}
		$i = 0;
		foreach($pages as $url){
			$datalsit = array();
			$runtime->start();
			$content = $this->objParse->getUrlContent($url);
			echo "提取文章列表内容，用时：".$runtime->get_format_time()."<br>";
			$runtime->start();
			$arcUrls = $this->objParse->ArcUrlParse($content);
			echo "解析文章URL，用时：".$runtime->get_format_time()."<br>";
			if(!$arcUrls){
				$this->objLog->PrintError("获取文章url失败！");
				continue;
			}
			
			foreach($arcUrls as $acrUrl){
				$runtime->start();
				$arcContent = $this->objParse->getUrlContent($acrUrl);
				echo "取得文章内容，用时：".$runtime->get_format_time()."<br>";
				
				$runtime->start();
				$res =  $this->objParse->ArcContentParse($arcContent);
				echo "解析数据，用时：".$runtime->get_format_time()."<br>";
				
				if(!empty($res["title"]) && empty($datalsit["title"])) $datalsit["title"] = $res["title"];
				$datalsit["value"][] = $res["value"];
				ob_flush();
				flush();
			}
			die();
			$runtime->start();
			$this->objLog->PrintNormal("成功解析".$i++."页");
			$this->objDataSave->Save($datalsit);
			echo "保存数据，用时：".$runtime->get_format_time()."<br>";
			die();
			
		}
		
		echo date("Y-m-d H:i:s",time());
	}

}