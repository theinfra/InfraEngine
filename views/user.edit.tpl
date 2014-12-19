%%Panel.HTMLHead%%
%%Panel.Header%%
%%Panel.MainMenuHorizontal%%
%%Panel.HeaderFlashMessages%%
<body>
	<h1>%%LNG_UserEdit%%</h1>
	<p class="UserAdminActionMenu" id="UserAdminActionMenu">
		<ul>
			
		</ul>
	</p>
	<form action="%%GLOBAL_AppPath%%/user/editsubmit" method="POST">
	<input type="hidden" id="UserEditUserId" name="UserEditUserId" value="%%GLOBAL_UserEditUserId%%" />
	<table>
		<tr>
			<td>%%LNG_Username%%</td>
			<td><input type="text" id="UserEditUsername" name="UserEditUsername" value="%%GLOBAL_UserEditUsername%%" class="UserEditForm FormField FormFieldText UserEditUsername" /></td>
		</tr>
		<tr>
			<td>%%LNG_FirstName%%</td>
			<td><input type="text" id="UserEditFirstName" name="UserEditFirstName" value="%%GLOBAL_UserEditFirstName%%" class="UserEditForm FormField FormFieldText UserEditFirstName" /></td>
		</tr>
		<tr>
			<td>%%LNG_LastName%%</td>
			<td><input type="text" id="UserEditLastName" name="UserEditLastName" value="%%GLOBAL_UserEditLastName%%" class="UserEditForm FormField FormFieldText UserEditLastName" /></td>
		</tr>
		<tr>
			<td>%%LNG_Mail%%</td>
			<td><input type="text" id="UserEditMail" name="UserEditMail" value="%%GLOBAL_UserEditMail%%" class="UserEditForm FormField FormFieldText UserEditMail" /></td>
		</tr>
		<tr>
			<td>%%LNG_Password%%</td>
			<td><input type="password" id="UserEditPassword" name="UserEditPassword" class="UserEditForm FormField FormFieldText UserEditPassword" /></td>
		</tr>
		<tr>
			<td>%%LNG_PasswordConfirm%%</td>
			<td><input type="password" id="UserEditPasswordConfirm" name="UserEditPasswordConfirm" class="UserEditForm FormField FormFieldText UserEditPasswordConfirm" /></td>
		</tr>
		<tr>
			<td>%%LNG_Phone%%</td>
			<td><input type="text" id="UserEditPhone" name="UserEditPhone" value="%%GLOBAL_UserEditPhone%%" class="UserEditForm FormField FormFieldText UserEditPhone" /></td>
		</tr>
		<tr>
			<td>%%LNG_Status%%</td>
			<td>
				<select name="UserEditStatus" id="UserEditStatus">
					<option %%GLOBAL_UserEditStatus2selected%% value="1">%%LNG_Blocked%%</option>
					<option %%GLOBAL_UserEditStatus2selected%% value="2">%%LNG_Active%%</option>
				</select>
			</td>
		</tr>
		<tr>
			<td>%%LNG_MembershipType%%</td>
			<td>
				<select name="UserEditMembershipType" id="UserEditMembershipType">
					<option %%GLOBAL_UserEditMembershipType1selected%% value="1">%%LNG_UserMembership1%%</option>
					<option %%GLOBAL_UserEditMembershipType2selected%% value="2">%%LNG_UserMembership2%%</option>
					<option %%GLOBAL_UserEditMembershipType3selected%% value="3">%%LNG_UserMembership3%%</option>
					<option %%GLOBAL_UserEditMembershipType4selected%% value="4">%%LNG_UserMembership4%%</option>
					<option %%GLOBAL_UserEditMembershipType5selected%% value="5">%%LNG_UserMembership5%%</option>
					<option %%GLOBAL_UserEditMembershipType6selected%% value="6">%%LNG_UserMembership6%%</option>
					<option %%GLOBAL_UserEditMembershipType7selected%% value="7">%%LNG_UserMembership7%%</option>
				</select>
			</td>
		</tr>
		<tr>
			<td>%%LNG_UserGroup%%</td>
			<td>
				<select name="UserEditUserGroup" id="UserEditUserGroup">
					<option %%GLOBAL_UserEditUserGroup1selected%% value="1">%%LNG_UserGroup1%%</option>
					<option %%GLOBAL_UserEditUserGroup2selected%% value="2">%%LNG_UserGroup2%%</option>
					<option %%GLOBAL_UserEditUserGroup3selected%% value="3">%%LNG_UserGroup3%%</option>
				</select>
			</td>
		</tr>
		<tr>
			<td><input type="reset" value="%%LNG_Reset%%" id="UserEditReset" name="UserEditReset" class="UserEditForm FormField FormFieldText UserEditReset" /></td>
			<td><input type="submit" value="%%LNG_Submit%%" id="UserEditSubmit" name="UserEditSubmit" class="UserEditForm FormField FormFieldText UserEditSubmit" /></td>
		</tr>
		</table> 
	</form>
</body>