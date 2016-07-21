<?php

class PANEL_HEADERFLASHMESSAGES extends AppPanel {
	
	public function SetPanelSettings(){
		if(!isset($_SESSION['APP_MESSAGES']) || !is_array($_SESSION['APP_MESSAGES']) || empty($_SESSION['APP_MESSAGES'])){
			$this->DontDisplay = true;
			return;
		}
		
		$panel = '';
		foreach($_SESSION['APP_MESSAGES'] as $msg){
			switch ($msg['sev']) {
				case APP_SEVERITY_SUCCESS :
					$GLOBALS['FlashMessageType'] = 'Success';
					break;
				case APP_SEVERITY_ERROR :
					$GLOBALS['FlashMessageType'] = 'Error';
					break;
				case APP_SEVERITY_WARNING :
					$GLOBALS['FlashMessageType'] = 'Warning';
					break;
				case APP_SEVERITY_NOTICE :
					$GLOBALS['FlashMessageType'] = 'Notice';
					break;
				case APP_SEVERITY_DEBUG :
					$GLOBALS['FlashMessageType'] = 'Debug';
					break;
			}
			
			if(trim($msg['msg']) == ''){
				$GLOBALS['FlashMessageMsg'] = GetLang('ErrorMsgGeneric');
			}
			else {
				$GLOBALS['FlashMessageMsg'] = $msg['msg'];
			}
			$panel .= $GLOBALS['APP_CLASS_VIEW']->GetSnippet('FlashMessage');
		}
		
		$GLOBALS['FlashMessagesContent'] = $panel;
		$_SESSION['APP_MESSAGES'] = array();
	}
}