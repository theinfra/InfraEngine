%%Panel.HTMLHead%%
<body>
<div id="Container">
<div class="WideContent">
%%Panel.HeaderFlashMessages%%
<div class="WideContent">
	<div class="UserLoginForm">
		<form method="POST" action="%%GLOBAL_AppPath%%/user/loginsubmit" id="UserLoginForm" >
			%%LNG_Username%%: <input type="text" id="UserLoginUsername" name="UserLoginUsername" value="%%GLOBAL_UserLoginUsername%%" class="UserLoginUsername FormField FormFieldText" /><br />
			%%LNG_Password%%: <input type="password" id="UserLoginPassword" name="UserLoginPassword" class="UserLoginPassword FormField FormFieldText" /><br />
			<input type="submit" name="UserLoginSubmit" id="UserLoginSubmit" value="%%LNG_Submit%%" class="UserLoginSubmit FormField FormFieldSubmit" />
		</form>
	</div>
</div>

</div> <!-- WideContent -->
</div> <!-- Container -->
</body>