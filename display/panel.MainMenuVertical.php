<?php

class PANEL_MAINMENUVERTICAL extends AppPanel {
	
	public function SetPanelSettings(){
		$menu_items = GetConfig("MainMenu");
		
		if(isset($menu_items["LogInOut"])){
			unset($menu_items["LogInOut"]);
			if(getUserData()){
				$menu_items["LogOut"] = "user/logout";
			}
			else {
				$menu_items["LogIn"] = "user/login";
			}
		}
		
		$GLOBALS["MainMenuVertical"] = renderMenu($menu_items, "#", "MainMenuVerticalMenu");
	}
	
} 