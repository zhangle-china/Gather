<?php
class CDlgl_Sipo_Gov_Cn extends CParse{

	public function __construct() {
		// TODO: Auto-generated method stub
		parent::__construct(0,0);
	}

	function getUrlContent($url){
		$url = explode("?", $url);
		if(isset($url[1]) && $url[1]) $param = $url[1];
		$url = $url[0];
		
		$option = array();
		$option[CURLOPT_POST] = 1;
		$option[CURLOPT_POSTFIELDS] = $param;
		// 		$option[CURLOPT_POSTFIELDS] = "KTcx=%C7%EB%CA%E4%C8%EB%C4%FA%B5%C4%B2%E9%D1%AF%C4%DA%C8%DD&Md3=%C9%EA%C7%EB%C8%CB&PG3=2&sTypeSM2=%C9%CC%B1%EA%D2%D1%D7%A2%B2%E1&sTypeSMmark2=1&sYWsmark2=&SXSch=&jzrdSSch=&dlSch=&AreaSmSch=&HYktSch=&XZqxSch=&BDdates=1&SQdates=&GGpNums=&ZCdates=&JZdates=";
		return $this->XCurl($url, $option);
	}
	
	
	public function ListUrlParse($content = null) {
		$url = "http://dlgl.sipo.gov.cn/txnqueryAgencyOrg.do";
		$content = $this->getUrlContent($url);
		if(!$content) die("列表获取失败 $url");
		$pattner = '~<table class="indcontcrltab">.+<tbody>(.+)</tbody>~isU';
		
		if(!preg_match($pattner, $content,$match)) return false;
		$content = $match[1];
		$pattner = '~<td>.+queryAgencyInfoList\(\'(\S+)\'.+<td>(\d+)</td>~isU';
		if(!preg_match_all($pattner,$content,$match))  return false;
		$result = array();
		foreach($match[1] as $key=>$area){
			if($area == "null") continue;
			$pageCount = intval($match[2][$key]);
			for($page=1;$page<=$pageCount;$page++){
				$url = "http://dlgl.sipo.gov.cn/txnqueryAgencyOrg.do?loginIp%3Alogin_ip=192.168.102.148&select-key%3Aagencycode=&select-key%3Acnkeyword=&select-key%3AcurrentPage=".$page."&select-key%3Alocaloffice=".$area."&select-key%3Aprincipal=&select-key%3Astatus=1";
				$result[] = $url;
			}
		}
		return $result;	
	}
	/* (non-PHPdoc)
	 * @see CParse::ArcUrlParse()
	*/
	public function ArcUrlParse($content) {
		// TODO: Auto-generated method stub
		$pattner = '~<div class="indconb">(.+)</div>~isU';
		if(!preg_match($pattner,$content,$match))  return false;
		$content = $match[1];
		$pattner = "~queryInfo\((\d+),(\d+)\)~";
		if(!preg_match_all($pattner, $content,$match)) return false;
		$result = array();
		foreach($match[1] as $key=>$v){
			$url = "http://dlgl.sipo.gov.cn/txnqueryAgencyOrgInfo.do?select-key%3Aagencycode=&select-key%3AagencycodeVal=".$match[2][$key]."&select-key%3Acnkeyword=&select-key%3Aid=".$v."&select-key%3Aprincipal=&select-key%3Astatus=1";
			$result[] = $url;
		}
		return $result;
	
	}
	
	public function ArcContentParse($content){
		if(empty($content)) return false;
		$pattner = '~<div class="indcona">(.*)<\!--conb-->(.*)<\!--page-->~isU';
		if(!preg_match($pattner,$content,$match)) return false;
		$content = $match[1];
		$dlrContent = $match[2];
		$parttner = '~<span class="indconalr flleft">(.*)[\x{ff1a}]+.*</span>.* <span class="indconarl flright">(.*)</span>~isUu';
		if(!preg_match_all($parttner,$content,$match)) die("error");
		$titles = array();
		$values = array();
		foreach($match[1] as $key=>$title){
			$titles[] = $title;
			$values[] = $match[2][$key];
		}
		
		$parttner = '~<div class="indcontb">(.*)</div>~isUu';
		if(preg_match_all($parttner,$dlrContent,$match)){
			$dlrContents = $match[1];
			foreach($dlrContents as $dlrContent){
				$parttner = '~<img.+src="(.+)".+>~isUu';
				if(preg_match($parttner,$dlrContent,$match)){
					$imgs = $match[1];
				}
				$parttner = '~<b>(.+)[\x{ff1a}]+</b>(.*)</p>~isUu';
				
				if(preg_match_all($parttner,$dlrContent,$match)){
					empty($detailTitles) && $detailTitles = $match[1];
					if(!in_array("照片",$detailTitles)) $detailTitles[] = "照片";
					$tmpValues = $match[2];
					$tmpValues[] = $imgs;
					$detailValues[] = $tmpValues;
				}
			
				
			}
			$titles[] ="dlren";
			$values[] = array("title"=>$detailTitles,"value"=>$detailValues);
		}

		$result["title"] = $titles;
		$result["value"] = $values;
		return $result;
	}
	
	


}