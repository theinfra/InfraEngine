%%Panel.HTMLHead%%
<body>
<div id="Container">
%%Panel.Header%%
<div class="WideContent">
<script type="text/javascript">
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
</script>
<div class="LogViewLogTable">
	%%GLOBAL_LogViewLogTable%%
</div>

<script lang="text/javascript">
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
		}
	}
);
});
</script>

</div> <!-- WideContent -->
</div> <!-- Container -->
</body>