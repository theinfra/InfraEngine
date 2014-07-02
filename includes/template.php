<?php

class CLASS_TEMPLATE {
	private $tplData = '';
	private $tplName = '';
	
	function printdebug($debug){
		print "**********************************<br>";
		print $debug;
		print "**********************************<br>";
	}
	
	function parseTemplate($tplname = '', $return = false){
		if(trim($tplname) == ''){
			if ($return) {
				return '';
			} else {
				echo '';
			}
		}
		
		$this->tplData = file_get_contents(APP_BASE_PATH.DIRECTORY_SEPARATOR.'templates'.DIRECTORY_SEPARATOR.$tplname.'.tpl');

		$this->tplData = $this->parsePanels();
		$this->tplData = $this->ParseGL($this->tplData);
		
		if ($return) {
			return $this->tplData;
		} else {
			echo $this->tplData;
		}
	}
	
	public function ParseGL($TemplateData)
	{
		
		preg_match_all("/(?siU)(%%LNG_[a-zA-Z0-9_]{1,}%%)/", $TemplateData, $matches);
		foreach ($matches[0] as $key => $k) {
			$pattern1 = $k;
			$pattern2 = str_replace("%", "", $pattern1);
			$pattern2 = str_replace("LNG_", "", $pattern2);

			$lang = GetLang($pattern2);
			if ($lang != '') {
				$TemplateData = str_replace($pattern1, $lang, $TemplateData);
			}
		}
		
		$TemplateData = $this->Parse("GLOBAL_", $TemplateData, $GLOBALS);
		
		return $TemplateData;
	}
	
	public function GetPanelContent($PanelId)
	{
		$panelData = "";
		
		$panelTemplate = APP_BASE_PATH.DIRECTORY_SEPARATOR.'templates'.DIRECTORY_SEPARATOR.'Panels'.DIRECTORY_SEPARATOR.$PanelId.'.tpl';
		$panelLogic = APP_BASE_PATH.DIRECTORY_SEPARATOR.'display'.DIRECTORY_SEPARATOR.'panel.'.$PanelId.'.php';
		include_once(APP_BASE_PATH.DIRECTORY_SEPARATOR.'includes'.DIRECTORY_SEPARATOR.'panel.php');
		
		if (file_exists ( $panelLogic )) {
			$panelClass = strtoupper('PANEL_'.$PanelId);
			// Parse the PHP panel if it exists
			include_once ($panelLogic);
			$objPanel = new $panelClass ();
			$objPanel->SetHTMLFile ( $panelTemplate );
			
			// Otherwise we have to parse the actual panel
			$panelData = $objPanel->ParsePanel ();
		} else {
			$panelData = file_get_contents ( $panelTemplate );
		}

		$panelData = $this->ParseGL($panelData);
	
		return $panelData;
	}
	
	function Parse($prefix, $text, $replace){
		$matches = array();
		$output = $text;
		
		preg_match_all('/(?siU)(%%'.preg_quote($prefix).'[a-zA-Z0-9_\.]+%%)/', $text, $matches);

		foreach ($matches[0] as $key => $k) {
			$pattern1 = $k;
			$pattern2 = str_replace('%', '', $pattern1);
			$pattern2 = str_replace($prefix.'', '', $pattern2);
			
			if (is_array ( $replace ) && isset ( $replace [$pattern2] )) {
				$output = str_replace ( $pattern1, $replace [$pattern2], $output );
			} elseif (is_string ( $replace ) && method_exists ( $this, $replace )) {
				$result = $this->$replace ( $pattern2 );
				$output = str_replace ( $pattern1, $result, $output );
			} else {
				$output = str_replace ( $pattern1, '', $output );
			}
		}
		return $output;
	}
	
	function parsePanels(){
		return $this->Parse('Panel.', $this->tplData, 'GetPanelContent');
	}
	
	public function GetSnippet($SnippetId)
	{
		$snippetFile = APP_BASE_PATH.DIRECTORY_SEPARATOR.'templates'.DIRECTORY_SEPARATOR.'Snippets'.DIRECTORY_SEPARATOR.$SnippetId.'.tpl';
		if(!$snippetFile) {
			return "<div>[Snippet not found: '" . $PanelId . "']</div>";
		}
	
		$snippetData = file_get_contents($snippetFile);
		return $this->ParseGL($snippetData);
	}
	
}

?>