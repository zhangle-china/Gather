<?php

/**
 * �۲���ģʽ���۲��߶���ӿ�
 * @author zhangle
 */
interface IObserver{
	function update($data);
}

/**
 * �۲���ģʽ�����۲��߶���ӿڡ�
 */
interface ISubject{
	function attach($observer);
	function deAttch($observer);
	function notifiy();
}