<?php

/**
 * 采集观察者，记录状态变化
 * @author Administrator
 *
 */
class CObserver implements IObserver{
	private  $config;
	function __construct(CConfig $config){
		$this->config = $config;
	}
	
	function update($data){
		if($this->config->Params()) $data = array_merge($this->config->Params(),$data);
		$this->config->WriteConfig($data);
	}
}