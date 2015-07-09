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
			$data["alias"] = $match[2];
			$data["parentid"] = 0;
			$arcUrl = $this->sourceUrl.$data["code"];
			$content = $this->XCurl($arcUrl);
			if(!preg_match('~<div class="tmGroupLeft".*>(.+)<div class="tmGroupRight".*>~isU',$content,$arcMatch)) die("分类详页提取失败！".$data["name"]); 
			$leftContent = $arcMatch[1];
			if(!preg_match('~<span class="title0">(.+)</span>.*<div class="content0">(.+)</div>.*<div id="note0" class="note0">(.*)</div>~isU',$leftContent,$leftMatch)) die('分类'.$data["name"].'内容提取失败！');
			$data["seo_description"] = $leftMatch[2];
		//	$data["descript"] = $leftMatch[3];
			
			if(!preg_match('~><ul class="gbiregitab">(.*)</ul>.*<div class="tmGroupRight".*>(.+)</div>~isU',$content,$arcMatch)) die("二级分类内容页提取失败！");
			$aliasContent = $arcMatch[1];
			$rightContent = $arcMatch[2];

			preg_match('~><a target="_blank" href="/tm-class/'.$data["code"].'".*>(.+)</a>~isU', $aliasContent,$aliasMatch);
			$data["catname"] = str_replace("第{$data['code']}类-", "",  $aliasMatch[1]);
			$parentid = $this->DataSave($data);
			if(!$parentid) die("分类保存失败！".$data["name"]);
		
		
			if(!preg_match_all('~<a href="/tm-class-book/\S?\d+" class="title">(\S?\d+)</a>.*<a href="/tm-class-book/\S?\d+" title="(.+)".*>~isU',$rightContent,$rightMatch)) die("二级分类数据提取失败！".$data["name"]);
			foreach($rightMatch[1] as $key=>$childCat){
				$childData = array();
				$childData["parentid"] = $parentid;
				$childData["code"] = $childCat;
				$childData["catname"] = $rightMatch[2][$key];         
	            $childUrl = dirname($this->sourceUrl)."/tm-class-book/".$childData["code"];
	            $childContent = $this->XCurl($childUrl);
	          //  if(!preg_match('~<div class="tmGroupRight".*>(.+)</div>~isU',$childContent,$childMatch)) die("二级分类详情页提取失败！".$data["name"]."->".$childData["name"]);
	           // $childData["descript"] = $childMatch[1];
	            if(!$this->DataSave($childData)) die("二级分类内容保存失败！");
	            //$this->params["code"][] = $childData["code"];
	            //$this->params["code"][] = $data["code"];
	            //$this->conifg->WriteConfig($this->params);
	         }
		}
	}

	private function DataSave($data){ 
		$data["moduleid"] = 5;
		$category = new category($data["moduleid"],intval($data["parentid"]));
		$sql = "SELECT catid FROM tm_category WHERE code = '$data[code]' and moduleid = ".$data["moduleid"];
		$rs = $this->db->getOne($sql);
		
		if($rs){
			$category->update(array("$rs"=>$data));
			return $rs;
		}
		$category->add($data);
		return $category->catid;


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
		$sql = "INSERT tm_category($fields) values($values)";
		if(!$this->db->query($sql)) return false;
		$id = $this->db->insert_id();

		if(!$descript) return $id;
		$sql = "INSERT INTO tm_categorytm_data(catid,descript) values($id,'".$descript."')";
		$this->db->query($sql);
		return $id;
	}
}


class category {
	var $moduleid;
	var $catid;
	var $category = array();
	var $db;
	var $table;

	function category($moduleid = 1, $catid = 0) {
		global $db, $DT_PRE, $CATEGORY;
		$this->moduleid = $moduleid;
		$this->catid = $catid;
		
		$DT_PRE = "tm_";
		$this->table = $DT_PRE.'category';
		$this->db = &$db;
	}

	function add($category)	{
		$category['moduleid'] = $this->moduleid;
		$category['letter'] = preg_match("/^[a-z]{1}+$/i", $category['letter']) ? strtolower($category['letter']) : '';
		foreach(array('group_list',  'group_show',  'group_add') as $v) {
			$category[$v] = isset($category[$v]) ? implode(',', $category[$v]) : '';
		}
		$sqlk = $sqlv = '';
		foreach($category as $k=>$v) {
			$sqlk .= ','.$k; $sqlv .= ",'$v'";
		}
		$sqlk = substr($sqlk, 1);
		$sqlv = substr($sqlv, 1);
		$sql = "INSERT INTO {$this->table} ($sqlk) VALUES ($sqlv)";

		$this->db->query("INSERT INTO {$this->table} ($sqlk) VALUES ($sqlv)");
		$this->catid = $this->db->insert_id();
		if($category['parentid']) {
			$category['catid'] = $this->catid;
			$this->category[$this->catid] = $category;
			$arrparentid = $this->get_arrparentid($this->catid, $this->category);
		} else {
			$arrparentid = 0;
		}
		$catdir = $category['catdir'] ? $category['catdir'] : $this->catid;
		$this->db->query("UPDATE {$this->table} SET listorder=$this->catid,catdir='$catdir',arrparentid='$arrparentid' WHERE catid=$this->catid");
		return true;
	}

	function edit($category) {
		$category['letter'] = preg_match("/^[a-z]{1}+$/i", $category['letter']) ? strtolower($category['letter']) : '';
		if($category['parentid']) {
			$category['catid'] = $this->catid;
			$this->category[$this->catid] = $category;
			$category['arrparentid'] = $this->get_arrparentid($this->catid, $this->category);
		} else {
			$category['arrparentid'] = 0;
		}
		foreach(array('group_list',  'group_show',  'group_add') as $v) {
			$category[$v] = isset($category[$v]) ? implode(',', $category[$v]) : '';
		}
		$category['linkurl'] = '';
		$sql = '';
		foreach($category as $k=>$v) {
			$sql .= ",$k='$v'";
		}
		$sql = substr($sql, 1);
		$this->db->query("UPDATE {$this->table} SET $sql WHERE catid=$this->catid");
		return true;
	}

