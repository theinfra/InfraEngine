<?php

class PANEL_MAINMENUHORIZONTAL extends AppPanel {
	
	public function SetPanelSettings(){
		$menu_items = array(
			GetLang("Home") => "index",
		);
		
		if(getUserData()){
			$menu_items[GetLang("LogOut")] = "user/logout";
		}
		else {
			$menu_items[GetLang("LogIn")] = "user/login";
		}
		
		$GLOBALS["MainMenuHorizontal"] = renderMenu($menu_items, "#");
	}
	
} 