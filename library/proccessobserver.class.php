<?php

Class CProccessObserver implements IObserver {
	
	/* (non-PHPdoc)
	 * @see IObserver::update()
	 */
	public function update($data) {
		echo $data["startpage"]+$data["pageindex"].",";
		if($data["pageindex"] % 10 === 0) echo "<br>";
		ob_flush();
		flush();
		
	}

	
}