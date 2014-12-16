<?php

class PANEL_MAINMENUHORIZONTAL extends AppPanel {
	
	public function SetPanelSettings(){
		$menu_items = array(
			"Home" => "index",
			"Reserve" => array(
				"All" => "item",
				"Space" => "item/espacio",
				"Equipment" => "item/equipo",
				"Addon" => "item/addon",
				),
			"MyReserves" => "reserve",
			"MyAccount" => "user",
			"Admin" => array(
				"Reserves" => "reserve/admin",
				"Items" => "item/admin",
				"Users" => "user/admin",
				"Reports" => "reports",
				"Settings" => "settings",
			),
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