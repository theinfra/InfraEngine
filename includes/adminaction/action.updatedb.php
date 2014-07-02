<?php

require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'base.php');

class APPACTION_UPDATEDB extends ADMINACTIONBASE {
	
	public function actiondefault(){
		$GLOBALS['ADMINACTION_LOG']['UPDATEDB'] = array();
		if(!$this->CheckEntities()){
			$this->addToLog("Error al checar entidades");
			$this->printLog('UPDATEDB');
			exit;
		}
		
		$this->addToLog("Se termino de checar entidades");
		$this->printLog();
	}
	
	private function CheckEntities(){
		foreach(scandir(APP_BASE_PATH.DIRECTORY_SEPARATOR.'includes'.DIRECTORY_SEPARATOR.'entities') as $file){
			$filepath = APP_BASE_PATH.DIRECTORY_SEPARATOR.'includes'.DIRECTORY_SEPARATOR.'entities'.DIRECTORY_SEPARATOR.$file;
			if(in_array($file, array('.', '..', 'base.php')) || is_dir($filepath) || !(strpos($file, 'entity.') == '0')){
				continue;
			}
			
			$entityname = preg_replace('#entity\.#', '', preg_replace('#\.php$#', '', $file));
			$entity = getEntity($entityname);
			
			$entity->setAdminAction($this);
			
			$this->addToLog("Revisando entidad '".$entityname."'");
			if(!$entity->checkEntitySchema()){
				$this->addToLog(sprintf(GetLang('ErrorWhileCheckingSchema'), $entityname));
			}
		}
		return true;
	}
} 