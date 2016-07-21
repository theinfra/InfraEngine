<?php

class PANEL_LEFTCOLUMNFLASHMESSAGES extends AppPanel {
	
	public function SetPanelSettings(){
		$GLOBALS["APP_CLASS_VIEW"]->GetPanelContent("HeaderFlashMessages");		
	}
}