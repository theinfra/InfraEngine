<?php

function renderMenu($menu_items, $parent, $class = ""){
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
				if(substr($value, 0, 1) == "#"){
					$s .= "<li><a data-scroll href=\"".$GLOBALS["AppPath"]."/".$value."\">".GetLang($key)."</a></li>";
				}
				else {
					if(UserHasAccess($value)){
						$s .= "<li><a href=\"".$GLOBALS["AppPath"]."/".$value."\">".GetLang($key)."</a></li>";
					}
				}
			}
		}
		
		if(trim($s) == ""){
			return "";
		}
		$menu = "<ul";
		if($parent == "#"){
			$menu .= ' class="'.$class.'"';
		}
		$menu .= ">".$s."</ul>";
		return $menu;
	}
}