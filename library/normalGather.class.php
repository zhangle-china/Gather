<?php
class CNormalGather extends CGather{
	
	/* (non-PHPdoc)
	 * @see CGather::Start()
	 */
	public function Start() {
		// TODO: Auto-generated method stub
		if(!($pages = $this->objParse->ListUrlParse())){
			$this->objLog->PrintError("��ȡurl�б�ʧ�ܣ�");
			die();
		}
		foreach($pages as $url){
			$content = $this->objParse->getUrlContent($url);
			$arcUrls = $this->objParse->ArcUrlParse($content);
			if(!$arcUrls){
				$this->objLog->PrintError("��ȡ����urlʧ�ܣ�");
				continue;
				
			}
			
			foreach($arcUrls as $acrUrl){
				$arcContent = $this->objParse->getUrlContent($acrUrl);
				$this->dataList[] = $this->objParse->ArcContentParse($arcContent);
				print_r($this->dataList);
				exit;
			}
		}
		
		$this->objDataSave->Save($this->dataList);
	}

}