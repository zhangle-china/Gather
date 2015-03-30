<?php
class CNormalGather extends CGather{
	
	/* (non-PHPdoc)
	 * @see CGather::Start()
	 */
	public function Start() {
		// TODO: Auto-generated method stub
		$runtime = new CRunTime();
		if(!($pages = $this->objParse->ListUrlParse())){
			$this->objLog->PrintError("��ȡurl�б�ʧ�ܣ�");
			die();
		}
		$i = 0;
		foreach($pages as $url){
			$datalsit = array();
			$runtime->start();
			$content = $this->objParse->getUrlContent($url);
			echo "��ȡ�����б����ݣ���ʱ��".$runtime->get_format_time()."<br>";
			$runtime->start();
			$arcUrls = $this->objParse->ArcUrlParse($content);
			echo "��������URL����ʱ��".$runtime->get_format_time()."<br>";
			if(!$arcUrls){
				$this->objLog->PrintError("��ȡ����urlʧ�ܣ�");
				continue;
			}
			
			foreach($arcUrls as $acrUrl){
				$runtime->start();
				$arcContent = $this->objParse->getUrlContent($acrUrl);
				echo "ȡ���������ݣ���ʱ��".$runtime->get_format_time()."<br>";
				
				$runtime->start();
				$res =  $this->objParse->ArcContentParse($arcContent);
				echo "�������ݣ���ʱ��".$runtime->get_format_time()."<br>";
				
				if(!empty($res["title"]) && empty($datalsit["title"])) $datalsit["title"] = $res["title"];
				$datalsit["value"][] = $res["value"];
				ob_flush();
				flush();
			}
			die();
			$runtime->start();
			$this->objLog->PrintNormal("�ɹ�����".$i++."ҳ");
			$this->objDataSave->Save($datalsit);
			echo "�������ݣ���ʱ��".$runtime->get_format_time()."<br>";
			die();
			
		}
		
		echo date("Y-m-d H:i:s",time());
	}

}