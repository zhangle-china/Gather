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
	 * 解析数据失败
	 * @return string
	 */
	static function ParseFailData(){
		return "解析数据失败";
	}
	
	
}