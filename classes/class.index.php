<?php

class APPCLASS_INDEX extends APP_BASE {
	
	function view(){
		
		$GLOBALS['APP_CLASS_TEMPLATE']->parseTemplate('index');
	}
}