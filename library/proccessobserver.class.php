<?php

Class CProccessObserver implements IObserver {
	
	/* (non-PHPdoc)
	 * @see IObserver::update()
	 */
	public function update($data) {
		echo $data["startpage"],",";
		if($data["startpage"] % 30 === 0) echo "<br>";
		ob_flush();
		flush();
		
	}

	
}