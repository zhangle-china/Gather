<?php
/**
 * 商标网代理机构解析类
 * 数据源：http://www.ctmo.gov.cn/sbdl/
 * 
 * @author Administrator
 *
 */
Class CSbDljgParse extends CParse{
	var $sourcePath ;
	var $pagesize;
	function __construct($sourcePath,$startPage, $endPage,$pagesize){
		$this->sourcePath = $sourcePath;
		$this->pagesize = $pagesize;
		parent::__construct($startPage, $endPage);
		$this->contentPageStyle = "LIST";
		
	}
	
	
	public function getUrlContent($url) {
		// TODO Auto-generated method stub
		$urlArray = explode("?", $url);
		$url = $urlArray[0];
		$data = isset($urlArray[1])?$urlArray[1]:"";
		
		$options = array();
		$options[CURLOPT_POST] = true;
		$options[CURLOPT_POSTFIELDS] = $data;
		return $this->XCurl($url, $options);
	
	}
	
	function ListUrlParse($content=null,$sourcePath=""){
		$result = array();
		for ($i=$this->startPage; $i<=$this->endPage; $i++){
			$result[] = $this->sourcePath."?gopage=".($i-1)."&pagenum=$i&";
		}
		return $result;
	}
	

	public function ArcUrlParse($content, $sourcePath = "") {
		// TODO Auto-generated method stub
		return array(0=>$sourcePath);
	}
	
	

	public function ArcContentParse($content, $sourcePath = "") {
		// TODO Auto-generated method stub
		if(!preg_match('~<table class="import_hpb_list".*>(.*)</table>~isU',$content,$match)) return false;
		$content = $match[1];
		if(!preg_match_all('~<tr>\s*<td.*>\s*(\S*)\s*</td>\s*<td.*>\s*(\S*)\s*</td>\s*<td.*>\s*(\S*)\s*</td>\s*<td.*>\s*(\S*)\s*</td>\s*<td.*>\s*(\S*)\s*</td>\s*</tr>~isU',$content,$match)) return false;
		$title = array($match[1][0],$match[2][0],$match[3][0],$match[4][0],$match[5][0]);
		$index = 1;
		$count = count($match[1]);
		$values = array();
		for($index;$index<$count;$index++){
			$values[] = array($match[1][$index],$match[2][$index],$match[3][$index],$match[4][$index],$match[5][$index]);
		}
		$result["title"] = $title;
		$result["value"] =  $values;
		return $result;
	}




	
	
	
}