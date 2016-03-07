<?php

class APP_BASE{
	protected  $requestvars = '';
	
	public $title = "";
	
	function __construct(){
		if (!isset($_SESSION)) {
			session_start();
		}
		$this->requestvars = parseGetVars();
	}
	
	public function getTitle(){
		return $this->title;
	}
	
	public function remote(){
		if(!isset($_GET['w']) || trim($_GET['w']) == ''){
			return '';
		}
	
		$function = 'remote_'.strtolower($_GET['w']);
	
		if(method_exists($this, $function)){
			$this->$function();
		}
		else {
			AddLog(sprintf(GetLang("ErrorRemoteFunctionNotFound"), $function, get_class($this)));
			echo app_json_encode(array("success" => "0", "msg" => GetLang("ErrorMsgGeneric")));
			exit;
		}
	}
}