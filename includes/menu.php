<?php

function renderMenu($menu_items, $parent, $class = ""){
	if(is_array($menu_items)){
		$s = "";
		foreach($menu_items as $key => $value){
			if(file_exists(APP_BASE_PATH.DIRECTORY_SEPARATOR."views".DIRECTORY_SEPARATOR."Styles".DIRECTORY_SEPARATOR."images".DIRECTORY_SEPARATOR."menu-icons".DIRECTORY_SEPARATOR."icon-".$key.".png")){
				$icon = '<img src="'.$GLOBALS["AppPath"].'/views/Styles/images/menu-icons/icon-'.$key.'.png" />';
			}
			else {
				$icon = '';
			}
			
			if(is_array($value)){
				$submenu = renderMenu($value, $key);
				if(trim($submenu) != ""){
					$s .= '<li class="MenuItem"><a href="#">'.$icon.'<span class="MenuItemText">'.GetLang($key).'</span></a>'.$submenu.'</li>';
				}
			}
			else {
				if(substr($value, 0, 1) == "#"){
					$s .= '<li class="MenuItem"><a data-scroll href="'.$GLOBALS["AppPath"].'/'.$value.'">'.$icon.'<span class="MenuItemText">'.GetLang($key).'</span></a></li>';
				}
				else {
					if(UserHasAccess($value)){
						$s .= '<li class="MenuItem"><a href="'.$GLOBALS["AppPath"].'/'.$value.'">'.$icon.'<span class="MenuItemText">'.GetLang($key).'</span></a></li>';
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