	function delete($catids) {
		if(is_array($catids)) {
			foreach($catids as $catid) {
				if(isset($this->category[$catid])) $this->delete($catid);
			}
		} else {
			$catid = $catids;
			if(isset($this->category[$catid])) {
				$this->db->query("DELETE FROM {$this->table} WHERE catid=$catid");
				$arrchildid = $this->category[$catid]['arrchildid'] ? $this->category[$catid]['arrchildid'] : $catid;
				$this->db->query("DELETE FROM {$this->table} WHERE catid IN ($arrchildid)");
				if($this->moduleid > 4) $this->db->query("UPDATE ".get_table($this->moduleid)." SET status=0 WHERE catid IN (".$arrchildid.")");
			}
		}
		return true;
	}

	function update($category) {
		if(!is_array($category)) return false;
		foreach($category as $k=>$v) {
			if(!$v['catname']) continue;
			$v['parentid'] = intval($v['parentid']);
			if($k == $v['parentid']) continue;

			//if($v['parentid'] > 0 && !isset($this->category[$v['parentid']])) continue;

			
			$v['listorder'] = intval($v['listorder']);
			$v['level'] = intval($v['level']);
			$v['letter'] = preg_match("/^[a-z0-9]{1}+$/i", $v['letter']) ? strtolower($v['letter']) : '';
			$v['catdir'] = $this->get_catdir($v['catdir'], $k);
			if(!$v['catdir'])$v['catdir'] = $k;
			$this->db->query("UPDATE {$this->table} SET catname='$v[catname]',parentid='$v[parentid]',listorder='$v[listorder]',style='$v[style]',level='$v[level]',letter='$v[letter]',catdir='$v[catdir]' WHERE catid=$k ");
		}
		return true;
	}

	function repair() {
		$query = $this->db->query("SELECT * FROM {$this->table} WHERE moduleid='$this->moduleid' ORDER BY listorder,catid");
		$CATEGORY = array();
		while($r = $this->db->fetch_array($query)) {
			$CATEGORY[$r['catid']] = $r;
		}
		$childs = array();
		foreach($CATEGORY as $catid => $category) {
			$CATEGORY[$catid]['arrparentid'] = $arrparentid = $this->get_arrparentid($catid, $CATEGORY);
			$CATEGORY[$catid]['catdir'] = $catdir = preg_match("/^[0-9a-z_\-\/]+$/i", $category['catdir']) ? $category['catdir'] : $catid;
			$sql = "catdir='$catdir',arrparentid='$arrparentid'";
			if(!$category['linkurl']) {
				$CATEGORY[$catid]['linkurl'] = listurl($category);
				$sql .= ",linkurl='$category[linkurl]'";
			}
			$this->db->query("UPDATE {$this->table} SET $sql WHERE catid=$catid");
			if($arrparentid) {
				$arr = explode(',', $arrparentid);
				foreach($arr as $a) {
					if($a == 0) continue;
					isset($childs[$a]) or $childs[$a] = '';
					$childs[$a] .= ','.$catid;
				}
			}
		}
		foreach($CATEGORY as $catid => $category) {
			if(isset($childs[$catid])) {
				$CATEGORY[$catid]['arrchildid'] = $arrchildid = $catid.$childs[$catid];
				$CATEGORY[$catid]['child'] = 1;
				$this->db->query("UPDATE {$this->table} SET arrchildid='$arrchildid',child=1 WHERE catid='$catid'");
			} else {
				$CATEGORY[$catid]['arrchildid'] = $catid;
				$CATEGORY[$catid]['child'] = 0;
				$this->db->query("UPDATE {$this->table} SET arrchildid='$catid',child=0 WHERE catid='$catid'");
			}
		}
		$this->cache($CATEGORY);
		return true;
	}

	function get_arrparentid($catid, $CATEGORY) {
		if($CATEGORY[$catid]['parentid'] && $CATEGORY[$catid]['parentid'] != $catid) {
			$parents = array();
			$cid = $catid;
			while($catid) {
				if($CATEGORY[$cid]['parentid']) {
					$parents[] = $cid = $CATEGORY[$cid]['parentid'];
				} else {
					break;
				}
			}
			$parents[] = 0;
			return implode(',', array_reverse($parents));
		} else {
			return '0';
		}
	}

	function get_arrchildid($catid, $CATEGORY) {
		$arrchildid = '';
		foreach($CATEGORY as $category) {
			if(strpos(','.$category['arrparentid'].',', ','.$catid.',') !== false) $arrchildid .= ','.$category['catid'];
		}
		return $arrchildid ? $catid.$arrchildid : $catid;
	}

	function get_catdir($catdir, $catid = 0) {
		if(preg_match("/^[0-9a-z_\-\/]+$/i", $catdir)) {
			$condition = "catdir='$catdir' AND moduleid='$this->moduleid'";
			if($catid) $condition .= " AND catid!=$catid";
			$r = $this->db->getOne("SELECT catid FROM {$this->table} WHERE $condition");
			if($r){
				return '';
			} else {
				return $catdir;
			}
		} else {
			return '';
		}
	}

	function get_letter($catname, $letter = true) {
		return $letter ? strtolower(substr(gb2py($catname), 0, 1)) : str_replace(' ', '', gb2py($catname));
	}

	function cache($data = array()) {
		cache_category($this->moduleid, $data);
	}
}
?>