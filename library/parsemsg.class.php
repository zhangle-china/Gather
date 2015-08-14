<?php
class ParseMsg{
	
	/**
	 * 提取目标地址失败；
	 * @return string
	 */
	static function FetchFailList(){
		return "提取目标地址失败！";
	}
	
	/**
	 * 提取文章列表页内容 失败
	 * @return string
	 */
	static function FetchFailArcListContent(){
		return "提取文章列表页内容 失败！";		
	}
	
		
	/**
	 * 提取详情页内容失败
	 * @return string
	 */
	static function FetchFailArcContent(){
		return "提取详情页内容失败！";
	}
	
	
	/**
	 * 解析文章地址失败
	 * @return string
	 */
	static function ParseFailArcUrl(){
		return "解析文章地址失败,非常规页面，需要特殊的解析方法";
	}
	
	
	/**
	 * 解析数据失败
	 * @return string
	 */
	static function ParseFailData(){
		return "解析数据失败，非常规页面，需要特殊的解析方法";
	}
	
	
	/**
	 * 非常规页面，需要特殊的解析方法
	 */
	static function  Unconventional(){
		return "非常规页面，需要特殊的解析方法";
	}
	
}