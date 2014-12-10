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
	
	function copyOrig($destination){
		if(!$this->isLoaded){
			return false;	
		}
		
		if(!is_dir($destination)){
			app_mkdir($destination, null, true);
		}
		
		copy($this->basefile, $destination.DIRECTORY_SEPARATOR.$this->filename);
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
	
	function createImageResize($destFile, $width, $height){
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
		$destWidth = $width;
		$destHeight = $height;
		/*
		if($width > $AutodestSize) {
			$destWidth = $AutodestSize;
			$destHeight = ceil(($height*(($AutodestSize*100)/$width))/100);
			$height = $destHeight;
			$width = $destWidth;
		}
		
		if($height > $AutodestSize) {
			$destHeight = $AutodestSize;
			$destWidth = ceil(($width*(($AutodestSize*100)/$height))/100);
		}
		*/
		
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
				
		imagecopyresampled($destimg, $srcImg, 0, 0, 0, 0, $destWidth, $destHeight, $srcWidth, $srcHeight);
		
		if ($ext == "jpg") {
			@imagejpeg($destimg, $destFile, 100);
		} else if($ext == "gif") {
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
		
		app_chmod($destFile, "0644");
		
		return $destFile;
	}
}