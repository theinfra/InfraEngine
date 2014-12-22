<?php

class APPCONTROLLER_LOG extends APP_BASE {
	
	public $menu = array(
		"view" => 3,
	);
	
	function view(){
		$logs = GetModel('log');
		$logs = $logs->getResultSet(NULL, "*", NULL, array("logdate" => "DESC"));
		
		$logtable = "<table>";
		
		if(empty($logs)){
			$logtable = "<tr><td>".GetLang("NoLogsFound")."</td></tr>";
		}
		
		foreach($logs as $log){
			$logtable .= "<tr>
						<td>&nbsp;</td>
						<td>".$log["logsummary"]."</td>
						<td>".$log["logseverity"]."</td>
						<td>".$log["logmodule"]."</td>
						<td>".date("j-M-Y G:i:s", $log["logdate"])."</td>
					</tr>
					<tr id=\"LogId".$log["logid"]."\" style=\"display: none;\">
						<td>".$log["logmsg"]."</td>		
					</tr>";
		}
		$logtable .= "</table>";
		
		$GLOBALS["LogViewLogTable"] = $logtable;
	}
}