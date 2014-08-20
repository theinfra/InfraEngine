<?php

class APPCONTROLLER_INDEX extends APP_BASE {
	
	function view(){
		
		$GLOBALS['APP_CLASS_VIEW']->parseView('index');
	}
}