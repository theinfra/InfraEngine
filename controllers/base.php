<?php

class APP_BASE{
	
	function __construct(){
		if (!isset($_SESSION)) {
			session_start();
		}
		$request = parseGetVars();
	}
	
	public function remote(){
		if(!isset($_GET['w']) || trim($_GET['w']) == ''){
			return '';
		}
	
		$function = 'remote_'.strtolower($_GET['w']);
	
		if(method_exists($this, $function)){
			$this->$function();
		}
	}
}