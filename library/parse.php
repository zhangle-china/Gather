<?php
abstract class CParse{
	protected  $startPage;
	protected  $endPage;
	function __construct($startPage,$endPage){
		$this->startPage = $startPage;
		$this->endPage = $endPage;
		
	}
	
	function getStartPageNum(){
		return $this->startPage;
	}
	
	function getEndPageNum(){
		return $this->endPage;
	}
	/**
	 * 从给定的文章中解析，或自定义页面地址列表。
	 * @param string $content
	 * @return Array 
	 */
	abstract function ListUrlParse($content=null);
	
	/**
	 * 从给定的内容中解析出文章的URL地址
	 * @param unknown $content
	 * @return Array
	 */
	abstract function ArcUrlParse($content);
	
	
	/**
	 * 从文章内容中解析出有效值
	 * @param unknown $content
	 * @return Array 有title和value两个键，分别存放 标题 和 值
	 */
	abstract function ArcContentParse($content);
	
	
	/**
	 * 从给定的URL中取得页面内容
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

