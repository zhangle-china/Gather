<?php
class CShangDunParse extends CParse{
	protected $startPage;
	protected $endPage;
	
	
	function __construct($startPage=0,$endPage=88437){
		parent::__construct();
		$this->startPage = $startPage;
		$this->endPage = $endPage;
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
		$option[CURLOPT_REFERER] = "http://sa.shangdun.org/exploit/";
		$option[CURLOPT_HTTPHEADER] = array("Content-Type: application/x-www-form-urlencoded",
				"Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8",
				"Accept-Language: zh-CN,zh;q=0.8,en-US;q=0.5,en;q=0.3",
				"Connection: keep-alive",
				'If-None-Match	:"91e25536-b8f-4cf861d2e8d40"',
				'User-Agent:Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:41.0) Gecko/20100101 Firefox/41.0'
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
			$url = "http://sa.shangdun.org/exploit/";
			$post1 = "KTcx=".urlencode(iconv("utf-8", "gb2312", "请输入您的查询内容"));
			$post1 .= "&md3=".urlencode(iconv("utf-8", "gb2312","申请人"));
			$post1 .= "&PG3=$i";
			$post1 .= "&sTypeSM2=".urlencode(iconv("utf-8", "gb2312",$this->status["type"]));
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
		//die($content);
		//$content = iconv("gbk","utf-8",$content);
		//die($content);
		if(!$content && $sourcePath) $content = $this->getUrlContent($sourcePath);
		if(!$content) throw new Exception("获取远程内容失败！");
		if(!preg_match("~<table id=\"SearMoreList\">(.*)</table>~isU", $content,$match)) throw new Exception("解析列表地址失败！");
		$content = $match[1];
		$result = array();
		if(!preg_match_all("~(jumpPageShow\('[a-zA-z]*\d+','\d+'\))~isU",$content,$matchs))  throw new Exception("提取列表地址失败！");
		foreach($matchs[1] as $jumpshow){
			$jumpshow = str_replace(array("jumpPageShow(",")"), "",$jumpshow);
		 	$jumpshow = str_replace("'", "", $jumpshow);
			$param = explode(",", $jumpshow);
			if(!$param[0] || !$param[1]){
				echo "<br>无效参数</br>";
				continue;
			}
			$url = "http://sa.shangdun.org/show/";
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
		//$content = iconv("gbk","utf-8",$content);
		//die($content);		
		$result = array();
		if(!$content && $sourcePath) $content = $this->getUrlContent($sourcePath);
		if(!$content) throw new Exception("获取远程内容失败！");
		if(!preg_match('~<div id="showDatas">(.*)</div>~isU', $content,$matchs)) throw new Exception("提取文章内容失败！");
		$content = $matchs[1];
		if(!preg_match_all('~<td class="(?:TBdt1|Timg1)">(.*)</td>.*<td class="(?:TBdt\d(?:\stdp)?|Timg\d)">(.*)</td>~isU', $content,$matchs)) throw new Exception("提取文章内容失败！");
		foreach ($matchs[1] as $key=>$kValue){
			$label = trim(strip_tags($kValue));
			$utfLable = iconv("gbk","utf-8",$label);
			if($utfLable == "商标图像"){
				$value = "";
				if(preg_match('~<img.*src="(.*)".*>~isU',$matchs[2][$key],$match)){
					$value = $match[1];
					$title[] = iconv("utf-8","gbk","原图地址");
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
				$value = str_replace(",","[-]",$value);
			}
			$title[] = $label;
			$valueList[] = $value;
		}
		if(preg_match('~<td colspan="3">.*(\d{4}\S+\d{1,2}\S+\d{1,2}).*(\d{4}\S+\d{1,2}\S+\d{1,2}).*</td>~isU', $content,$matchs)) {
			$v = iconv("gbk","utf-8",$matchs[1]);
			$v = str_replace(array("年","月"), "-", $v);
			$valueList[] = $v;
			$v = iconv("gbk","utf-8",$matchs[2]);
			$v = str_replace(array("年","月"), "-", $v);
			$valueList[] = $v;
		}
		else{
			$valueList[] = "";
			$valueList[] = "";	
		}
		$title[] = iconv("utf-8","GBK","商标专用开始日期");
		$title[] = iconv("utf-8","GBK","商标专用结束日期"); 
		$proList = "";
		if(preg_match("~.+NowIdOn=(\d+)&NowCLOn=(\d+).*~isU",$sourcePath,$matchs)){
			$url = "http://sa.shangdun.org/getTMproc.asp?R={$matchs[1]}&L={$matchs[2]}&sid=".rand(1000,2000);
			$pro = file_get_contents($url);
			if($pro && preg_match('~.*ShowList: "(.+)".*~isU',$pro,$match)){
			      $proList = $match[1];
			      $proList = str_replace(",","[-]",$proList);	
			}

		}

		$valueList[] = $proList;
		$title[] = iconv("utf-8","GBK","商标流程"); 
		$result = array("title"=>$title,"value"=>$valueList);
		return $result;
	}
	/* (non-PHPdoc)
	 * @see CParse::DefaultCacheFile()
	 */
	protected function DefaultCacheFile() {
		// TODO Auto-generated method stub
		$this->cacheFile = "shangdunparse";
	}
}
