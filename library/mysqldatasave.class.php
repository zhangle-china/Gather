<?php
class CMysqlDataSave implements IDataSave{
	private $_db;
	private $_masterTable;
	private $_detailTable;
	private $_createdTables; 
	function __construct($host,$user,$pwd,$database,$masterTable){
		$this->_db = new CMySql($host, $user, $pwd);
		$sql = "CREATE DATABASE IF NOT EXISTS $database";
		$this->_db->query($sql);
		$this->_db->select_database($database);
		$this->masterTable = $masterTable;
		$this->_createdTables = array();
	}
	
	function addDetailTable($key,$tablename){
		$this->_detailTable[$key] = $tablename;
	}
	
	function delDatailTable($key){
		unset($this->delDatailTable[$key]);
	}
	
	
	/* (non-PHPdoc)
	 * @see IDataSave::Save()
	 */
	public function Save($data) {
		// TODO Auto-generated method stub

		if(empty($data["title"]) || empty($data["value"])) throw new Exception("数据入库失败！入库数据格式不正确！");
		if(!is_array($data["title"]) || count($data["title"]) == 0 || count($data["title"]) != @count($data["value"][0])) throw new Exception("数据入库失败！入库数据格式不正确！");	

		$insertSQL = array();
		foreach ($data["value"] as $dataKey=>$dataItem){
			$insertFields =  "";
			$insertValues = "";
			foreach($dataItem as $key=>$value){
			
				if(is_array($value)){
					$detailkey = $data["title"][$key];
					$detailTableName = $this->_detailTable[$detailkey];
					$detailTableName || $detailTableName = $detailkey;
				
					$insertDetailValues = "%d";
					$insertDetailSQL =  array();
					$detailTitle = $value["title"];
					$detailVlaues = $value["value"];
					$insertDetailFields = "master_id";
					
					foreach($detailVlaues as $dValues){
						foreach($dValues as $dkey=>$dValue){
							if(!settype($dValue,"string")){
								unset($detailTitle[$dkey]);
								continue;
							}
							$insertDetailFields .= ",$detailTitle[$dkey]";
							$insertDetailValues .=",'$dvalue'";
						}
					
						$insertDetailFields = trim($insertDetailFields,",");
						$insertDetailValues = trim($insertDetailValues,",");
						$insertDetailSQL[] = "INSERT $detailTableName($insertDetailFields) values($insertDetailValues)";
						
					}
	
					$detailCreateSQL = "CREATE TABLE IF NOT EXISTS $detailTableName(did int(11) AUTO_INCREMENT PRIMARY KEY,master_id int(11) NOT NULL,";
			
					foreach($detailTitle as $title){
						$title= preg_replace("/\s+/is","",$title);
						$detailCreateSQL .= "$title varchar(50),";
					}
					$detailCreateSQL = trim($detailCreateSQL,",");
					$detailCreateSQL .= ")";
					//如果从表未被创建
					if(!in_array($detailTableName,$this->_createdTables)){
						$this->_db->query($detailCreateSQL);
						$this->_createdTables[] = $detailTableName;
					}
					continue;
				}
				
				if(!settype($value,"string")){ 
					unset($data["title"][$key]);
					continue;
				}
				$insertFields .= ",".$data['title'][$key];
				$insertValues .= ",'".addslashes($value)."'";
			}
			$insertFields = trim($insertFields,",");
			$insertValues = trim($insertValues,",");
			
			$insertSQL[] =  "INSERT INTO ".$this->_masterTable." (".$insertFields.") values($insertValues)";	
		}
		foreach($data["title"] as $field){
			$createMasterSQL .= ",".$field." VARCHAR(50)";
		}
		$createMasterSQL = trim($createMasterSQL,",");
		$createMasterSQL = "CREATE TABLE IF NOT EXISTS ".$this->_masterTable."(mid int(11) AUTO_INCREMENT PRIMARY KEY ".$createMasterSQL.")";
		die($createMasterSQL);
		if(!in_array($this->_masterTable,$this->_createdTables)){
			$this->_db->query($createMasterSQL);
			$this->_createdTables[] = $this->_masterTable;
		}
		
	
		if(!$this->_db->query($sql)) throw new Exception("数据入库失败！");
		$masterID = $this->_db->insert_id();
		foreach ($insertDetailSQL as $key=>$sql){
			$sql = sprintf($sql,$masterID);
			$insertDetailSQL[$key] = $sql;
		}
		
		$sql = implode(";", $insertDetailSQL);
		$this->_db->query($sql);
		var_dump("asdf");
		die();
	}

	/* (non-PHPdoc)
	 * @see IDataSave::GetDataFile()
	 */
	public function GetDataFile() {
		// TODO Auto-generated method stub
		
	}

	
}