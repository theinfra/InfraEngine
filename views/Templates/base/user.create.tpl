%%Panel.HTMLHead%%
<body>
<div id="Container">
%%Panel.Header%%
<div class="WideContent">
	<h1>%%LNG_UserCreate%%</h1>
	<p class="UserAdminActionMenu" id="UserAdminActionMenu">
		<ul>
			
		</ul>
	</p>
	<form action="%%GLOBAL_AppPath%%/user/createsubmit" method="POST">
	<table>
		<tr>
			<td>%%LNG_Username%%</td>
			<td><input type="text" id="UserCreateUsername" name="UserCreateUsername" value="%%GLOBAL_UserCreateUsername%%" class="UserCreateForm FormField FormFieldText UserCreateUsername" /></td>
		</tr>
		<tr>
			<td>%%LNG_FirstName%%</td>
			<td><input type="text" id="UserCreateFirstName" name="UserCreateFirstName" value="%%GLOBAL_UserCreateFirstName%%" class="UserCreateForm FormField FormFieldText UserCreateFirstName" /></td>
		</tr>
		<tr>
			<td>%%LNG_LastName%%</td>
			<td><input type="text" id="UserCreateLastName" name="UserCreateLastName" value="%%GLOBAL_UserCreateLastName%%" class="UserCreateForm FormField FormFieldText UserCreateLastName" /></td>
		</tr>
		<tr>
			<td>%%LNG_Mail%%</td>
			<td><input type="text" id="UserCreateMail" name="UserCreateMail" value="%%GLOBAL_UserCreateMail%%" class="UserCreateForm FormField FormFieldText UserCreateMail" /></td>
		</tr>
		<tr>
			<td>%%LNG_Password%%</td>
			<td><input type="password" id="UserCreatePassword" name="UserCreatePassword" class="UserCreateForm FormField FormFieldText UserCreatePassword" /></td>
		</tr>
		<tr>
			<td>%%LNG_PasswordConfirm%%</td>
			<td><input type="password" id="UserCreatePasswordConfirm" name="UserCreatePasswordConfirm" class="UserCreateForm FormField FormFieldText UserCreatePasswordConfirm" /></td>
		</tr>
		<tr>
			<td>%%LNG_Phone%%</td>
			<td><input type="text" id="UserCreatePhone" name="UserCreatePhone" value="%%GLOBAL_UserCreatePhone%%" class="UserCreateForm FormField FormFieldText UserCreatePhone" /></td>
		</tr>
		<tr>
			<td>%%LNG_Status%%</td>
			<td>
				<select name="UserCreateStatus" id="UserCreateStatus">
					<option %%GLOBAL_UserCreateStatus2selected%% value="1">%%LNG_Blocked%%</option>
					<option %%GLOBAL_UserCreateStatus1selected%% value="2">%%LNG_Active%%</option>
				</select>
			</td>
		</tr>
		<tr>
			<td>%%LNG_MembershipType%%</td>
			<td>
				<select name="UserCreateMembershipType" id="UserCreateMembershipType">
					<option %%GLOBAL_UserCreateMembershipType1selected%% value="1">%%LNG_UserMembership1%%</option>
					<option %%GLOBAL_UserCreateMembershipType2selected%% value="2">%%LNG_UserMembership2%%</option>
					<option %%GLOBAL_UserCreateMembershipType3selected%% value="3">%%LNG_UserMembership3%%</option>
					<option %%GLOBAL_UserCreateMembershipType4selected%% value="4">%%LNG_UserMembership4%%</option>
					<option %%GLOBAL_UserCreateMembershipType5selected%% value="5">%%LNG_UserMembership5%%</option>
					<option %%GLOBAL_UserCreateMembershipType6selected%% value="6">%%LNG_UserMembership6%%</option>
					<option %%GLOBAL_UserCreateMembershipType7selected%% value="7">%%LNG_UserMembership7%%</option>
				</select>
			</td>
		</tr>
		<tr>
			<td>%%LNG_NextDateMembership%%</td>
			<td><input type="text" id="UserNextDateMembership" name="UserNextDateMembership" value="%%GLOBAL_UserNextDateMembership%%" class="UserNextDateMembership FormField FormFieldText FormFieldDatePicker" /></td>
		</tr>
		<tr>
			<td>%%LNG_UserGroup%%</td>
			<td>
				<select name="UserCreateUserGroup" id="UserCreateUserGroup">
					<option %%GLOBAL_UserCreateUserGroup1selected%% value="1">%%LNG_UserGroup1%%</option>
					<option %%GLOBAL_UserCreateUserGroup2selected%% value="2">%%LNG_UserGroup2%%</option>
					<option %%GLOBAL_UserCreateUserGroup3selected%% value="3">%%LNG_UserGroup3%%</option>
				</select>
			</td>
		</tr>
		<tr>
			<td><input type="reset" value="%%LNG_Reset%%" id="UserCreateReset" name="UserCreateReset" class="UserCreateForm FormField FormFieldText UserCreateReset" /></td>
			<td><input type="submit" value="%%LNG_Submit%%" id="UserCreateSubmit" name="UserCreateSubmit" class="UserCreateForm FormField FormFieldText UserCreateSubmit" /></td>
		</tr>
		</table> 
	</form>
	<script type="text/javascript">
		$(".FormFieldDatepicker").datepicker({
			showOn: "button",
	    	buttonImage: "%%GLOBAL_AppPath%%/views/Styles/jquery-ui-datepicker/images/calendar.gif",
	    	buttonImageOnly: true,
	    	buttonText: "%%LNG_SelectDate%%",
	    	minDate: 0,
	    	dateFormat: 'dd-mm-yy'
		});
	</script>

</div> <!-- WideContent -->
</div> <!-- Container -->
</body>