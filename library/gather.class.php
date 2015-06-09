<?php
/**
 *采集器，控制整个采集流程
 * @author zhangle
 * @version 1.1
 */
abstract  class CGather{
	protected $objLog; 
	protected $objDataSave;
	protected $objParse;
	protected $dataList;
	function __construct(CLog $log,IDataSave $objDataSave,CParse $objParse){
		$this->objLog = $log;
		$this->objDataSave = $objDataSave;
		$this->objParse = $objParse;
		$this->dataList = array();
	}
	
	/**
	 * ��CURL�ķ�ʽ����ȡ�ƶ���ַ������
	 * @param String $url
	 * @param string $ref
	 * @param unknown $post
	 * @param string $ua
	 * @param string $print
	 * @return mixed
	 */
	protected function xcurl($url,$ref=null,$post=array(),$ua="null",$print=false) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_AUTOREFERER, true);
		if(!empty($ref)) {
			curl_setopt($ch, CURLOPT_REFERER, $ref);
		}
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		if(!empty($ua)) {
			curl_setopt($ch, CURLOPT_USERAGENT, $ua);
		}
		if(count($post) > 0){
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
		}
		print_r($post);
		
		$output = curl_exec($ch);
		exit($output);
		curl_close($ch);
		if($print) {
			print($output);
		} else {
			return $output;
		}
	}
	/**
	 * ��ʼִ�вɼ����ɼ�������������ڿ�������ɼ�����
	 */
	abstract function Start();
	
	
}