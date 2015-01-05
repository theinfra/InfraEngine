<?php

class APPCONTROLLER_LOG extends APP_BASE {
	
	public $menu = array(
		"view" => 3,
	);
	
	function view(){
		$logs = GetModel('log');
		$logs = $logs->getResultSet(NULL, "*", NULL, array("logdate" => "DESC", "logid" => "ASC"));
		
		$logtable = "<table width=\"95%\" align=\"center\">";
		
		if(empty($logs)){
			$logtable = "<tr><td>".GetLang("NoLogsFound")."</td></tr>";
		}
		
		foreach($logs as $log){
			$logtable .= "<tr>
						<td>".sprintf("<a href=\"#\" onclick=\"ShowLogInfo('%d'); return false;\"><img id=\"LogExpand%d\" src=\"".$GLOBALS["AppPath"]."/images/plus.gif\" align=\"left\" width=\"19\" class=\"ExpandLink\" height=\"16\" title=\"%s\" border=\"0\"></a>", $log['logid'], $log['logid'], GetLang('ClickToViewLogInfo'))."</td>
						<td>".$log["logsummary"]."</td>
						<td>".$log["logseverity"]."</td>
						<td>".$log["logmodule"]."</td>
						<td>".date("j-M-Y G:i:s", $log["logdate"])."</td>
					</tr>
					<tr id=\"LogId".$log["logid"]."\" style=\"display: none;\">
						<td colspan=\"5\">".$log["logmsg"]."</td>		
					</tr>";
		}
		$logtable .= "</table>";
		
		$GLOBALS["LogViewLogTable"] = $logtable;
	}
}