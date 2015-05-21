<?php

/**
 * 采集状态观察者，用户将采集的实时状态记录下来，用于再次启动采集时，从最新状态开始采集
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