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

</div> <!-- WideContent -->
</div> <!-- Container -->
</body>