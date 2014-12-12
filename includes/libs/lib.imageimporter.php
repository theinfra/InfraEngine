<?php

class APPLIB_IMAGEIMPORTER {
	
	private $isLoaded = false;
	
	private $basefile = "";
	private $filename ="";
	
	public function __construct()
	{
		return function_exists('gd_info');
	}
	
	function loadFile($BaseFilePath, $BaseFileName){
		if(!file_exists($BaseFilePath)){
			return false;
		}
		
		$this->basefile = $BaseFilePath; 
		$this->filename = $BaseFileName;

		$this->isLoaded = true;
		return true;
	}
	
	function copyOrig($destination, $overrideName = ""){
		if(!$this->isLoaded){
			return false;	
		}
		
		if(!is_dir($destination)){
			if(!app_mkdir($destination, null, true)){
				return false;
			}
		}
		
		$filename = $this->filename;
		if(trim($overrideName) != ""){
			$filename = $overrideName;
		}
		
		copy($this->basefile, $destination.DIRECTORY_SEPARATOR.$filename);
	}
	
	function getFileExt(){
		if(!$this->isLoaded){
			return false;
		}
		
		$tmp = explode(".", $this->filename);
		$ext = app_strtolower($tmp[count($tmp)-1]);
		
		return $ext;
	}
	
	function getSrcImageWidth(){
		if(!$this->isLoaded){
			return false;
		}
		
		$ext = $this->getFileExt();
;
		if ($ext == "jpg") {
			$srcImg = imagecreatefromjpeg($this->basefile);
		} else if($ext == "gif") {
			$srcImg = imagecreatefromgif($this->basefile);
			if(!function_exists("imagegif")) {
				$gifHack = 1;
			}
		} else {
			$srcImg = imagecreatefrompng($this->basefile);
		}
		
		if(!$srcImg) {
			return false;
		}
		
		return @imagesx($srcImg);
	}
	
	function getSrcImageHeight(){
		if(!$this->isLoaded){
			return false;
		}
		
		$ext = $this->getFileExt();
		
		if ($ext == "jpg") {
			$srcImg = imagecreatefromjpeg($this->basefile);
		} else if($ext == "gif") {
			$srcImg = imagecreatefromgif($this->basefile);
			if(!function_exists("imagegif")) {
				$gifHack = 1;
			}
		} else {
			$srcImg = imagecreatefrompng($this->basefile);
		}
		
		if(!$srcImg) {
			return false;
		}
		
		return @imagesy($srcImg);
	}
	
	function createImageResize($destFile, $width, $height = null){
		if(!$this->isLoaded){
			return false;
		}
		
		$ext = $this->getFileExt();
		
		if ($ext == "jpg") {
			$srcImg = imagecreatefromjpeg($this->basefile);
		} else if($ext == "gif") {
			$srcImg = imagecreatefromgif($this->basefile);
			if(!function_exists("imagegif")) {
				$gifHack = 1;
			}
		} else {
			$srcImg = imagecreatefrompng($this->basefile);
		}
		
		if(!$srcImg) {
			return false;
		}
		
		$srcWidth = @imagesx($srcImg);
		$srcHeight = @imagesy($srcImg);
		
		// Make sure the dest has a constant height

		if($height == null){
			$height = $srcHeight * ($width / $srcWidth);
		}
		
		$destimg = imagecreatetruecolor($width, $height);
		if($ext == "gif" && !isset($gifHack)) {
			$colorTransparent = @imagecolortransparent($srcImg);
			@imagepalettecopy($srcImg, $destimg);
			@imagecolortransparent($destimg, $colorTransparent);
			@imagetruecolortopalette($destimg, true, 256);
		}
		else if($ext == "png") {
			@imagecolortransparent($destimg, @imagecolorallocate($destimg, 0, 0, 0));
			@imagealphablending($destimg, false);
		}
				
		imagecopyresampled($destimg, $srcImg, 0, 0, 0, 0, $width, $height, $srcWidth, $srcHeight);
		
		//if ($ext == "jpg") {
			@imagejpeg($destimg, $destFile, 100);
		/*} else if($ext == "gif") {
			if(isset($gifHack) && $gifHack == true) {
				$destFile = isc_substr($destFile, 0, -3)."jpg";
				@imagejpeg($destimg, $destFile, 100);
			}
			else {
				@imagegif($destimg, $destFile);
			}
		} else {
			@imagepng($destimg, $destFile);
		}
		*/
		app_chmod($destFile, "0644");
		
		return $destFile;
	}
}