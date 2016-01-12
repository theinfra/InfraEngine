<?php

class APPCONTROLLERBASE_USER extends APP_BASE {
	
	public $menu = array(
		"view" => 1,
		"login" => 0,
		"logout" => 0,
		"loginsubmit" => 0,
		"admin" => 3,
		"create" => 3,
		"createsubmit" => 3,
		"delete" => 3,
		"edit" => 3,
		"editsubmit" => 3,
		"mydetails" => 1,
		"mydetailssubmit" => 1,
		"myreserves" => 1,
	);
	
	function view(){
		if(!$userdata = getUserData()){
			$this->login();
			$GLOBALS["AppRequestVars"][1] = "login";
			return;
		}
	}
	
	function login(){
		overwritePostToGlobalVars();
	}
	
	function loginsubmit(){
		  $postFields = array(
			'UserLoginUsername', 
		    'UserLoginPassword',
	    );
	    
	    foreach($postFields as $field){
	    	if(!isset($_POST[$field])){
	    		AddLog(sprintf(GetLang("ErrorPostVarNotSet"), 'User/Login', $field));
	    		flashMessage(GetLang("ErrorMsgGeneric"), APP_SEVERITY_ERROR);
	    		$GLOBALS['AppRequestVars'][1] = "create";
	    		overwritePostToGlobalVars();
	    		return;
	    	}
	    }
	    
	    $user_model = getModel("usuario");
	    $user = $user_model->getResultSet(0, 1, array("username" => $_POST["UserLoginUsername"]));

	    if(empty($user)){
	    	flashMessage(GetLang("ErrorNoUserPwd"), APP_SEVERITY_ERROR);
	    	header("Location: ".$GLOBALS["AppPath"]."/user/login");
	    	exit;
	    }
	    
	    $user = $user[0];
	    
	    if($user["password"] != appGeneratePasswordHash($_POST["UserLoginPassword"], $user["salt"])){
	    	flashMessage(GetLang("ErrorNoUserPwd"), APP_SEVERITY_ERROR);
	    	header("Location: ".$GLOBALS["AppPath"]."/user/login");
	    	exit;
	    }
	    
	    if($user["status"] != "2"){
	    	flashMessage(GetLang("ErrorUserNotActive"), APP_SEVERITY_ERROR);
	    	header("Location: ".$GLOBALS["AppPath"]."/user/login");
	    	exit;
	    }
	    
	    if(!trim($user['token'])) {
	    	$userToken = appGenerateUserToken();
	    	$user['token'] = $userToken;
	    	$user_model->edit(array("token" => $user["token"]), array("userid" => $user["userid"]));
	    }
	    
	    AppSetCookie("APP_TOKEN", $user['token'], time()+(3600*24*7), true);
	    
	    // Make the cookie accessible via PHP as well
	    $_COOKIE['APP_TOKEN'] = $user['token'];
	    
	    // Also store it in the session as well when we're transferring the session between domains
	    $_SESSION['APP_TOKEN'] = $user['token'];
	    
	    if (isset($_SESSION['LOGIN_REDIR']) && $_SESSION['LOGIN_REDIR'] != '') {
	    	$page = $_SESSION['LOGIN_REDIR'];
	    	unset($_SESSION['LOGIN_REDIR']);
	    	header(sprintf("Location: %s", $page));
	    }
	    else {
	    	header(sprintf("Location: %s/user/", $GLOBALS['AppPath']));
	    }
	    
	    die();
	     
	}
	
	function logout(){
		APPUnsetCookie("APP_TOKEN");
		unset($_COOKIE['APP_TOKEN']);
		unset($_SESSION['APP_TOKEN']);

		header("Location: ".$GLOBALS["AppPath"]);
		exit;
	}
	
