<?php
class CDlgl_Sipo_Gov_Cn extends CParse{
	private $_area;
	private $_curArea;
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
	
	public function ListUrlParse($content = null,$sourcePath="") {
		$url = "http://dlgl.sipo.gov.cn/txnqueryAgencyOrg.do";
		$content = $this->getUrlContent($url);
		if(!$content) die("列表获取失败 $url");
		$pattner = '~<table class="indcontcrltab">.+<tbody>(.+)</tbody>~isU';
		
		if(!preg_match($pattner, $content,$match)) return false;
		$content = $match[1];
		$pattner = '~<td>.+queryAgencyInfoList\(\'(\S+)\'.+>(\S+)</a>.*<td>(\d+)</td>~isU';
		if(!preg_match_all($pattner,$content,$match))  return false;
		$result = array();
		foreach($match[1] as $key=>$area){
			if($area == "null") continue;
			$pageCount = intval($match[3][$key]);
			$this->_area[$area] = $match[2][$key];
			for($page=1;$page<=$pageCount;$page++){
				$loginIP = sprintf("%d.%d.%d.%d",rand(10,254),rand(10,254),rand(10,254),rand(10,254));
				$url = "http://dlgl.sipo.gov.cn/txnqueryAgencyOrg.do?loginIp%3Alogin_ip=".$loginIP."&select-key%3Aagencycode=&select-key%3Acnkeyword=&select-key%3AcurrentPage=".$page."&select-key%3Alocaloffice=".$area."&select-key%3Aprincipal=&select-key%3Astatus=1";
				$result[] = $url;
			}
		}

		return $result;	
	}
	/* (non-PHPdoc)
	 * @see CParse::ArcUrlParse()
	*/
	public function ArcUrlParse($content,$sourcePath="") {
		// TODO: Auto-generated method stub
		
		//提取地区信息
		$pattner = "~Alocaloffice=(.+)&~iU";
		if(preg_match($pattner,$sourcePath,$match)){
			$area = $match[1];
			$area = $this->_area[$area];
		}
		$area || $area="";
		$this->_curArea = $area;
		
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
	
	public function ArcContentParse($content,$sourcePath=""){
		if(empty($content)) return false;
		
		$pattner = '~<div class="indcona">(.*)<\!--conb-->(.*)<\!--page-->~isU';
		if(!preg_match($pattner,$content,$match)) return false;
		$content = $match[1];
		$dlrContent = $match[2];
		
		$titles = array("机构名称","机构代码","机构状态","成立日期","联系电话","电子邮箱","机构地址","传真","负责人","合伙人/股东","年度报告",);
		$values = array();
		
		foreach($titles as $key=>$title){
			$values[$key] = $this->parseData($title, $content);
		}
		$titles[] = "地区";
		$values[] = $this->_curArea ;
		
	
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
	
	
	private function parseData($title,$content){
		
		$pattner = '~<span class="indconalr flleft">'.$title.'.* <span class="indconarl flright">(.*)</span>~isU';
		preg_match($pattner,$content,$match);
		$value = $match[1] ;
		$value || $value = "";
		return $value;
	}
	
	
	private function  utf8_unicode($name){  
        $name = iconv('UTF-8', 'UCS-2', $name);  
        $len  = strlen($name);  
        $str  = '';  
        for ($i = 0; $i < $len - 1; $i = $i + 2){  
            $c  = $name[$i];  
            $c2 = $name[$i + 1];  
            if (ord($c) > 0){   //两个字节的文字  
                $str .= '\u'.base_convert(ord($c), 10, 16).str_pad(base_convert(ord($c2), 10, 16), 2, 0, STR_PAD_LEFT);  
                //$str .= base_convert(ord($c), 10, 16).str_pad(base_convert(ord($c2), 10, 16), 2, 0, STR_PAD_LEFT);  
            } else {  
                $str .= '\u'.str_pad(base_convert(ord($c2), 10, 16), 4, 0, STR_PAD_LEFT);  
                //$str .= str_pad(base_convert(ord($c2), 10, 16), 4, 0, STR_PAD_LEFT);  
            }  
        }  
        $str = strtoupper($str);//转换为大写  
        return $str;  
    }  
  
	


}