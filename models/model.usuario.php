<?php

require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'base.php');

class APPMODEL_USUARIO extends APPMODELBASE
{

	/**
	 * Constructor
	 *
	 * Base constructor
	 *
	 * @access public
	 */
	public function __construct()
	{
		$schema = array(
				"userid" => array(
							"type" => "int",
							"size" => 11,
							"auto_increment" => true,
							"null" => false,
						),
				"firstname" => array(
						"type" => "varchar",
						"size" => 50,
						"null" => false,
				),
				"lastname" => array(
						"type" => "varchar",
						"size" => 50,
						"null" => false,
				),
				"mail" => array(
						"type" => "varchar",
						"size" => 50,
						"null" => false,
				),
				"username" => array(
						"type" => "varchar",
						"size" => 50,
						"null" => false,
				),
				"password" => array(
						"type" => "varchar",
						"size" => 50,
						"null" => false,
				),
				"salt" => array(
						"type" => "varchar",
						"size" => 16,
						"null" => false,
				),
				"phone" => array(
						"type" => "varchar",
						"size" => 20,
						"null" => false,
				),
				"status" => array(
						"type" => "int",
						"size" => 11,
						"null" => false,
				),
				"membershiptype" => array(
						"type" => "int",
						"size" => 11,
						"null" => false,
					),
				"nextdatemembership" => array(
					"type" => "int",
					"size" => 11,
					"null" => true,
				),
				"usergroup" => array(
					"type" => "int",
					"size" => 11,
					"null" => false,
				),
				"usertoken" => array(
					"type" => "char",
					"size" => 30,
					"null" => true,
				),
		);

		$tableName = "usuario";
		$primaryKeyName = array(
				"userid",
				);
		$searchFields = array(
				"firstname",
				"lastname",
				"mail",
				"username",
				"status",	
		);
		
		$customKeyName = array();

		parent::__construct($schema, $tableName, $primaryKeyName, $searchFields, $customKeyName);
	}
	
	public function getByToken($token){
		return $GLOBALS["APP_CLASS_DB"]->FetchRow("SELECT * FROM usuario WHERE usertoken = '".$GLOBALS["APP_CLASS_DB"]->Quote($token)."'");
	}

}
