<?php

Class CProccessObserver implements IObserver {
	var $name;
	var $isInline = true;
	function __construct($name){
		$this->name = $name;
	}
	
	function SetIsInline($isInline){
		$this->isInline = $isInline;
	}
	/* (non-PHPdoc)
	 * @see IObserver::update()
	 */
	public function update($data) {
		echo $this->name.":".$data["listIndex"]."-".$data["arcListIndex"].",";
		if(!$this->isInline) echo "<br>";
		if($data["arcListIndex"] % 30 === 0) echo "<br>";
		ob_flush();
		flush();
		
	}

	
}