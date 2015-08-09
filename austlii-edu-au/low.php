<?php 

	Class Category{
		var $db;
		function __construct(CMySql $db){
			$this->db = $db;
		}
		
		function GetChildCategory($parent = 0){
			$sql = "SELECT * FROM category where parentid = $parent";
			$rs = $this->db->query($sql);
			$result = array();
			while($row = $this->db->fetch_array($rs)){
				$result[] = $row;
			}
			return $result;
		}
		
		function GetCats($cond){
			$sql = "SELECT * FROM category WHERE 1 = 1 and $cond";
			$rs = $this->db->query($sql);
			$result = array();
			while ($row = $this->db->fetch_array($rs)){
				$result[] = $row;								
			}
			return $result;
		}
	}


	
	Class MyParse extends CParse {
		var $category;
		function __construct(Category $category){
			$this->category = $category;
		}
		function DefaultCacheFile(){
			return "myparse-law";
		}
		
		function ListUrlParse($content=null,$sourcePath=""){
			$firstCat = $this->status["firstCat"];
			$secondCat = $this->status["secondCat"];
			$cacheKey = md5($firstCat.$secondCat);
			if($list = $this->ReadCache($cacheKey)) return $list;
			$firstCats = $this->category->GetCats("title ='".$firstCat."'");
			$firstCat = array_shift($firstCats);
			$secondCats = $this->category->GetCats("title = '$secondCat' and parentid=".$firstCat["id"]);
			foreach ($secondCats as $scat){
				if($scat["title"] == $secondCat){
					$secondCat = $scat;
				}
			}
			$threeCats = $this->category->GetChildCategory($secondCat["id"]);
			$result = array();
			foreach($threeCats as $cat){
				$url = $cat["url"];
				
				if(!preg_match('~^http://.*~is',$url)) $url = "http://www.austlii.edu.au/".ltrim($url,"/"); 
				$content = $this->getUrlContent($url);
				if(!$content) throw new Exception(ParseMsg::FetchFailList());
				if(!preg_match('~<h3>Decisions beginning with ...</h3>(.+)</h3>~isU', $content,$match)) throw new Exception(ParseMsg::FetchFailList());
				$content = $match[1];
				if(!preg_match_all('~<a href="(.+)">~isU', $content,$match)) throw new Exception(ParseMsg::FetchFailList());
				foreach ($match[1] as $detailUrl){
					$result[] = rtrim($url,"/")."/".$detailUrl;
				}
			}
			$this->Cache($cacheKey, $result);
			return $result;
			
		}
		
		function ArcUrlParse($content,$sourcePath=""){
			!$content && $content = $this->getUrlContent($sourcePath);
			
		}
	}
	
	Class FactoryParse {
		static function NewInstnace($type){
			return new MyParse();
		}
	}
	
	
	Class Low { 
		var $task;
		var $category;
		var $firstCats;
		var $secondCats;
		
		function __construct(){
			$category = new Category($db);
			$db = new CMySql("localhost", "root", "xtqiqi","low_aozhou");
			$this->category = new Category($db);
			$this->firstCats = $this->category->GetChildCategory(0);
			$this->secondCats = $this->category->GetChildCategory($this->firstCats[0]); 
			$_REQUEST["secondCat"] || $_REQUEST["secondCat"] = "case law";
			if(!in_array(strtolower($_REQUEST["secondCat"],array("case law","legislation","materials")))) throw new Exception("创建失败，错误的二级类型！");
			$parse = FactoryParse::NewInstnace($_REQUEST["secondCat"]);
			$ds = new CMysqlDataSave("localhost", "root", "xtqiqi","low_aozhou");
			$observerProccess = new CProccessObserver("");
			$this->task = new CTask("austlii-law", $parse);
			$this->task->SetDataSave($ds);
			$this->task->attach($observer);
		}
		
		function onDefault(){
			$this->onView();
		}
		
		function onView(){
			$firstCats = $this->firstCats;
			$secondCats =  $this->secondCats;
			$status = $this->task->GetSataus();
			require 'index.template.php';
			die();
		}
		
		function onRun(){
			$this->task->$_REQUEST["firstCat"] && $this->task->status["firstCat"] = $this->task->$_REQUEST["firstCat"];
			$this->task->$_REQUEST["secondCat"] && $this->task->status["secondCat"] = $this->task->$_REQUEST["secondCat"];
			if(!in_array(strtolower($_REQUEST["secondCat"],array("case law","legislation","materials")))) throw new Exception("创建失败，错误的二级类型！");
			$parse = FactoryParse::NewInstnace($_REQUEST["secondCat"]);
			$this->task->SetParse($parse);
			$this->task->Run();
		}
	}
	
	
	
?>