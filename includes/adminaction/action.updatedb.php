<?php

require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'base.php');

class APPACTION_UPDATEDB extends ADMINACTIONBASE {
	
	public function actiondefault(){
		$GLOBALS['ADMINACTION_LOG']['UPDATEDB'] = array();
		if(!$this->CheckModels()){
			$this->addToLog("Error al checar modelos");
			$this->printLog('UPDATEDB');
			exit;
		}
		
		$this->addToLog("Se termino de checar modelos");
		$this->printLog();
	}
	
	private function CheckModels(){
		foreach(getAllModels() as $modelname){
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