	function admin(){
		$this->title = GetLang("Users");
		
		$users = getModel("usuario");
		$resultSet = $users->getResultSet(0, "*");
		
		if(!is_array($resultSet) || empty($resultSet)){
			$GLOBALS['UsersResultSetTable'] = GetLang("NoUsersFound");
			return;
		}

		$GLOBALS['UsersResultSetTable'] = '<table id="UserAdminTable" class="UserAdminTable">
	<thead>
		<tr>
			<th>Nombre</th>
			<th>Apellido</th>
			<th>Usuario</th>
			<th>Mail</th>
			<th>Telefono</th>
			<th>Estatus</th>
			<th>Tipo Membr.</th>
			<th>Fecha Renov.</th>
			<th>Grupo</th>
			<th>Accion</th>
		</tr>
	</thead>
	<tbody>';
		
		$usersTable = '';
		foreach($resultSet as $userRow){
			$usersTable .= "<tr>" . 
				"<td>" . $userRow['firstname'] . "</td>" . 
				"<td>" . $userRow['lastname'] . "</td>" .
				"<td>" . $userRow['username'] . "</td>" .
				"<td><a href=\"mailto:".$userRow['mail']."\">" . $userRow['mail'] . "</a></td>" .
				"<td>" . $userRow['phone'] . "</td>" .
				"<td>" . $userRow['status'] . "</td>" .
				"<td>" . GetLang('UserMembership'.$userRow['membershiptype']) . "</td>" .
				"<td>" . formatDateSpanish($userRow['nextdatemembership']) . "</td>" .
				"<td>" . $userRow['usergroup'] . "</td>" .
				"<td><a class=\"UserAdminUserDelete\" href=\"".$GLOBALS['AppPath']."/user/delete?userid=".$userRow['userid']."\">Eliminar</a> | <a href=\"".$GLOBALS['AppPath']."/user/edit?userid=".$userRow['userid']."\">Editar</a></td>" .
				"</tr>";
		}
		
		$GLOBALS['UsersResultSetTable'] .= $usersTable . '</tbody>
</table>';
	}
	
	function create(){
		$GLOBALS["ViewScripts"] .= '<script src="'.$GLOBALS["AppPath"].'/javascript/jquery-ui-datepicker.min.js"></script>';
		$GLOBALS["ViewStylesheet"] .= '<link rel="stylesheet" href="'.$GLOBALS["AppPath"].'/views/Styles/jquery-ui-datepicker/jquery-ui-datepicker.min.css">';
		overwritePostToGlobalVars();
	}
	
