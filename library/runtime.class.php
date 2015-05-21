<?php

class CRunTime{
	private $startTime;
	private $endTime;
	private function get_cur_time(){
		list($usec, $sec) = explode(' ', microtime());
		return ((float)$usec + (float)$sec);
	}

	function start(){
		$this->startTime = $this->get_cur_time();
	}

	function get_time(){
		$this->endTime = $this->get_cur_time();
		return round(($this->endTime - $this->startTime) * 1000,1);  //微秒
	}
	function get_format_time(){
		$this->endTime = $this->get_cur_time();
		$time = ($this->endTime - $this->startTime) ;
		if($time <1) return ($time * 1000)."微秒";
		if($time < 60) return $time ."秒";
		if($time < 60*60) return floor ($time/60)."分钟".($time%60)."秒";
		if($time < 60*60*24){
			$h = floor($time/(60*60));
			$time = $time%(60*60);
			$i  = floor($time/60);
			$time = $time%60;
			$s = $time;
			return $h."小时".$i."分".$s."秒";
		}
		if($time < 60*60*24*365){
			$d = floor($time/(60*60*24));
			$time = $time%(60*60*24);
			$h = floor($time/(60*60));
			$time = $time%(60*60);
			$i  = floor($time/60);
			$time = $time%60;
			$s = $time;
			return $d."天".$h."小时".$i."分".$s."秒";
		}
	}
}
