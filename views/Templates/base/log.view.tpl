%%Panel.HTMLHead%%
<body>
<div id="Container">
%%Panel.Header%%
<div class="WideContent">
<div class="LogViewActions">
	<ul>
		<li><input type="button" name="LogViewClearLog" id="LogViewClearLog" value="%%LNG_ClearLog%%" class="FormField FormFieldButton LogViewClearLog"/></li>
	</ul>
</div>
<div class="LogViewLogTable">
	%%GLOBAL_LogViewLogTable%%
</div>

<script lang="text/javascript">
function ShowLogInfo(id)
{
	if($("#LogId"+id).is(":visible")){
		$("#LogId"+id).hide();
		$("#LogExpand"+id).attr("src", "%%GLOBAL_AppPath%%/images/plus.gif");
	}
	else {
		$("#LogId"+id).show();
		$("#LogExpand"+id).attr("src", "%%GLOBAL_AppPath%%/images/minus.gif");		
	}
}

$('body').on('click', '.LogDelete', function(){
Vrow = $(this).parent().parent();
Vrow.addClass("LogSelected");
if(!confirm("%%LNG_WarningDeleteLog%%")){
	Vrow.removeClass("LogSelected");
	return;
}	

Vlogid = Vrow.attr("logid");
	
$.getJSON(
	"%%GLOBAL_AppPath%%/log/remote/",
	{
		w: "deletelog",
		LogId: Vlogid
	},
	function(data){
		if(data.success == 0){
			alert(data.msg);
		}
		else {
			Vrow.remove();
			$("#LogId"+Vlogid).remove();
		}
	}
);
});

$('body').on('click', '#LogViewClearLog', function(){
	if(confirm("%%LNG_WarningClearLog%%")){
		$.getJSON(
				"%%GLOBAL_AppPath%%/log/remote/",
				{
					w: "clearlog"
				},
				function(data){
					if(data.success == 0){
						alert(data.msg);
					}
					else {
						$(".LogViewLogTable tbody").empty();
					}
				}
		);
	}
});
</script>

</div> <!-- WideContent -->
</div> <!-- Container -->
</body>