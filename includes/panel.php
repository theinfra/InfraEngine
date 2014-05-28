<?php

class AppPanel {
	
	public $DontDisplay = false;
	
	public $_htmlFile;
	
	public function ParsePanel()
	{
		$htmlPanelData = '';
		$parsedPanelData = '';
	
		// check for file and load the contents
		if (file_exists($this->_htmlFile)) {
			if ($fp = @fopen($this->_htmlFile, 'rb')) {
				while (!feof($fp)) {
					$htmlPanelData .= fgets($fp, 4096);
				}
				@fclose($fp);
			}
		}
	
		// sets the local panel settings, defined within the extendee
		if (method_exists($this, 'SetPanelSettings')) {
			$this->SetPanelSettings();
		}
	
		// some panels require the option to return blank
		if (isset($this->DontDisplay) && $this->DontDisplay == true) {
			$parsedPanelData = '';
		} else {
			// parse panel as normal
			$parsedPanelData = $htmlPanelData;
		}
	
		return $parsedPanelData;
	}
	
	public function SetHTMLFile($HTMLFile)
	{
		$this->_htmlFile = $HTMLFile;
	}
	
}