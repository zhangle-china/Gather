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
			$this->objLog->PrintError("��ȡurl�б�ʧ�ܣ�");
			die();
		}
		$this->status["startpage"] = $this->objParse->getStartPageNum();
		
		$this->status["endpage"] = $this->objParse->getEndPageNum();
		$this->status["pageindex"]  = $i = 0;
	
		foreach($pages as $url){
			echo $this->status["startpage"] .",";
			if($this->status["startpage"] % 30==0) echo "<br>";
			ob_flush();
			flush();
			
			$datalsit = array();
			$content = $this->objParse->getUrlContent($url);
			$arcUrls = $this->objParse->ArcUrlParse($content);
			if(!$arcUrls){
				$this->objLog->PrintError("��ȡ����urlʧ�ܣ�listUrl:".$url);
				continue;
			}
			
			foreach($arcUrls as $acrUrl){
				$arcContent = $this->objParse->getUrlContent($acrUrl);
				if(!$arcContent){
					$this->objLog->PrintError("��ȡ��������ʧ�ܣ�arcurl:".$url);
					continue;
				}
				$res =  $this->objParse->ArcContentParse($arcContent);
				if(!$res){
					$this->objLog->PrintError("������������ʧ�ܣ�arcurl:".$url);
					continue;
				}
				if(!empty($res["title"]) && empty($datalsit["title"])) $datalsit["title"] = $res["title"];
				$datalsit["value"][] = $res["value"];
			}		
			$this->objLog->PrintNormal("�ɹ���������".$this->status["startpage"]."ҳ");
			$this->objDataSave->Save($datalsit);
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