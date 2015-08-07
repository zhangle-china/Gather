<?php 
/**
 * 数据采集
 * 数据源 http://www.sgs.gov.cn/
 */

define("DEBUG",true);
require_once 'init.php';


class MyParse extends CParse{
	var $cat = array();
	function __construct(){
		$this->cat = array(
				"000000"=>"上海市工商局",
				'010000'=>"黄浦区市场监管局",				
				'040000'=>"徐汇区市场监督管理局",				
				'050000'=>"长宁区市场监管局",				
				'060000'=>"静安区市场监管局",				
				'070000'=>"普陀区市场监管局",				
				'080000'=>"闸北区市场监管局",				
				'090000'=>"虹口区市场监管局",				
				'100000'=>"杨浦区市场监管局",				
				'120000'=>"闵行区市场监督管理局",				
				'130000'=>"宝山区市场监督管理局",				
				'140000'=>"嘉定区市场监管局",				
				'150000'=>"浦东新区市场监管局",				
				'200000'=>"机场分局",				
				'260000'=>"奉贤区市场监管局",				
				'270000'=>"松江区市场监管局",				
				'280000'=>"金山区市场监管局",				
				'290000'=>"青浦区市场监督管理局",				
				'300000'=>"崇明县市场监管局",				
				'410000'=>"自贸试验区分局"			
		);
		parent::__construct(1, 1);
	}
	

	/* (non-PHPdoc)
	 * @see CParse::getUrlContent()
	*/
	public function getUrlContent($url) {
		// TODO Auto-generated method stub
		$options[CURLOPT_POST] = 1;
		$options[CURLOPT_HEADER] = 1;
		$options[CURLOPT_POSTFIELDS] = substr($url, strpos($url,"?")+1);
		$ip = sprintf("%d.%d.%d.%d",rand(1,100),rand(10,254),rand(1,254),rand(1,254));
		$options[CURLOPT_HTTPHEADER] = array("X-FORWARDED-FOR:$ip", "CLIENT-IP:$ip");
		//$options[CURLOPT_REFERER] =  "http://www.163.com";
		//$options[CURLOPT_PROXY] =  "http://127.0.0.1:61580";
		//$options[CURLOPT_USERAGENT] =  "Mozilla/5.0 (Windows NT 6.1) AppleWebKit/536.11 (KHTML, like Gecko) Chrome/20.0.1132.57 Safari/536.11";
		return $this->XCurl(substr($url,0,strpos($url, "?")), $options);	
	}
	
	/* (non-PHPdoc)
	 * @see CParse::ListUrlParse()
	 */
	public function ListUrlParse($content = null, $sourcePath = "") {
		$result = array();
		if($list = $this->ReadCache("pagelist-mc")){
			return $list;
		}
		
		foreach($this->cat as $key=>$v){
			
			$url = "http://www.sgs.gov.cn/shaic/appStat!toNameAppList.action";
			$urlFormat = $url."?p=%d&nameSearchCondition.acceptOrgan=%s&nameSearchCondition.checkName=&nameSearchCondition.startDate=&nameSearchCondition.endDate=";
			$nameurl = sprintf($urlFormat,1,$key);
			$result[] = $nameurl;
			$content = $this->getUrlContent($nameurl);
			if(preg_match("~<table.*class=\"page_table\".*>.*<td.*>共(\d+)页</td>.*</table>~isU", $content,$match)){
				$page = intval($match[1]);
				for($p = 2;$p<= $page;$p++){
					$result[] = sprintf($urlFormat,$p,$key);
				}
			}
			/*
			$url = "http://www.sgs.gov.cn/shaic/appStat!toEtpsAppList.action";
			$urlFormat = $url."?p=%d&appTotalSearchCondition.acceptOrgan=%s";
			$nzUrl = sprintf($urlFormat,1,$key);
			$result[] = $nzUrl;
			$content = $this->getUrlContent($nzUrl);
			if(preg_match("~<table.*class=\"page_table\".*>.*<td.*>共(\d+)页</td>.*</table>~isU", $content,$match)){
				$page = intval($match[1]);
				for($p = 2;$p<= $page;$p++){
					$result[] = sprintf($urlFormat,$p,$key);
				}
			}
			*/
		}
		$this->Cache("pagelist-mc", $result);
		return $result;
	}

	public function ArcUrlParse($content, $sourcePath = "") {
		return $sourcePath;
	}

	public function ArcContentParse($content, $sourcePath = "") {
		if(! preg_match("~<table class=tgList .*>(.+)</table>~isU", $content,$match)) die("1adsf");
		$content = $match[1];
		if(!preg_match_all("~<th>(.+)</th>~",$content,$match)) die("2adsf");
		foreach($match[1] as $key=>$value){
			$title[] = $value;
		}
		if(!preg_match_all("~<td>(.*)</td>~",$content,$match)) die("3adsf");
		foreach($match[1] as $key=>$v){
			$row[] = $v;
			if(($key+1)% count($title) == 0){
				$values[] = $row;
				$row = array();
			} 			
		}
		$result = array("title"=>$title,"value"=>$values);
		return $result;
	}
	
	protected function DefaultCacheFile() {
		// TODO Auto-generated method stub
		return "www-sgs-gov-cn";
	}
}

$file = str_replace(".php", "", __FILE__);
$file = str_replace(".","-", $file);
$parse = new MyParse(1,1);
$parse->SetContentPageStyle("LIST");
$config = new CConfig(ROOT."/data/$file.php");
$log = new CLog($file);
$log->SetOutputType(LogOutputType::FILE);

$fileName =iconv("utf-8","GBK",ROOT."/data/上海新注册企业-名称登记状态查询.csv");
$objDS = new CCsvDataSave($fileName,$log);

$processServ = new CProccessObserver();

$gather = new CNormalGather($log, $objDS, $parse);
$gather->attach($processServ);
$gather->Start();

?>