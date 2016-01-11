<?php

class CShangbiaoPageCountObserver implements IObserver{
	protected $shangbiaoparse;
	protected $indexFlag = 0;
	function __construct(CTask $task){
		$this->shangbiaoparse = $task;
	}
	function update($data){
		$status = $this->shangbiaoparse->GetSataus();
		$this->indexFlag || $this->indexFlag = $status["listIndex"];
		if( $data["listIndex"] >  $this->indexFla) {
			$this->indexFla = $data["listIndex"]; 
			$status["startpage"] ++;
		}
		$this->shangbiaoparse->SetStatus($status);
	}
}