	function createsubmit(){
		    $postFields = array(
				'UserCreateUsername', 
			    'UserCreateFirstName',
			    'UserCreateLastName',
			    'UserCreateMail',
			    'UserCreatePassword', 
			    'UserCreatePasswordConfirm', 
			    'UserCreatePhone',
			    'UserCreateStatus',
			    'UserCreateMembershipType',
			    'UserCreateUserGroup',
		    	'UserNextDateMembership',
		    );
		    
		    foreach($postFields as $field){
		    	if(!isset($_POST[$field])){
		    		AddLog(sprintf(GetLang("ErrorPostVarNotSet"), 'User/CreateSubmit', $field));
		    		flashMessage(GetLang("ErrorMsgGeneric"), APP_SEVERITY_ERROR);
		    		$GLOBALS['AppRequestVars'][1] = "create";
		    		overwritePostToGlobalVars();
		    		return;
		    	}
		    }

		    if(!is_email_address($_POST['UserCreateMail'])){
		    	flashMessage(GetLang('InvalidEmail'), APP_SEVERITY_ERROR);
		    	$GLOBALS['AppRequestVars'][1] = 'create';
		    	overwritePostToGlobalVars();
		    	return;
		    }
		    
		    if($_POST['UserCreatePassword'] != $_POST['UserCreatePasswordConfirm']){
		    	flashMessage(GetLang('PasswordDontMatch'), APP_SEVERITY_ERROR);
		    	$GLOBALS['AppRequestVars'][1] = 'create';
		    	overwritePostToGlobalVars();
		    	return;		    	
		    }
		    
		    $salt = substr(md5(uniqid()), 0, 16);
		    $password = appGeneratePasswordHash($_POST['UserCreatePassword'], $salt);
		    
		    $newuser = array(
		    	'firstname' => $_POST['UserCreateFirstName'],
		    	'lastname' => $_POST['UserCreateLastName'],
		    	'mail' => $_POST['UserCreateMail'],
		    	'username' => $_POST['UserCreateUsername'],
		    	'password' => $password,
		    	'salt' => $salt,
		    	'phone' => $_POST['UserCreatePhone'],
		    	'status' => $_POST['UserCreateStatus'],
		    	'membershiptype' => $_POST['UserCreateMembershipType'],
		    	'usergroup' => $_POST['UserCreateUserGroup'],
		    	'nextdatemembership' => strtotime($_POST["UserNextDateMembership"]),
		    );
		    
			$user_model = GetModel('usuario');
			$userid = $user_model->add($newuser);
			
			if(isId($userid)){
				flashMessage(GetLang('UserCreatedSuccess'), APP_SEVERITY_SUCCESS);
				header("Location: ".$GLOBALS['AppPath']."/user/admin");
				exit;
			}
			else {
				AddLog(sprintf(GetLang("ErrorCreatingUser") .' - Array ('.print_r($newuser, true).')', 'User/CreateSubmit', $field), APP_SEVERITY_ERROR);
				flashMessage(GetLang('ErrorMsgGeneric'), APP_SEVERITY_SUCCESS);
				header("Location: ".$GLOBALS['AppPath']."/user/admin");
				exit;
			}
	}
	
	function delete(){
		if(!isset($_GET['userid']) || !isId($_GET['userid'])){
			flashMessage(GetLang('ErrorMsgGeneric'), APP_SEVERITY_ERROR);
			$GLOBALS['AppRequestVars'][1] = 'admin';
			return;
		}
		
		$GLOBALS['UserDeleteUserId'] = $_GET['userid'];
		
		if(!isset($_GET['confirm']) || $_GET['confirm'] != 1){
			return;
		}

		$user_model = GetModel('usuario');
		$delete = $user_model->delete($_GET['userid']);
		if(!$delete){
			AddLog(sprintf(GetLang('ErrorDeletingUser'), $_GET['userid'], $user_model->getError()));
			flashMessage(GetLang('ErrorMsgGeneric'), APP_SEVERITY_ERROR);
			header("Location: ".$GLOBALS['AppPath']."/user/admin");
			exit;
		}
		else {
			flashMessage(GetLang('UserDeleteSuccess'), APP_SEVERITY_SUCCESS);
			header("Location: ".$GLOBALS['AppPath']."/user/admin");
			exit;
		}
	}
	
	function edit(){
		$GLOBALS["ViewScripts"] .= '<script src="'.$GLOBALS["AppPath"].'/javascript/jquery-ui-datepicker.min.js"></script>';
		$GLOBALS["ViewStylesheet"] .= '<link rel="stylesheet" href="'.$GLOBALS["AppPath"].'/views/Styles/jquery-ui-datepicker/jquery-ui-datepicker.min.css">';
		overwritePostToGlobalVars();
		
		if(!isset($_GET['userid']) || !isId($_GET['userid'])){
			flashMessage(GetLang('ErrorMsgGeneric'), APP_SEVERITY_ERROR);
			$GLOBALS['AppRequestVars'][1] = 'admin';
			return;
		}
				
		$user_model = GetModel("usuario");
		$user = $user_model->get($_GET['userid']);

		$data = array(
				'UserEditUsername' => $user['username'],
				'UserEditFirstName' => $user['firstname'],
				'UserEditLastName' => $user['lastname'],
				'UserEditMail' => $user['mail'],
				'UserEditPassword' => "",
				'UserEditPasswordConfirm' => "",
				'UserEditPhone' => $user['phone'],
				'UserEditStatus' => $user['status'],
				'UserEditMembershipType' => $user['membershiptype'],
				'UserEditUserGroup' => $user['usergroup'],
				'UserNextDateMembership' => date("d/m/Y", $user['nextdatemembership']),
		);
		$GLOBALS['UserEditUserId'] = $_GET['userid'];
		
		overwritePostToGlobalVars($data);
		
	}
	
