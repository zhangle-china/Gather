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
	 * �Ӹ����������н��������Զ���ҳ���ַ�б�
	 * @param string $content
	 * @return Array 
	 */
	abstract function ListUrlParse($content=null);
	
	/**
	 * �Ӹ����������н��������µ�URL��ַ
	 * @param unknown $content
	 * @return Array
	 */
	abstract function ArcUrlParse($content);
	
	
	/**
	 * �����������н�������Чֵ
	 * @param unknown $content
	 * @return Array ��title��value���������ֱ��� ���� �� ֵ
	 */
	abstract function ArcContentParse($content);
	
	
	/**
	 * �Ӹ�����URL��ȡ��ҳ������
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

