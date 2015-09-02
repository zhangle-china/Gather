<?php
/**
 * 从表采集； 当主表采集到一条数据，此类负责采集相关的从数据；
 * @author Administrator
 */
Class CObserverDetail implements IObserver{
	var $task;
	function __construct(ITask $task){
		$this->task = $task;
	}
	
	
	/* (non-PHPdoc)
	 * @see IObserver::update()
	 */
	public function update($data) {
		// TODO Auto-generated method stub
		$parse = $this->task->GetParse();
		$parse->SetExtendData(array("masterid"=>$data["masterid"]));
		$parse->SetDataSoruceUrl($data["sourceurl"]);
		$this->task->Run();
	}
}