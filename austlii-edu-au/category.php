 <?php 
/**
 * 分类采集
 */
require '../init.php';

Class FirstCategoryParse extends CParse{

	protected function DefaultCacheFile() {
		// TODO Auto-generated method stub
		
		return "austlii-category.tmp";
	}

	public function getUrlContent($url) {
		// TODO Auto-generated method stub
		$option = array();
		$option[CURLOPT_HEADER] = 1;
		$option[CURLOPT_HTTPHEADER] = array(
			"Host: www.austlii.edu.au",
			"User-Agent: Mozilla/5.0 (Windows NT 6.1; WOW64; rv:39.0) Gecko/20100101 Firefox/39.0",
			"Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8",
			"Accept-Language: zh-CN,zh;q=0.8,en-US;q=0.5,en;q=0.3",
			"Accept-Encoding: gzip, deflate",
			"Connection: keep-alive",
			"Cache-Control: max-age=0"	
		);
		return $this->XCurl($url,$option);
	}

	public function ListUrlParse($content = "", $sourcePath = "") {
		// TODO Auto-generated method stub
		return array("http://www.austlii.edu.au/");
		
	}

	/* (non-PHPdoc)
	 * @see CParse::ArcUrlParse()
	 */
	public function ArcUrlParse($content, $sourcePath = "") {
		// TODO Auto-generated method stub
		if(!$content && $sourcePath) $content = $this->getUrlContent($sourcePath);
		if(!$content) throw new Exception(ParseMsg::FetchFailArcContent());
		if(!preg_match('~<li><a href="/databases.html">All Databases</a><br><br></li>(.*)</ul>~isU',$content,$match)) throw new Exception(ParseMsg::ParseFailData());
		$content = $match[1];
		preg_match_all('~<a href="(.+)">(.+)</a>~isU', $content,$match);
		$result = array();
		foreach ($match[1] as $value){
			$result[] = $sourcePath."?starttag=".$value;
		}
		return $result;
	
	}

	/* (non-PHPdoc)
	 * @see CParse::ArcContentParse()
	 */
	public function ArcContentParse($content, $sourcePath = "") {
		// TODO Auto-generated method stub
		$startTag = substr($sourcePath, strrpos($sourcePath,"=")+1);
		if(!$content && $sourcePath) $content = $this->getUrlContent($sourcePath);
		if(!$content) throw new Exception(ParseMsg::FetchFailArcContent());
		$title = array("title","parentid","url");
		if(!preg_match('~<li><a href="/databases.html">All Databases</a><br><br></li>(.*)</ul>~isU',$content,$match)) throw new Exception(ParseMsg::ParseFailData());
		$content = $match[1];
		
		preg_match('~<a href="'.$startTag.'">(.+)</a>~isU', $content,$match);
		$value[] = $match[1];
		$value[] = 0;
		$value[] = $startTag;
		$result = array("title"=>$title,"value"=>$value);
		return $result;
	}
}


class SecondParse extends FirstCategoryParse{
	protected function DefaultCacheFile() {
		// TODO Auto-generated method stub
		return "austlii-category-second.tmp";
	}
	
	function ListUrlParse($content="", $sourcePath=""){
		if(!$this->dataSourceUrl) return false;
		$url = str_replace("?starttag=","",$this->dataSourceUrl);
		return array($url);
	}
	
	function ArcUrlParse($content,$sourcePath = ""){
		$result = array();
		$result[] = $sourcePath."?cat=Case Law";
		$result[] = $sourcePath."?cat=Legislation";
		$result[] = $sourcePath."?cat=Materials";
		return $result;
	}
	
	
	function ArcContentParse($content,$sourcePath = ""){
		echo $sourcePath."<br>";
		$cat = substr($sourcePath, strrpos($sourcePath, "=")+1);
		$title = array("title","parentid");
		$value[] = $cat;
		$value[] = $this->extendData["masterid"];
		return array("title"=>$title,"value"=>$value);
	}
}

class ThreeParse extends FirstCategoryParse{
	function __construct(){
		$this->contentPageStyle = "LIST";
	}
	protected function DefaultCacheFile() {
		// TODO Auto-generated method stub
		return "austlii-category-three.tmp";
	}
	
	function ListUrlParse($content = "",$sourcePath = ""){
		if(!$this->dataSourceUrl) return false;
		return array($this->dataSourceUrl);
	}
	
	function ArcUrlParse($content,$sourcePath = ""){
		return array($sourcePath);
	}
	
	
	function ArcContentParse($content,$sourcePath = ""){
		!$content && $content = $this->getUrlContent($sourcePath);
		if(!content) throw new Exception(ParseMsg::FetchFailArcContent());
		$cat = substr($sourcePath, strrpos($sourcePath, "=")+1);
		
		$title = array("title","parentid","url");
		if(preg_match('~<div id="databases">(.*)</form>~isU', $content,$match)) 
			$content = $match[1]."</form>";
		elseif(preg_match('~<div id="databases">(.*) </div>~isU', $content,$match)){
			$content = $match[1];
		}
		else{
			throw new Exception(ParseMsg::ParseFailData());
		}
		if(preg_match('~'.$cat.'</A></H4>(.*)</ul>~isU', $content,$match))
			$content = $match[1];
	    elseif(preg_match('~'.$cat.'</A></H4>(.*)(<H4|</form>)~isU', $content,$match))
	    	$content = $match[1];
	    else throw new Exception(ParseMsg::ParseFailData().$content);
		preg_match_all('~<A HREF="(.+)">(.+)</a>~isU',$content,$match);
		$values = array();
		foreach ($match[1] as $key=>$v){
			$value = array();
			$value[] = $match[2][$key];
			$value[] = $this->extendData["masterid"];
			$value[] = $v;
			$values[] = $value; 
		}
		
		return array("title"=>$title,"value"=>$values);
	}
}

$firstProccess = new CProccessObserver("一级分类");
$firstParse = new FirstCategoryParse();
$firstDs = new CMysqlDataSave("localhost", "root", "xtqiqi","low_aozhou","category");
$firstTask = new CTask("austlii-edu-au-category-first", $firstParse);
$firstTask->attach($firstProccess);


$secondProccess = new CProccessObserver("二级分类");
$secondParse = new SecondParse();
$secondTask = new CTask("austlii-edu-au-category-second", $secondParse);
$secondTask->attach($secondProccess);
$secondTask->SetDataSave($firstDs);

$threeProccess = new CProccessObserver("三级分类");
$secondProccess->SetIsInline(false); //不在一行上显示进度；
$threeParse = new ThreeParse();
$threeTask = new CTask("austlii-edu-au-category-three", $threeParse);
$threeTask->SetDataSave($firstDs);
$threeTask->attach($threeProccess);

$observerDetailSecond = new CObserverDetail($secondTask);
$firstTask->attach($observerDetailSecond);
$observerDetailThree = new CObserverDetail($threeTask);
$secondTask->attach($observerDetailThree);

$threeTask->Run();
$secondTask->Run();
$firstTask->Run($firstDs);
?>;