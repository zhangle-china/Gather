<?php
abstract class CParse{
	protected $startPage;
	protected $endPage;
	protected $param;
	protected $contentPageStyle;
	function __construct($startPage,$endPage){
		$this->contentPageStyle = "ARTICLE";
		$this->startPage = $startPage;
		$this->endPage = $endPage;
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
	
	function SetParam($varName,$varValue){
		$this->param[$varName] = $varValue;
	}
	
	function GetParam(){
		return $this->param;
	}
	/**
	 * 列表地址解析； 可以从给定的内容解析出列表地址，也可以按照某种规定制定列表地址，并返回；
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
	
	abstract function getUrlContent($url);
	
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
}

