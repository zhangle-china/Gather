<?php 
/**
 * 数据采集
 * 数据源 http://www.sgs.gov.cn/
 */

define("DEBUG",true);
require_once '../init.php';

Class MyParse extends CParse{
	var $area = Array();
	var $homeUrl = "http://www.dianping.com";
	function getUrlContent($url){
		$options[CURLOPT_HTTPHEADER] = array(
				"Accept:text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8",
				'User-Agent:Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/45.0.2454.85 Chrome/45.0.2454.85 Safari/537.36',
		);
		return $this->XCurl($url,$options);
	}
	
	private function __parsePageUrl($content){
		$result = array();
		if(preg_match_all('~<a\s+href="(\S+)".*>~isU', $content,$match)){
			$cityUrlList = $match[1];
			foreach ($cityUrlList as $cityUrl){
				$content = $this->getUrlContent($this->homeUrl.$cityUrl);
				$pattern = '~<a.*href="(/search/category/\d+/\d+/g20038+)".*>~isU';
				if(!preg_match($pattern, $content,$match)){
					echo ("此地无农家乐信息".$cityUrl);
					continue;
				}
				$url = $match[1];
				$content = $this->getUrlContent($this->homeUrl.$url);
				if(!preg_match('~<div class="page">(.*)</div>~isU',$content, $match)){
					$result[] = $this->homeUrl.$url;
					continue;
				}
				$content = $match[1];
				if(!preg_match_all('~<a.*data\-ga\-page="(\d+)".*class="PageLink".*>~', $content,$match)){
					$result[] = $this->homeUrl.$url;
					continue;
				}
				$totalPage  = array_pop($match[1]);
				for ($i=1;$i<=$totalPage;$i++){
					$result[] = $this->homeUrl.$url."p".$i;
				}
			}
		}
		return $result;
	}
	function ListUrlParse($content = NULL, $sourcePath = ''){
		$content = $this->getUrlContent($this->homeUrl."/citylist/citylist?citypage=1");
		if(!preg_match('~<ul .* id="divArea">(.*)</ul>~isU',$content,$match)) throw new Exception("提取城市列表失败！");
		$content = $match[1];
		if(!preg_match_all('~<li.*>(.*)</li>~isU',$content,$match)) throw new Exception("解析地区信息失败");
		$items = $match[1];
		$result = array();
		foreach ($items as $item){
			if(preg_match_all("~<dl.*>(.+)</dl>~isU",$item,$match)){
				$areaItems = $match[1];
				foreach ($areaItems as $areaItem){
					preg_match("~<dt>(\S+)</dt>~isU",$areaItem,$match);
					$province = $match[1];
					$cacheKey = md5($province);
					$data = $this->ReadCache($cacheKey);
					if(!$data){
						$data = $this->__parsePageUrl($areaItem);
						$this->Cache($cacheKey,$data);
					}
					$result = array_merge($result,$data);
				}
			}
			else{
				preg_match('~<strong class="vocabulary">(\S+)</strong>~isU', $item,$match);
				$province = $match[1];
				$cacheKey = md5($province);
				$data = $this->ReadCache($cacheKey);
				if(!$data){
					$data = $this->__parsePageUrl($item);
					$this->Cache($cacheKey,$data);
				}
				$result = array_merge($result,$data);
			}
		}
		$this->Cache("pageList", $result);
		return $result;
	}
	function ArcUrlParse($content,$sourcePath=""){
		$content = $this->getUrlContent($sourcePath);
		if(!$content) Throw new Exception("提取列表内容失败！");
		if(!preg_match('~<div.*id="shop-all-list">(.+)</ul>~isU', $content,$match)) throw  new Exception("解析列表内容失败！");
		$content = $match[1];
		if(!preg_match_all('<a.*href="(/shop/\d+)".*>',$content,$match)) throw new Exception("解析文章地址失败");
		$result = $match[1];
		$result = array_unique($result);
		return $result;
	}
	
	function ArcContentParse($conten="",$sourcePath=""){
		if(!preg_match("~http://.*~", $sourcePath)) $sourcePath = $this->homeUrl.$sourcePath;
		preg_match("~.*/(\d+)$~is",$sourcePath,$match);
		$shopid = $match[1];
		if($this->__isNote($shopid)) return array(); //以采集过
		$conten = $this->getUrlContent($sourcePath);
		if(!$conten) throw new Exception("提取文章祥页内容失败！");
		if(!preg_match('~<div id="basic-info".*>(.*)<a class="J-unfold unfold">~isU', $conten,$match)) throw new Exception("提取商家基本信息失败！");
		$baseContent = $match[1];
		$result["title"] =  array("商家名称","电话","地区","地址","图像");
		preg_match('~<a class="city J-city">(.*)</a>~isU',$conten,$match);
		$city = @strip_tags($match[1]);
		preg_match('~<h1 class="shop-name">(.*)<~isU',$baseContent,$match);
		$shopName = @preg_replace("~\s~", "", $match[1]);
		preg_match('~地址：(.*)</div>~isU',$baseContent,$match);
		$address = @preg_replace("~\s~", "", strip_tags($match[1]));
		preg_match('~电话：(.*)</p>~isU',$baseContent,$match);
		$phone  = @preg_replace("~\s~", "", strip_tags($match[1]));
		$image = "";
		if(preg_match('~<div id="aside" class="aside">(.*)</div>~isU',$conten, $match)){
			$imgContent = $match[1];
			preg_match('~<img.*src="(.+)"~isU', $imgContent,$match);
			$image = @$match[1];
			if($image && @copy($image, $this->downloadDir."/".$shopid."-".basename($image))){
				$image = $shopid."-".basename($image);
			}
		}
		$shopName = str_replace(",", "，", $shopName);
		$phone = str_replace(",", "，", $phone);
		$address = str_replace(",", "，", $address);
		$result["value"] = array($shopName,$phone,$city,$address,$image);
		$this->__note($shopid);
		return $result;
	
				
	}
	
	function DefaultCacheFile(){
		
	}
	
	/**
	 * 记录已下载的地址
	 * @param unknown $url
	 * @return boolean
	 */
	private function __note($value){
		$noteFile = dirname(dirname($this->cacheFile))."/note.php";
		$note  = file_get_contents($noteFile);
		$note && $note = unserialize($note);
		$note[] = $value;
		$note = serialize($note);
		file_put_contents($noteFile, $note);
		return true;
	}
	
	/**
	 * 判断是否采集过
	 * @param string $url
	 */
	private function __isNote($value){
		$noteFile = dirname(dirname($this->cacheFile))."/note.php";
		$note  = file_get_contents($noteFile);
		if(!$note) return false;
		$note = unserialize($note);
		return in_array($value,$note);
	}
}

Class Dianping {
	var $task;
	var $category;
	var $firstCats;
	var $secondCats;

	function __construct(CLog $log = null){
		$parse = new MyParse();
		$observerProccess = new CProccessObserver("");
		$this->task = new CTask("dianping", $parse);
	         $filename = $this->task->GetTaskDir()."/data/nonjiale.csv";
		$ds = new CCsvDataSave($filename,$this->task->GetLog());
		$this->task->SetDataSave($ds);
		$this->task->attach($observerProccess);
	}

	function onDefault(){
		$this->onView();
	}

	function onView(){
		$firstCats = $this->firstCats;
		$secondCats =  $this->secondCats;
		$status = $this->task->GetSataus();
		require 'index.template.php';
		die();
	}

	function onRun(){
		ini_set('pcre.backtrack_limit', 1000000);
		$this->task->SetAllowErrNum(1000000);
		$this->task->Run();
			
	}
}