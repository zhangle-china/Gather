<?php
class CImage{
	static $imageType = array(1 => "GIF",2 => "JPG",3 => "PNG",4 => "SWF",5 => "PSD",6 => "BMP",7 => "TIFF",8 => "TIFF",9 => "JPC","10" => "JP2",11 => "JPX",12 => "JB2",13 => "SWC",14 => "IFF",15 => "WBMP",16 => "XB");

	private static function CreateFileName($type,$fileName=""){
		$result = "";
		if(empty($fileName)) return time().rand(1, 999999).".$type";
		$index = strrpos($fileName, ".");
		$ext = substr($fileName, $index+1);
		$result = $fileName;
		if(strtolower($type) != strtolower($ext)){
			$result .= ".$type";
		}
		return $result;
	}
	
	static function CopyImage($source,$targetDir="",$targetFileName=""){
		if(empty($targetDir)) $targetDir = dirname(__FILE__);
		if(!is_dir($targetDir)) mkdir($targetDir,777,true);
		if(!$imageInfo = @getimagesize($source)) throw new Exception("源文件无效：$source",10003);
		list($w,$h,$type,$attr) = $imageInfo;
		$fileType = self::$imageType[$type];
		if(!isset($fileType)) throw new Exception("非法的文件类型",10001);
		$fileName = self::CreateFileName($fileType, $targetFileName);
		$targetFile = $targetDir."/".$fileName;
		if(file_exists($targetFile)) throw new Exception("目标文件已存在！",10000);
		if(@copy($source, $targetFile)) return $targetFile;
		throw new Exception("下载文件失败：$source",10002);	
	}
}