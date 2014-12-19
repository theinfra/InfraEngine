<?php

class APPCONTROLLER_LOG extends APP_BASE {
	
	public $menu = array(
		"view" => 3,
	);
	
	function view(){
		$logs = GetModel('log');
		$logs = $logs->getResultSet(NULL, NULL, NULL, array("logdate" => "DESC"));
		
		print_array($logs);
	}
}