<?php
interface IDataSave{
	function Save($data);
	/**
	 * 取得当前状态值
	 */
	function GetStatus();
	
	/**
	 * 设置状态
	 * @param Array $status
	 */
	function SetStatus($status);
}

