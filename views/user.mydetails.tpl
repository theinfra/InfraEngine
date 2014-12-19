%%Panel.HTMLHead%%
%%Panel.Header%%
%%Panel.MainMenuHorizontal%%
%%Panel.HeaderFlashMessages%%
<body>
<div class="UserMyDetailsForm">
<form action="%%GLOBAL_AppPath%%/user/mydetailssubmit" id="UserMyDetailsForm" method="POST">
	<table>
		<tr>
			<td>%%LNG_Username%%</td>
			<td><input type="text" id="UserMyDetailsUsername" name="UserMyDetailsUsername" value="%%GLOBAL_UserMyDetailsUsername%%" class="UserMyDetailsForm FormField FormFieldText UserMyDetailsUsername" /></td>
		</tr>
		<tr>
			<td>%%LNG_FirstName%%</td>
			<td><input type="text" id="UserMyDetailsFirstName" name="UserMyDetailsFirstName" value="%%GLOBAL_UserMyDetailsFirstName%%" class="UserMyDetailsForm FormField FormFieldText UserMyDetailsFirstName" /></td>
		</tr>
		<tr>
			<td>%%LNG_LastName%%</td>
			<td><input type="text" id="UserMyDetailsLastName" name="UserMyDetailsLastName" value="%%GLOBAL_UserMyDetailsLastName%%" class="UserMyDetailsForm FormField FormFieldText UserMyDetailsLastName" /></td>
		</tr>
		<tr>
			<td>%%LNG_Mail%%</td>
			<td><input type="text" id="UserMyDetailsMail" name="UserMyDetailsMail" value="%%GLOBAL_UserMyDetailsMail%%" class="UserMyDetailsForm FormField FormFieldText UserMyDetailsMail" /></td>
		</tr>
		<tr>
			<td>%%LNG_Password%%</td>
			<td><input type="password" id="UserMyDetailsPassword" name="UserMyDetailsPassword" class="UserMyDetailsForm FormField FormFieldText UserMyDetailsPassword" /></td>
		</tr>
		<tr>
			<td>%%LNG_PasswordConfirm%%</td>
			<td><input type="password" id="UserMyDetailsPasswordConfirm" name="UserMyDetailsPasswordConfirm" class="UserMyDetailsForm FormField FormFieldText UserMyDetailsPasswordConfirm" /></td>
		</tr>
		<tr>
			<td>%%LNG_Phone%%</td>
			<td><input type="text" id="UserMyDetailsPhone" name="UserMyDetailsPhone" value="%%GLOBAL_UserMyDetailsPhone%%" class="UserMyDetailsForm FormField FormFieldText UserMyDetailsPhone" /></td>
		</tr>
		<tr>
			<td><input type="reset" value="%%LNG_Reset%%" id="UserMyDetailsReset" name="UserMyDetailsReset" class="UserMyDetailsForm FormField FormFieldText UserMyDetailsReset" /></td>
			<td><input type="submit" value="%%LNG_Submit%%" id="UserMyDetailsSubmit" name="UserMyDetailsSubmit" class="UserMyDetailsForm FormField FormFieldText UserMyDetailsSubmit" /></td>
		</tr>
		</table> 
</form>
</div>
</body>