<?php
class CShangDunParse extends CParse{
	function __construct($startPage=0,$endPage=88437){
		parent::__construct($startPage, $endPage);
	}
	/* (non-PHPdoc)
	 * @see CParse::getUrlContent()
	 */
	public function getUrlContent($url) {
		// TODO: Auto-generated method stub
		$url = explode("?", $url);
		if(isset($url[1]) && $url[1]) $param = $url[1];
		$url = $url[0];
		
		$option = array();
		$option[CURLOPT_COOKIE] = "CNZZDATA5381036=cnzz_eid%3D1046433570-1426838869-%26ntime%3D1427152231; ASPSESSIONIDSCTTSCBC=LBJCFELDHIIDFCMMLKCJGOEC; ASPSESSIONIDQCSQSDAC=BGLCFELDDFLBHHIPHFNKCDOJ; ASPSESSIONIDSCTSQCBC=POKCFELDCGDDCCBGIHEAANAM; ASPSESSIONIDQATRRBCC=NLKCFELDONHKGKOHNEFKGGNL; ASPSESSIONIDSATSSCAD=PJDOGFMDKLBIGNMJCKJFJIAP; ASPSESSIONIDQARSRACC=PJHBOFAAEEHFELOGEBEJIEHO; ASPSESSIONIDSCQTRACD=JHLLKOCABBONIONBMLHKEGNF";
		$option[CURLOPT_ENCODING] = "gzip, deflate";
		$option[CURLOPT_REFERER] = "http://www.shangdun.org/exploit/";
		$option[CURLOPT_HTTPHEADER] = array("Content-Type: application/x-www-form-urlencoded",
				"Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8",
				"Accept-Language: zh-CN,zh;q=0.8,en-US;q=0.5,en;q=0.3",
				"Connection: keep-alive",
		);
		$option[CURLOPT_POST] = 1;
 		$option[CURLOPT_POSTFIELDS] = $param;
// 		$option[CURLOPT_POSTFIELDS] = "KTcx=%C7%EB%CA%E4%C8%EB%C4%FA%B5%C4%B2%E9%D1%AF%C4%DA%C8%DD&Md3=%C9%EA%C7%EB%C8%CB&PG3=2&sTypeSM2=%C9%CC%B1%EA%D2%D1%D7%A2%B2%E1&sTypeSMmark2=1&sYWsmark2=&SXSch=&jzrdSSch=&dlSch=&AreaSmSch=&HYktSch=&XZqxSch=&BDdates=1&SQdates=&GGpNums=&ZCdates=&JZdates=";		
		return $this->XCurl($url, $option);
	}

	
	/**
	 * 返回列表URL  需要调用SetParam方法，设置一个变量名为 type 的参数，其值为 商标的状态类型
	 * (non-PHPdoc)
	 * @see Parse::ListUrlParse()
	 */
	public function ListUrlParse($content=null,$sourcePath="") {
		// TODO: Auto-generated method stub
		$result = array();
		//for($i=1;$i<=88437;$i++){
		for($i=$this->startPage;$i<=$this->endPage;$i++){
			$url = "http://www.shangdun.org/exploit/";
			$post1 = "KTcx=".urlencode(iconv("utf-8", "gb2312", "请输入您的查询内容"));
			$post1 .= "&md3=".urlencode(iconv("utf-8", "gb2312","申请人"));
			$post1 .= "&PG3=$i";
			$post1 .= "&sTypeSM2=".urlencode(iconv("utf-8", "gb2312",$this->param["type"]));
			$post1 .= "&sTypeSMmark2=1";
			$post1 .= "&BDdates=1";
			$url .= "?".$post1;
			$result[] = $url;
		}
		return $result;
	} 

	/* (non-PHPdoc)
	 * @see Parse::ArcUrlParse()
	 */
	public function ArcUrlParse($content,$sourcePath="") {
		// TODO: Auto-generated method stub
		$content = iconv("gbk","utf-8",$content);
		$result = array();
		if(!preg_match('~id="jumpShow"(.+)<\/form>~isU', $content,$matchs)) return false;
	
		$contnet = $matchs[1];
		if(!preg_match_all("~(jumpPageShow\('\d+','\d+'\))~isU",$content,$matchs)) return false;
	
		foreach($matchs[1] as $jumpshow){
			$jumpshow = str_replace(array("jumpPageShow(",")"), "",$jumpshow);
		    $jumpshow = str_replace("'", "", $jumpshow);
			$param = explode(",", $jumpshow);
			$url = "http://www.shangdun.org/show/";
			$url .= "?NowIdOn=". intval($param[0]);
			$url .=  "&NowCLOn=".  intval($param[1]);
			$result[] = $url;
		}
		return $result;
	}
	
	

	/* (non-PHPdoc)
	 * @see Parse::ArcContentParse()
	 */
	public function ArcContentParse($content,$sourcePath="") {
		// TODO: Auto-generated method stub
		$result = array();
		if(!preg_match('~<div id="showDatas">(.*)</div>~isU', $content,$matchs)) return false;
		$content = $matchs[1];
		if(!preg_match_all('~<td class="(?:TBdt1|Timg1)">(.*)</td>.*<td class="(?:TBdt\d(?:\stdp)?|Timg\d)">(.*)</td>~isU', $content,$matchs)) return false;
		foreach ($matchs[1] as $key=>$kValue){
			$label = trim(strip_tags($kValue));
			if($label == "商标图像"){
				$value = "";
				if(preg_match('~<img.*src="(.*)".*>~isU',$matchs[2][$key],$match)){
					$value = $match[1];
					$title[] = "原图地址";
					$valueList[] = $value;
					/*
				 	$targetDir = dirname(dirname("__FILE__"))."/data/download/image";
				 	$Index = count(scandir($targetDir));
				 
				 	$tempDir = $targetDir."/".($Index-1);
				 	if(is_dir($tempDir)){
				 		if(count(scandir($tempDir))%3000 == 0) $tempDir = $targetDir."/".$Index;
				 	}
				 	else{
				 		$tempDir = $targetDir."/".($Index);
				 	}
				 	$targetDir = $tempDir;
				 	if(!$value = CImage::CopyImage($value,$targetDir)) $value  = "";
				 	*/
					$value = "";
				}
			}else{
				$value = strip_tags($matchs[2][$key]);
			}
			$title[] = $label;
			$valueList[] = $value;
		}
		$result = array("title"=>$title,"value"=>$valueList);
		return $result;
	}
	
	

}