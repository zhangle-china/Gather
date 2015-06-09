<?php
abstract class CParse{
	protected $startPage;
	protected $endPage;
	protected $param;
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
	
	function SetParam($varName,$varValue){
		$this->param[$varName] = $varValue;
	}
	
	function GetParam(){
		return $this->param;
	}
	/**
	 * �Ӹ�������н��������Զ���ҳ���ַ�б?
	 * @param string $content
	 * @return Array 
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
	 * �Ӹ��URL��ȡ��ҳ������
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

