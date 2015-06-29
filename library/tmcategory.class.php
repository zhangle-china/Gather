<?php
/*
商标分类采集
数剧源：http://www.tmkoo.com/tm-class/
*/
Class CTmCategory {
	var $db;
	var $sourceUrl = "http://www.tmkoo.com/tm-class/";
	var $config;
	var $params ;
	function __construct(CMysql $db,CConfig $config){
		$this->db = $db;
		$this->config = $config;
		$this->params = $config->Params();
	}	
    
    /**
	 * �Ӹ��URL��ȡ��ҳ������
	 * @param String $url
	 * @param Array $option
	 * @return mixed
	 */
	protected function XCurl($url,Array $option = null){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_AUTOREFERER, true);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		if(is_array($option) && count($option)){
			foreach($option  as $key=>$v){
				curl_setopt($ch, $key, $v);
			}
		}
		$output = curl_exec($ch);
		return $output;
	}

    private function CovertCode($content){ 
    	return $content;
    }

	function Start(){
		$content = $this->XCurl($this->sourceUrl);
		if(!$content)	die("获取数据源失败！");
		if(!preg_match_all('~<div class="tmclass">(.*)</div>~isU',$content,$match)) die("提取以及分类列表失败！");		
		foreach($match[1] as $cat){ 		
			if(!preg_match('~<a href="/tm-class/(\d+)".*>(.+)</a>.*<a.*>(.+)</a>~isU', $cat,$match)) die("提取分类信息失败!");
			$data = array();
			$data["code"] = $match[1];
			$data["name"] = $match[2];
			$data["alias"] = $match[3];
			$arcUrl = $this->sourceUrl.$data["code"];
			$content = $this->XCurl($arcUrl);
			if(!preg_match('~<div class="tmGroupLeft".*>(.+)<div class="tmGroupRight".*>~isU',$content,$arcMatch)) die("分类详页提取失败！".$data["name"]); 
			$leftContent = $arcMatch[1];
			if(!preg_match('~<span class="title0">(.+)</span>.*<div class="content0">(.+)</div>.*<div id="note0" class="note0">(.*)</div>~isU',$leftContent,$leftMatch)) die('分类'.$data["name"].'内容提取失败！');
			$data["alias"] = $leftMatch[1];
			$data["summary"] = $leftMatch[2];
			$data["descript"] = $leftMatch[3];

			$parentid = $this->DataSave($data);
			if(!$parentid) die("分类保存失败！".$data["name"]);
			if(!preg_match('~<div class="tmGroupRight".*>(.+)</div>~isU',$content,$arcMatch)) die("二级分类内容页提取失败！");
			$rightContent = $arcMatch[1];
			if(!preg_match_all('~<a href="/tm-class-book/\S?\d+" class="title">(\S?\d+)</a>.*<a href="/tm-class-book/\S?\d+" title="(.+)".*>~isU',$rightContent,$rightMatch)) die("二级分类数据提取失败！".$data["name"]);
			foreach($rightMatch[1] as $key=>$childCat){
				$childData = array();
				$childData["parentid"] = $parentid;
				$childData["code"] = $childCat;
				$childData["name"] = $rightMatch[2][$key];         
	            $childUrl = dirname($this->sourceUrl)."/tm-class-book/".$childData["code"];
	            $childContent = $this->XCurl($childUrl);
	            if(!preg_match('~<div class="tmGroupRight".*>(.+)</div>~isU',$childContent,$childMatch)) die("二级分类详情页提取失败！".$data["name"]."->".$childData["name"]);
	            $childData["descript"] = $childMatch[1];
	            if(!$this->DataSave($childData)) die("二级分类内容保存失败！");
	            $this->params["code"][] = $childData["code"];
	            $this->params["code"][] = $data["code"];
	            //$this->conifg->WriteConfig($this->params);
	         }
		}
	}

	private function DataSave($data){ 
		$sql = "SELECT id as total FROM tm_categorytm WHERE code = '$data[code]'";
		$rs = $this->db->getOne($sql);
		if($rs) return $rs["id"];

		foreach($data as $key=>$v){ 
			$v = trim($v);
			$v = addslashes($this->CovertCode($v));
			if($key == "descript"){
				$descript = $v;
				continue;
			}
			$fields .= $key.",";
			$values .= "'".$v."',";
		}
		$fields = rtrim($fields,",");
		$values = rtrim($values,",");
		$sql = "INSERT tm_categorytm($fields) values($values)";
		if(!$this->db->query($sql)) return false;
		$id = $this->db->insert_id();
		if(!$descript) return $id;
		$sql = "INSERT INTO tm_categorytm_data(catid,descript) values($id,'".$descript."')";
		$this->db->query($sql);
		return $id;
	}
}
?>