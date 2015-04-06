<?php

/**
 * 观察者模式。观察者对象接口
 * @author zhangle
 */
interface IObserver{
	function update($data);
}

/**
 * 观察者模式。被观察者对象接口。
 */
interface ISubject{
	function attach($observer);
	function deAttch($observer);
	function notifiy();
}