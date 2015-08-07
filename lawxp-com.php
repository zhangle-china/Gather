<?php
define("DEBUG",true);
require_once 'init.php';

/**
 * 主表解析器
 * @author Administrator
 */
Class MyParse extends CParse{
	protected function DefaultCacheFile() {
		// TODO Auto-generated method stub
		return "lawxpcom";
	}

	public function getUrlContent($url) {
		// TODO Auto-generated method stub
		/*
		$splitIndex = strrpos($url, "?");
		$post = substr($url, $splitIndex+1);
		$url = substr($url,0,$splitIndex);
		
		$option[CURLOPT_POST] = 1;
		$option[CURLOPT_POSTFIELDS] = $post;
		*/
		return $this->XCurl($url, array());
	}

	/* (non-PHPdoc)
	 * @see CParse::ListUrlParse()
	 */
	public function ListUrlParse($content = null, $sourcePath = "") {
		// TODO Auto-generated method stub
		if($list = $this->ReadCache("list")) return $list;
		$list = array();
		$format = "http://www.lawxp.com/Lawyer/?ProvinceId=0&CityId=0&LawFieldId=0&CategoryId=0&Level=-1&pg=%d";
		for($i = 1;$i<8077;$i++){
			$list[] = sprintf($format,$i);
		}
		
		$this->Cache("list", $list);
		return $list;
	}

	public function ArcUrlParse($content, $sourcePath = "") {
		if(!$content && $sourcePath){
			 $content = $this->getUrlContent($sourcePath);
		}
		if(!$content) throw new Exception("提取文章地址失败！");
		if(!preg_match("~<div.*id=\"LawyerList_div\".*>(.+)<ul class='credit-page'>~isU", $content,$match)) throw new Exception("提取文章地址失败！");
		$content = $match[1];
		if(!preg_match_all("~<a.*href=\"(/Lawyer/uid\d+\.html)\".*>~isU", $content,$match)) throw  new Exception("提取文章地址失败！");
		
		$result =array_unique($match[1]);
		foreach($result as $key=>$v){
			$result[$key] = "http://www.lawxp.com".$v;
		}
	
		return $result;
		
	}

	public function ArcContentParse($content, $sourcePath = "") {
		if(!$content && $sourcePath){
			 $content = $this->getUrlContent($sourcePath);
		}
		if(!$content) throw new Exception("提取祥页内容失败！");
		if(!preg_match("~<!--img-->(.*)<!--内容-->~isU", $content,$match)) throw new Exception("解析内容失败！");
		$content = $match[1];
		
		$title = array("l_name","l_area","l_range",'l_photo',"l_phone");
		
		$value = array();
		preg_match("~<h1>(.*)</h1>~isU", $content,$match);
		$value[] = preg_replace("~\s~","", $match[1]);
		preg_match("~</h1>\s?<span>(.*)</span>~isU", $content,$match);
		$value[] = preg_replace("~\s~","", $match[1]);
		preg_match("~最擅长领域：(.*)</span>~isU", $content,$match);
		$value[] = strip_tags($match[1]);
		preg_match("~<div class=\"lawyer-js2\">.*<img src=\"(.*)\".*>~isU", $content,$match);
		
		$img = strip_tags($match[1]);
		if(!preg_match("~^http:.+~is",$img)){
			$img = "http://www.lawxp.com".$img;
		}
		try{
			$newimg = CImage::CopyImage($img,$this->downloadDir);
		}
		catch (Exception $e){
			$newimg = "";
		}
		$newimg && $img = basename($newimg);
		$value[] = $img; //照片
		
		
		
		$value[] = "";//电话为空
		$result = array("title"=>$title,"value"=>$value);

		return $result;
	}


}


/**
 * 从表解析器
 * @author Administrator
 *
 */
class MyDetailParse extends CParse{
	/* (non-PHPdoc)
	 * @see CParse::DefaultCacheFile()
	*/
	
	protected function DefaultCacheFile() {
		// TODO Auto-generated method stub
		return "lawxpcom";
	}
	
	public function getUrlContent($url) {
		// TODO Auto-generated method stub
		return $this->XCurl($url, array());
	}
	
