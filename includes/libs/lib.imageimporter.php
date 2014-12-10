<?php

class APPLIB_IMAGEIMPORTER {
	
	private $basefile = "";
	private $filename ="";
	
	function loadFile($BaseFilePath, $BaseFileName){
		if(!file_exists($BaseFilePath)){
			return false;
		}
		
		$this->basefile = $BaseFilePath; 
		$this->filename = $BaseFileName;

		return true;
	}
	
	function copyOrig($destination){
		if(!is_dir($destination)){
			app_mkdir($destination, null, true);
		}
		
		copy($this->basefile, $destination.DIRECTORY_SEPARATOR.$this->filename);
	}
}