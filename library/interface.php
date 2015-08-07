<?php

/**
 * 任务接口
 * @author zhangle
 *
 */
interface ITask{
	/**
	 * 返回此任务的解析器对象；
	 */
	function GetParse();
	function Run();
}

/**
 *观察者接口
 * @author zhangle
 */
interface IObserver{
	/**
	 * 当被观察的对象发生变化时，此方法被调用；
	 * @param Array $data ; 发生变化的关键值；根据具体的观察着和被观察者而定；
	 */
	function update($data);
}

/**
 *被观察者接口
 */
interface ISubject{
	function attach($observer);
	function deAttch($observer);
	function notifiy();
}