	function editsubmit(){
		if(!isset($_POST['UserEditUserId']) || !isId($_POST['UserEditUserId'])){
			AddLog(sprintf(GetLang("ErrorPostVarNotSet"), 'User/EditSubmit', 'UserEditUserId'));
			flashMessage(GetLang('ErrorMsgGeneric'), APP_SEVERITY_ERROR);
			header("Location: ".$GLOBALS["AppPath"]."/user/admin");
			exit;
		}
		
		$userid = $_POST['UserEditUserId'];
		
		$postFields = array(
				'UserEditUsername',
				'UserEditFirstName',
				'UserEditLastName',
				'UserEditMail',
				'UserEditPassword',
				'UserEditPasswordConfirm',
				'UserEditPhone',
				'UserEditStatus',
				'UserEditMembershipType',
				'UserEditUserGroup',
				'UserNextDateMembership',
		);
		
		foreach($postFields as $field){
			if(!isset($_POST[$field])){
				AddLog(sprintf(GetLang("ErrorPostVarNotSet"), 'User/EditSubmit', $field));
				flashMessage(GetLang("ErrorMsgGeneric"), APP_SEVERITY_ERROR);
				$GLOBALS['AppRequestVars'][1] = "edit";
				overwritePostToGlobalVars();
				return;
			}
		}
		
		if(!is_email_address($_POST['UserEditMail'])){
			flashMessage(GetLang('InvalidEmail'), APP_SEVERITY_ERROR);
			$GLOBALS['AppRequestVars'][1] = 'edit';
			overwritePostToGlobalVars();
			return;
		}
		
		if( (trim($_POST['UserEditPassword']) != '' || trim($_POST['UserEditPasswordConfirm']) != '') && $_POST['UserEditPassword'] != $_POST['UserEditPasswordConfirm']){
			flashMessage(GetLang('PasswordDontMatch'), APP_SEVERITY_ERROR);
			$GLOBALS['AppRequestVars'][1] = 'edit';
			overwritePostToGlobalVars();
			return;
		}

		$edituser = array(
				'firstname' => $_POST['UserEditFirstName'],
				'lastname' => $_POST['UserEditLastName'],
				'mail' => $_POST['UserEditMail'],
				'username' => $_POST['UserEditUsername'],
				'phone' => $_POST['UserEditPhone'],
				'status' => $_POST['UserEditStatus'],
				'membershiptype' => $_POST['UserEditMembershipType'],
				'usergroup' => $_POST['UserEditUserGroup'],
				'nextdatemembership' => strtotime($_POST["UserNextDateMembership"]),
		);
		
		if(trim($_POST['UserEditPassword']) != '' && trim($_POST['UserEditPasswordConfirm']) != ''){
			$salt = substr(md5(uniqid()), 0, 16);
			$password = appGeneratePasswordHash($_POST['UserEditPassword'], $salt);
				
			$edituser['password'] = $password;
			$edituser['salt'] = $salt;
		}
		
		$user_model = GetModel('usuario');
		$userid = $user_model->edit($edituser, array("userid" => $userid));

		if($userid){
			flashMessage(GetLang('UserEditSuccess'), APP_SEVERITY_SUCCESS);
			header("Location: ".$GLOBALS['AppPath']."/user/admin");
			exit;
		}
		else {
			AddLog(sprintf(GetLang("ErrorEditingUser") .' - Array ('.print_r($edituser, true).')', 'User/EditSubmit', $field), APP_SEVERITY_ERROR);
			flashMessage(GetLang('ErrorMsgGeneric'), APP_SEVERITY_SUCCESS);
			header("Location: ".$GLOBALS['AppPath']."/user/admin");
			exit;
		}
	}
	