	public function ListUrlParse($content = null, $sourcePath = "") {
		// TODO Auto-generated method stub
		if(!$this->dataSourceUrl) return array();
		$cacheKey = md5($this->dataSourceUrl);
		$list = $this->ReadCache($cacheKey);
		if($list) return $list;
		if(!preg_match("~/uid(\d+)\.html~",$this->dataSourceUrl,$match)) throw  new Exception("提取律师ID失败！");
		$uid =  $match[1];
		$url = "http://www.lawxp.com/M/Lawyer/Case.aspx?ajax=3&lawyerid=".$uid."&courtid=0&LawFieldId=0&pg=1";
		$content = $this->getUrlContent($url);
		if(!$content) throw new Exception("提取案例地址列表失败！");
		if(!preg_match("~共(\d+)条~", $content,$match)) throw  new Exception("提取案例地址列表 分页信息 失败！");
		$count = $match[1];
		$pagesCount = ceil($count / 10);
		$result = array();
		for($i=1 ;$i<=$pagesCount;$i++){
			$result[] = "http://www.lawxp.com/M/Lawyer/Case.aspx?ajax=3&lawyerid=".$uid."&courtid=0&LawFieldId=0&pg=".$i;
		}
		$this->Cache($cacheKey, $result);
		return $result;			
	}
	
	/* (non-PHPdoc)
	 * @see CParse::ArcUrlParse()
	*/
	public function ArcUrlParse($content, $sourcePath = "") {
		// TODO Auto-generated method stub
		if(!$content){
			$content = $this->getUrlContent($sourcePath);
		}
		if(!$content) throw new Exception("提取列表页内容失败！");
		if(!preg_match_all("~<a href=\"(/case/c\d+\.html)\".*>~isU", $content,$match)) throw new Exception("从列表页提取文章地址失败；");
		$result = array_unique($match[1]);
		foreach ($result as $key=>$v){
			$result[$key] = "http://www.lawxp.com/".$v;
		}
		return $result;
		
	}
	
	/* (non-PHPdoc)
	 * @see CParse::ArcContentParse()
	*/
	public function ArcContentParse($content, $sourcePath = "") {
		if(!$content){
			$content = $this->getUrlContent($sourcePath);
		}
		if(!$content) throw new Exception("提取详情页内容失败！");
		
		if(!preg_match('~<div class="mylnr-jianjie">(.*)<div class="mylnr-bt">~isU', $content,$match)) throw new Exception("解析案例信息失败");
		$content = $match[1];
		$title = array("title","anyou","shenlijigou","wenhao","wenshuleixing","shenjieriqi","shenlichengxu","shenlirenyuan");
		$title = array_merge($title, array_keys($this->extendData));
		preg_match("~案例标题：(.*)</span>~isU", $content,$match);
		$value[] = preg_replace("~\s~", "", $match[1]);
		preg_match("~案由：(.*)</span>~isU", $content,$match);
		$value[] = strip_tags($match[1]);
		preg_match("~审理机构：(.*)</span>~isU", $content,$match);
		$value[] = preg_replace("~<a.*a>~","",  $match[1]);
		preg_match("~文书字号：(.*)</span>~isU", $content,$match);
		$value[] = $match[1];
		preg_match("~文书类型：(.*)</span>~isU", $content,$match);
		$value[] = $match[1];
		preg_match("~审结日期：(.*)</span>~isU", $content,$match);
		$value[] = $match[1];
		preg_match("~审理程序：(.*)</span>~isU", $content,$match);
		$value[] = $match[1];
		preg_match("~审理人员：(.*)</span>~isU", $content,$match);	
		$value[] = $match[1];
		$value = array_merge($value,array_values($this->extendData));
		$result = array("title"=>$title,"value"=>$value);
		return $result;
	}
}

$observerProccess =  new CProccessObserver("案例");
$dds = new CMysqlDataSave("localhost", "root","xtqiqi","caiji","lowxpcom_d");
$dParse = new MyDetailParse();
$dTask = new  CTask("lawxp-com-d",$dParse);
$dTask->SetAllowErrNum(10);
$dTask->attach($observerProccess);
$dTask->Run($dds); //先启动从表采集器； 采集上次未釆完的从表信息；  如第一次启动次采集器，则不执行任何操作；

$mobserverProccess =  new CProccessObserver("律师");
$observerDetail = new CObserverDetail($dTask);

$ds = new CMysqlDataSave("localhost", "root","xtqiqi","caiji","lowxpcom_m");
$mParse = new MyParse();
$mTask = new CTask("lawxp-com-m",$mParse);
$mTask->SetAllowErrNum(10);
$mTask->SetDataSave($ds);
$mTask->attach($mobserverProccess);
$mTask->attach($observerDetail);
$mTask->Run();
?>