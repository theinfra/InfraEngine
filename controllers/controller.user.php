<?php

class APPCONTROLLER_USER extends APP_BASE {
	
	protected $adminops = array(
		
	);
	
	function view(){
		$users = getModel('usuario');
		$result = $users->getResultSet(null, null, null, array("userid", "firstname", "lastname"));
		print_array($result);
		exit;
		
		if(!getUserRecord()){
			$this->viewAction = "login";
			return;
		}
	}
}