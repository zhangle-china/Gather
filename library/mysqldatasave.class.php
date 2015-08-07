<?php
class CMysqlDataSave implements IDataSave{
	private $_db;
	private $_masterTable;
	private $_detailTable;
	private $_createdTables; 
	function __construct($host,$user,$pwd,$database="caiji",$masterTable="master"){
		$this->_db = new CMySql($host, $user, $pwd);
		$sql = "CREATE DATABASE IF NOT EXISTS $database";
		$this->_db->query($sql);
		$this->_db->select_database($database);
		$this->_masterTable = $masterTable;
		$this->_createdTables = array();
	}
	
	/* (non-PHPdoc)
	 * @see IDataSave::Save()
	 */
	public function Save($data) {
		// TODO Auto-generated method stub
		if(empty($data["title"]) || empty($data["value"])){
			var_dump($data);
			throw new Exception("数据入库失败！入库数据格式不正确！");
		}
		if(!is_array($data["title"]) || count($data["title"]) == 0 || count($data["title"]) != @count($data["value"][0])){
			var_dump($data)	;
			throw new Exception("数据入库失败！入库数据格式不正确！");
		}
		$insertSQL = array();
		foreach ($data["value"] as $dataKey=>$dataItem){
			$insertFields =  "";
			$insertValues = "";
			$insertDetailSQL =  array();
			foreach($dataItem as $key=>$value){
				if(!settype($value,"string")){ 
					unset($data["title"][$key]);
					continue;
				}
				if(in_array($data["title"][$key],$this->_createdTables) ) continue;
				$insertFields .= ",`".$data['title'][$key]."`";
				$insertValues .= ",'".addslashes($value)."'";
			}
			$insertFields = trim($insertFields,",");
			$insertValues = trim($insertValues,",");
			$insertFields = preg_replace("~\s~is","",$insertFields);
			$insertFields = str_replace("/","",$insertFields);
			$insertSQL[] = array("sql"=>"INSERT INTO ".$this->_masterTable." (".$insertFields.") values($insertValues)","detailSQL"=>$insertDetailSQL);	
		}
		foreach($data["title"] as $field){
			if(strtolower($field) == "masterid"){
				$createSQL .= ",masterid int(11) NOT NULL DEFAULT 0";
				continue;
			}
			$field = preg_replace("~\s~is","",$field);	
			$field = str_replace("/","",$field);
			$createSQL .= ",".$field." VARCHAR(500) NOT NULL DEFAULT ''";
		}
		
		$createSQL = trim($createSQL,",");
		$createSQL = " CREATE TABLE IF NOT EXISTS ".$this->_masterTable."(mid int(11) AUTO_INCREMENT PRIMARY KEY, ".$createSQL.")";
		if(!in_array($this->_masterTable,$this->_createdTables)){
			$this->_db->query($createSQL);
			$this->_createdTables[] = $this->_masterTable;
		}
		
	   foreach($insertSQL as $SQL){
	   	  $sql = $SQL["sql"];
	   	  try{
	   	  	$this->_db->query($sql);
	   	  	$resultid = $this->_db->insert_id();
	   	  }
	   	  catch (Exception $e){
	   	  	echo "<br><br>------------------------------------------------------<br><br>";
	   	  	echo $e->getMessage();
	   	  	ob_flush();
	   	  	flush();
	   	  	continue;
	   	  }
	   }
	   if(!$resultid) throw new Exception("入库失败！".$sql);
	   return $resultid;
	}

	public function SetStatus($status){
		
	}
	public function GetStatus() {
		return  array(); //无状态对象
	}
	
}