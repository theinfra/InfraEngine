<?php

function renderMenu($menu_items, $parent){
	if(is_array($menu_items)){
		$s = "";
		foreach($menu_items as $key => $value){
			if(is_array($value)){
				$submenu = renderMenu($value, $key);
				if(trim($submenu) != ""){
					$s .= "<li><a href=\"#\">".GetLang($key)."</a>".$submenu."</li>";
				}
			}
			else {
				if(UserHasAccess($value)){
					$s .= "<li><a href=\"".$GLOBALS["AppPath"]."/".$value."\">".GetLang($key)."</a></li>";
				}
			}
		}
		
		if(trim($s) == ""){
			return "";
		}
		$menu = "<ul";
		if($parent == "#"){
			$menu .= " class=\"MainMenuHorizontalMenu\"";
		}
		$menu .= ">".$s."</ul>";
		return $menu;
	}
}