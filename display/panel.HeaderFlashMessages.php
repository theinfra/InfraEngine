<?php

class PANEL_HEADERFLASHMESSAGES extends AppPanel {
	
	public function SetPanelSettings(){
		if(!isset($GLOBALS['APP_MESSAGES']) || !is_array($GLOBALS['APP_MESSAGES']) || empty($GLOBALS['APP_MESSAGES'])){
			$this->DontDisplay = true;
			return;
		}
		
		$panel = '';
		foreach($GLOBALS['APP_MESSAGES'] as $msg){
			switch ($msg['sev']) {
				case APP_SEVERITY_SUCCESS :
					$GLOBALS['HeaderFlashMessageType'] = 'Success';
					break;
				case APP_SEVERITY_ERROR :
					$GLOBALS['HeaderFlashMessageType'] = 'Error';
					break;
				case APP_SEVERITY_WARNING :
					$GLOBALS['HeaderFlashMessageType'] = 'Warning';
					break;
				case APP_SEVERITY_NOTICE :
					$GLOBALS['HeaderFlashMessageType'] = 'Notice';
					break;
				case APP_SEVERITY_DEBUG :
					$GLOBALS['HeaderFlashMessageType'] = 'Debug';
					break;
			}
			
			if(trim($msg['msg']) == ''){
				$GLOBALS['HeaderFlashMessageMsg'] = GetLang('ErrorGeneric');
			}
			else {
				$GLOBALS['HeaderFlashMessageMsg'] = $msg['msg'];
			}
			$panel .= $GLOBALS['APP_CLASS_VIEW']->GetSnippet('HeaderFlashMessage');
		}
		
		$GLOBALS['HeaderFlashMessagesContent'] = $panel;
	}
}