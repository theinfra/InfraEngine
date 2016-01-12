<?php

class APPCONTROLLERBASE_LOG extends APP_BASE {
	
	public $menu = array(
		"view" => 3,
		"remote" => 3,
	);
	
	function view(){
		$logs = GetModel('log');
		$logs = $logs->getResultSet(NULL, 20, NULL, array("logdate" => "DESC", "logid" => "ASC"));
		
		$logtable = "<table width=\"95%\" align=\"center\">";
		
		if(empty($logs)){
			$logtable = "<tr><td>".GetLang("NoLogsFound")."</td></tr>";
		}
		
		$altClass = "Odd";
		foreach($logs as $log){
			$logtable .= "<tr LogId=\"".$log["logid"]."\" class=\"".$altClass."\">
						<td>".sprintf("<a href=\"#\" onclick=\"ShowLogInfo('%d'); return false;\"><img id=\"LogExpand%d\" src=\"".$GLOBALS["AppPath"]."/images/plus.gif\" align=\"left\" width=\"19\" class=\"ExpandLink\" height=\"16\" title=\"%s\" border=\"0\"></a>", $log['logid'], $log['logid'], GetLang('ClickToViewLogInfo'))."</td>
						<td>".preg_replace('/\s\s+/', ' ', $log["logsummary"])."</td>
						<td>".$log["logseverity"]."</td>
						<td>".$log["logmodule"]."</td>
						<td>".date("j-M-Y G:i:s", $log["logdate"])."</td>
						<td><a class=\"LogDelete\" href=\"#\"><img src=\"".$GLOBALS["AppPath"]."/images/delicon.png\" /></a></td>
					</tr>
					<tr id=\"LogId".$log["logid"]."\" style=\"display: none;\">
						<td colspan=\"5\">".$log["logmsg"]."</td>		
					</tr>";
			
					$altClass = ($altClass == "Odd") ? "Even" : "Odd";
		}
		$logtable .= "</table>";
		
		$GLOBALS["LogViewLogTable"] = $logtable;
	}
	
	function remote_deletelog(){
		if(!isset($_GET["LogId"]) || !isId($_GET["LogId"])){
			AddLog(sprintf(GetLang("ErrorPostVarNotSet"), 'Log/RemoteDeleteLog', 'LogId'));
			echo app_json_encode(array("success" => 0, "msg" => GetLang("ErrorMsgGeneric")));
			exit;
		}
		
		$log_model = getModel("log");
		
		if(!$log_model->delete(array("logid" => $_GET["LogId"]))){
			AddLog(sprintf(GetLang("ErrorWhileDeletingLog"), $_GET["LogId"]). $GLOBALS["APP_CLASS_DB"]->GetError());
			echo app_json_encode(array("success" => 0, "msg" => GetLang("ErrorMsgGeneric")));
			exit;
		}
		else {
			echo app_json_encode(array("success" => 1));
			exit;			
		}
	}
	
	function remote_clearlog(){
		$model = getModel("log");
		if(!$GLOBALS["APP_CLASS_DB"]->DeleteQuery("log", "WHERE 1=1")){
			AddLog(GetLang("ErrorWhileClearLog"));
			echo app_json_encode(array("success" => 0, "msg" => GetLang("ErrorMsgGeneric")));
			exit;
		}
		
		echo app_json_encode(array("success" => 1));
		exit;
	}
}