<?php

class PANEL_MAINMENUHORIZONTAL extends AppPanel {
	
	public function SetPanelSettings(){
		$menu_items = array(
			"Home" => "index",
		);
		
		if(getUserData()){
			$menu_items["LogInOut"] = "user/logout";
		}
		else {
			$menu_items["LogInOut"] = "user/login";
		}
		
		$GLOBALS["MainMenuHorizontal"] = renderMenu($menu_items, "#");
	}
	
} 