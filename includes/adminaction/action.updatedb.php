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
			if(in_array($file, array('.', '..', 'base.php')) || is_dir($filepath) || !(strpos($file, 'model.') == '0')){
				continue;
			}
			
			$modelname = preg_replace('#model\.#', '', preg_replace('#\.php$#', '', $file));
			$model = getModel($modelname);
			
			$model->setAdminAction($this);
			
			$this->addToLog("Revisando entidad '".$modelname."'");
			if(!$model->checkModelSchema()){
				$this->addToLog(sprintf(GetLang('ErrorWhileCheckingSchema'), $modelname));
			}
		}
		return true;
	}
} 