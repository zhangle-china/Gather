<?php
abstract class CParse {

	protected $contentPageStyle;
	protected $cache;
	protected $cacheFile;
	protected $extendData;
	protected $dataSourceUrl;
	protected $status;
	protected $downloadDir;
	
	function __construct(){
		$this->contentPageStyle = "ARTICLE";
		$this->cache = true;
		$this->SetCacheFile($this->DefaultCacheFile());
	}
	
	function SetDownloadDir($dir){
		if(!is_dir($dir)) throw new Exception("您指定的目录不存在".$dir);
		$this->downloadDir = $dir;
	}
	function SetStatus($status){
		$status["cache"] && $this->cache=$status["cache"];
		$status["cacheFile"] && $this->cacheFile=$status["cacheFile"];
		$status["extendData"] && $this->extendData=$status["extendData"];
		$status["dataSourceUrl"] && $this->dataSourceUrl=$status["dataSourceUrl"];
	}
	function GetStatus(){
		$this->status["cache"] = $this->cache;
		$this->status["cacheFile"] = $this->cacheFile;
		$this->status["extendData"] = $this->extendData;
		$this->status["dataSourceUrl"] = $this->dataSourceUrl;
		return $this->status;
	}
	
	function InitStatus(){
		$this->cache = true;
		$this->status["extendData"] = "";
		$this->status["dataSourceUrl"] = "";
	}
	/**
	 * 设置数据源地址，解析器可以以该地址为基础，解析出目标地址
	 * @param unknown $URL
	 */
	function SetDataSoruceUrl($URL){
		$this->dataSourceUrl = $URL;
	}
	
	protected abstract function DefaultCacheFile();
	/**
	 * 提取URL的内容
	 * @param String $url
	 * @param Array $option
	 * @return mixed
	 */
	protected function XCurl($url,Array $option){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_AUTOREFERER, true);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		if(is_array($option) && count($option)){
			foreach($option  as $key=>$v){
				curl_setopt($ch, $key, $v);
			}
		}
		$output = curl_exec($ch);
		return $output;
	}
	
	/**
	 * 每个具体的解析器，有自己提取URL内容的方式
	 * @param string $url
	 */
	abstract function getUrlContent($url);
	
	function __toString(){
		return "CParse";
	}
	/**
	 * 关闭缓存；
	 */
	function CloseCache(){
		$this->cache = false;
	}
	
	function SetCacheFile($filename){
		$dir = dirname($filename);
		if(!is_dir($dir)){
			$tmpFile = $this->cacheFile = ROOT."/data/cache/".$this."-".$filename;
		}
		else{
			$tmpFile = $filename;
		}
		if(!strpos(basename($tmpFile),".")){
			$tmpFile = trim($tmpFile,"/"). "/cache.tmp"; 
		}
		if(false === ($lastIndex = strrpos($tmpFile, ".")) || strtolower(substr($tmpFile, $lastIndex)) !== ".tmp" ){
			$tmpFile = $tmpFile.".tmp";
		}
		
		$this->cacheFile = $tmpFile;
	}
	
	protected function Cache($key,$value){
		if(!$this->cache) return false;
		$cache = file_get_contents($this->cacheFile);
		$cache = unserialize($cache);
		$cache[$key] = $value;
		$cache = serialize($cache);
		$f = fopen($this->cacheFile,"w");
		fwrite($f, $cache);
		fclose($f);
		return true;
	}
	
	protected function ReadCache($key){
		if(!$cache = file_get_contents($this->cacheFile) ) return false;
		$cache = unserialize($cache);
		if(!key_exists($key, $cache)) return false;
		return $cache[$key];
	}
	/**
	 * 设置内容页样式，决定了对采集到的值得处理方式
	 * @param sring $style 样式；取值范围 "ARTICLE","LIST" ；不在此范围的 ，系统一律按"ARTICLE"
	 * 
	 */
	function SetContentPageStyle($style){
		$style = strtoupper($style);
		if(!in_array($style,Array("ARTICLE","LIST"))) $style = "ARTICLE";
		$this->contentPageStyle = $style;
	}
	
	/**
	 * 取得当前对象的内容页样式；
	 * @return string
	 */
	function GetContentPageStyle(){
		return $this->contentPageStyle;
	}
	
	/**
	 * 返回开始页
	 */
	function getStartPageNum(){
		return $this->startPage;
	}
	
	function getEndPageNum(){
		return $this->endPage;
	}
	
	
	/**
	 * 设置扩展数据；采集到的数据将和此扩展数据进行合并存储
	 * @param array $extendData  //key=>vlaue ，value 只能为基础数据类型，不允许为对象 
	 */
	function SetExtendData(Array $extendData){
		$this->extendData = $extendData;
	}
	
	
	/**
	 * 目标地址解析； 可以从给定的内容解析出 要采集的网页地址，也可以按照某种自动生成目标地址，并以数组的形式返回；
	 * @param string $content 制定内容；默认为空； 
	 * @param string $sourcePath 内容源地址；从改地址取得内容，并从中解析列表地址；
	 * @return Array 列表地址 
	 */
	abstract function ListUrlParse($content=null,$sourcePath="");
	
	
	/**
	 * 从给定的内容中解析出文章的地址
	 * @param unknown $content 文章内容
	 * @param string $sourcePath 文章源地址
	 * @return Array
	 */
	abstract function ArcUrlParse($content,$sourcePath="");
	
	
	/**
	 * 从文章内容中解析所需要的数据
	 * @param string $content
	 * @param string $sourcePath 文章源地址
	 * @return Array ��title��value������ֱ��� ���� �� ֵ
	 */
	abstract function ArcContentParse($content,$sourcePath="");
	
}

