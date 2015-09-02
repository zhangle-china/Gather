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
		var $titles = array();
		var $cats = array();
		function __construct(Category $category = null){
			$this->category = $category;
			parent::__construct();
		}
		function DefaultCacheFile(){
			return "myparse-law";
		}
		
		function ListUrlParse($content=null,$sourcePath=""){
			$firstCat = $this->status["firstCat"];
			$secondCat = $this->status["secondCat"];
			$cacheKey = md5($firstCat.$secondCat);
			if($list = $this->ReadCache($cacheKey)){
				$this->cats = $this->ReadCache($cacheKey."-cats");
				return $list;
			}
		
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
				if(!preg_match('~<h3>.+<blockquote>(.+)</blockquote>~isU', $content,$match)) {
					echo "P:".parseMsg::Unconventional()." URL: ".$url." <br>";
					continue;
				}
				$content = $match[1];
				if(!preg_match_all('~<a href="(.+)">~isU', $content,$match)){
					echo "P:".parseMsg::Unconventional()." URL: ".$url." <br>";
					continue;
				}
				foreach ($match[1] as $detailUrl){
					$targetUrl = rtrim($url,"/")."/".$detailUrl;
					$result[] = $targetUrl;
					$this->cats[md5($targetUrl)] = $cat["id"];
				}
			}
			$this->Cache($cacheKey, $result);
			$this->Cache($cacheKey."-cats", $this->cats);
			return $result;
		}
		
		function ArcUrlParse($content,$sourcePath=""){
		
			$catid = $this->cats[md5($sourcePath)];
			$this->extendData["catid"] = intval($catid);
			!$content && $content = $this->getUrlContent($sourcePath);
			if(!$content) throw New Exception(ParseMsg::FetchFailArcListContent());
	
			if(!preg_match('~<ul.*>(.+)</ul>~isU',$content,$match)) throw new Exception(ParseMsg::ParseFailArcUrl());
			$content = $match[1];
			if(!preg_match_all('~<a href="(.+)">(.+)</a~isU', $content,$match)) throw new Exception(ParseMsg::ParseFailArcUrl());
			$result = array();
			
			foreach ($match[1] as $key=>$url){
				$url = dirname($sourcePath)."/".$url;
				$result[] = $url;
				$this->titles[md5($url)] = $match[2][$key]; 
				
			}
			return $result;
		}
		
		function ArcContentParse($content,$sourcePath=""){
			if($result = $this->PdfPage($content,$sourcePath)){
				return $result;
			}
			
			!$content && $content = $this->getUrlContent($sourcePath);
			
			if(!$content) throw New Exception(ParseMsg::FetchFailArcContent());
			if($result = $this->PdfPage($content,$sourcePath)){
				return $result;
			}	
			
			$title = array("title","content","year");
			if(!preg_match("~<hr>(.+<hr>)~isU", $content,$match)) throw new Exception(ParseMsg::ParseFailData());
			$content = $match[1];
			if(!preg_match("~<h2.*>(.*)</h2>~isU", $content,$match)) throw new Exception(ParseMsg::ParseFailData());
			$value["title"] = $match[1];
			if(!preg_match('~</h2>(.+)<hr>~isU',$content,$match)) throw new Exception(ParseMsg::ParseFailData());
			$value["content"] = $this->FilterTags($match[1]);
			
			if(preg_match_all('~([1|2][0-9]{3})~i',$value["title"],$match))
				$value["year"] = array_pop($match[1]);
			else 
				$value["year"] = "0";
			
			if($this->extendData["catid"]){
				$title[] = "catid";
				$value["catid"] = $this->extendData["catid"];
			}
			$result = array("title"=>$title,"value"=>$value);
			return $result;
			
		}
		
		protected function PdfPage($content,$sourcePath=""){
			
			$dir = $this->downloadDir;
			$dir = rtrim($dir,"/");
			$relatvieDir = "/pdf/";
			$this->status["firstCat"] && $relatvieDir.= $this->status["firstCat"]."/";
			$this->status["secondCat"] && $relatvieDir.= $this->status["secondCat"]."/";
			$dir .= $relatvieDir;
			mkdir($dir,"777",true);
			
			if($content && preg_match('~<object.*data="(.+\.pdf)".*>~isU',$content,$match)){
				$sourcePath = $match[1];
				if(!preg_match('~^http[s]?://.+~is',$sourcePath)){
					$sourcePath = "http://www.austlii.edu.au/".$sourcePath;
				}
			}
			elseif($content && preg_match('~<meta http-equiv="refresh".*url=(.+\.pdf)".*>~isU',$content,$match)){
				
				if(!preg_match('~^http[s]?://.+~is', $match[1])){
					$sourcePath = $sourcePath.$match[1];
				}
			}	
			
			if(!$sourcePath || strtolower(substr($sourcePath, strrpos($sourcePath, ".")+1)) != "pdf") return false;
			
			$fileName = time()."-".basename($sourcePath);
			$pdffile =  $relatvieDir.$fileName;
			if(!$this->download_file($sourcePath,$dir."/".$fileName)){
				$pdffile = $sourcePath;
			}
			$title = array("title","docfile","year");
			$value["title"] =  $this->titles[md5($sourcePath)];
			$value["docfile"] = $pdffile;
			if(preg_match_all('~([1|2][0-9]{3})~i',$value["title"],$match))
				$value["year"] = array_pop($match[1]);
			else
				$value["year"] = "0";
		
			if($this->extendData["catid"]){
				$title[] = "catid";
				$value["catid"] = $this->extendData["catid"];
			}
			return array("title" => $title,"value" => $value);
		}
		
		protected function download_file($url, $path) {
			$newfilename = $path;
			$opts = array(
			  'http'=>array(
			    'method'=>"GET",
			    'header'=>"Accept-language: en\r\n" .
			              "Cookie: foo=bar\r\n".
			  			  "User-Agent: Mozilla/5.0 (Windows NT 6.1; WOW64; rv:39.0) Gecko/20100101 Firefox/39.0\r\n"
			  )
			);
			try{
				$context = stream_context_create($opts);
				$file = fopen ($url, "rb",false,$context);
				$newfile = fopen ($newfilename, "wb");
				$size = stream_copy_to_stream($file, $newfile);
				fclose($file);
				fclose($newfilename);
				return $size;
			}catch (Exception $e){
				return false;
			}
			
			
		}
		
		public function getUrlContent($url) {
			// TODO Auto-generated method stub
			$option = array();
			//$option[CURLOPT_HEADER] = 1;
			$option[CURLOPT_HTTPHEADER] = array(
					"Host: www.austlii.edu.au",
					"User-Agent: Mozilla/5.0 (Windows NT 6.1; WOW64; rv:39.0) Gecko/20100101 Firefox/39.0",
					"Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8",
					"Accept-Language: zh-CN,zh;q=0.8,en-US;q=0.5,en;q=0.3",
					"Accept-Encoding: gzip, deflate",
					"Connection: keep-alive",
					"Cache-Control: max-age=0"
			);
			$option[CURLOPT_TIMEOUT] = 60;
			$connectNum = 0;
			$content = "";
			while(!$content && $connectNum < 5){
				$content = $this->XCurl($url,$option);
				$connectNum++;
			}
			return $content;
		}

	}
	
	
	class LegisParse extends MyParse{
		function ArcContentParse($content,$sourcePath = ""){
			if($result = $this->PdfPage($content,$sourcePath)){
				return $result;
			}
			!$content && $content = $this->getUrlContent($sourcePath);
			if(!$content) throw New Exception(ParseMsg::FetchFailArcContent());
			if($result = $this->PdfPage($content,$sourcePath)){
				return $result;
			}	
			if(!preg_match('~<hr.*>(.+<hr>)~isU', $content,$match)) throw new Exception(ParseMsg::ParseFailData());
			$content = $match[1];
				
			$title = array("title","content","year");
			if(!preg_match("~<h3>(.*)</h3>~isU", $content,$match)) throw new Exception(ParseMsg::ParseFailData());
			$value["title"] = $match[1];
			if(!preg_match('~</h3>(.+)<hr>~isU',$content,$match)) throw new Exception(ParseMsg::ParseFailData());
			$content = $match[1];
			$self = $this;
			$content = preg_replace_callback('~<a href="(s\d+.html)">(.+[\n\r])~isU',function($m) use($self,$sourcePath){
						$detailUrl = $sourcePath."/".$m[1];
						$dContent = $self->getUrlContent($detailUrl);
						preg_match("~<hr>(.*)<hr>~isU", $dContent,$dMatch);
						$dContent =  $dMatch[1];
						$dContent = $self->FilterTags($dContent);
						$m[2] = $self->FilterTags($m[2]);
						$data = str_replace(".html","",$m[1]);
						$result = "<span class='item'><a href='javascript:;' class='low-m' data='".$data."'>".$m[2]."<span>";
						$result .= "<div id='low-m-$data' style='' class='low-d'>$dContent</div>";
						return $result;
					},$content);
 			$value["content"] = $content;
 			
 			if(preg_match_all('~([1|2][0-9]{3})~i',$value["title"],$match))
 				$value["year"] = array_pop($match[1]);
 			else
 				$value["year"] = "0";
 			
 			if($this->extendData["catid"]){
 				$title[] = "catid";
 				$value["catid"] = $this->extendData["catid"];
 			}
 			
			$result = array("title"=>$title,"value"=>$value);
			return $result;
		}
	}
	
	Class FactoryParse {
		static function NewInstnace($type){
			$db = new CMySql("localhost", "root", "xtqiqi","low_aozhou");
			$category = new Category($db);
			switch (strtolower($type)){
				case 'legislation':
					return new LegisParse($category);
				break;
				default: return new MyParse($category);
			}
			
		}
	}
	
	
	Class Low { 
		var $task;
		var $category;
		var $firstCats;
		var $secondCats;
		
		function __construct(){

			$db = new CMySql("localhost", "root", "xtqiqi","low_aozhou");

			$this->category = new Category($db);
		
			$this->firstCats = $this->category->GetChildCategory(0);
			$this->secondCats = $this->category->GetChildCategory($this->firstCats[0]["id"]); 
			$_REQUEST["secondCat"] || $_REQUEST["secondCat"] = "case law";
			if(!in_array(strtolower($_REQUEST["secondCat"]),array("case law","legislation","materials"))) throw new Exception("创建失败，错误的二级类型！");
			$parse = FactoryParse::NewInstnace($_REQUEST["secondCat"]);
			$ds = new CMysqlDataSave("localhost", "root", "xtqiqi","low_aozhou","low");
			$observerProccess = new CProccessObserver("");
			$this->task = new CTask("austlii-law", $parse);
			$this->task->SetAllowErrNum(10);
			$this->task->SetDataSave($ds);
			$this->task->attach($observerProccess);
	
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
			ini_set('pcre.backtrack_limit', 1000000);
			
			$_REQUEST["firstCat"] && $this->task->SetStatus(array("firstCat"=>$_REQUEST["firstCat"]));
			$_REQUEST["secondCat"] && $this->task->SetStatus(array("secondCat" => $_REQUEST["secondCat"]));
			if(!in_array(strtolower($_REQUEST["secondCat"]),array("case law","legislation","materials"))) throw new Exception("创建失败，错误的二级类型！");
			$parse = FactoryParse::NewInstnace($_REQUEST["secondCat"]);
			
			$parse->SetStatus($this->task->GetSataus());
			$this->task->SetParse($parse);
			$this->task->Run();
			
		}
	}
	
?>