	function mydetails(){
		$user = getUserData();
		if(!$user){
			$GLOBALS["AppRequestVars"][1] = "login";
			header("Location: ".$GLOBALS["AppPath"]."/user/login");
			exit;
		}
		
		if(isset($_POST) && !empty($_POST)){
			overwritePostToGlobalVars();
		}
		else {
			overwritePostToGlobalVars(array(
				"UserMyDetailsUsername" => $user["username"],
				"UserMyDetailsFirstName" => $user["firstname"],
				"UserMyDetailsLastName" => $user["lastname"],
				"UserMyDetailsMail" => $user["mail"],
				"UserMyDetailsPhone" => $user["phone"],
			));
		}
	}
	
	function mydetailssubmit(){
		$postFields = array(
			"UserMyDetailsUsername",
			"UserMyDetailsFirstName",
			"UserMyDetailsLastName",
			"UserMyDetailsMail",
			"UserMyDetailsPhone",
		);
		
		foreach($postFields as $field){
			if(!isset($_POST[$field])){
				AddLog(sprintf(GetLang("ErrorPostVarNotSet"), 'User/MyDetailsSubmit', $field));
				flashMessage(GetLang("ErrorMsgGeneric"), APP_SEVERITY_ERROR);
				$GLOBALS['AppRequestVars'][1] = "mydetails";
				overwritePostToGlobalVars();
				return;
			}
		}
		
		if(!is_email_address($_POST['UserMyDetailsMail'])){
			flashMessage(GetLang('InvalidEmail'), APP_SEVERITY_ERROR);
			$GLOBALS['AppRequestVars'][1] = 'mydetails';
			overwritePostToGlobalVars();
			return;
		}
		
		if( (trim($_POST['UserMyDetailsPassword']) != '' || trim($_POST['UserMyDetailsPasswordConfirm']) != '') && $_POST['UserMyDetailsPassword'] != $_POST['UserMyDetailsPasswordConfirm']){
			flashMessage(GetLang('PasswordDontMatch'), APP_SEVERITY_ERROR);
			$GLOBALS['AppRequestVars'][1] = 'mydetails';
			overwritePostToGlobalVars();
			return;
		}
		
		$edituser = array(
				'firstname' => $_POST['UserMyDetailsFirstName'],
				'lastname' => $_POST['UserMyDetailsLastName'],
				'mail' => $_POST['UserMyDetailsMail'],
				'username' => $_POST['UserMyDetailsUsername'],
				'phone' => $_POST['UserMyDetailsPhone'],
		);
		
		if(trim($_POST['UserMyDetailsPassword']) != '' && trim($_POST['UserMyDetailsPasswordConfirm']) != ''){
			$salt = substr(md5(uniqid()), 0, 16);
			$password = appGeneratePasswordHash($_POST['UserMyDetailsPassword'], $salt);
		
			$edituser['password'] = $password;
			$edituser['salt'] = $salt;
		}
		
		$user = getUserData();
		
		$user_model = GetModel('usuario');
		$userid = $user_model->edit($edituser, array("userid" => $user["userid"]));
		
		if($userid){
			flashMessage(GetLang('UserMyDetailsSuccess'), APP_SEVERITY_SUCCESS);
			header("Location: ".$GLOBALS['AppPath']."/user/mydetails");
			exit;
		}
		else {
			AddLog(sprintf(GetLang("ErrorEditingUser") .' - Array ('.print_r($edituser, true).')', 'User/EditSubmit', $field), APP_SEVERITY_ERROR);
			flashMessage(GetLang('ErrorMsgGeneric'), APP_SEVERITY_SUCCESS);
			header("Location: ".$GLOBALS['AppPath']."/user/mydetails");
			exit;
		}
	}
}