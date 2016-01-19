<?php

require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'base.php');

class APPMODEL_USER extends APPMODELBASE
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
						"size" => 128,
						"null" => false,
				),
				"salt" => array(
						"type" => "varchar",
						"size" => 16,
						"null" => false,
				),
				"token" => array(
						"type" => "varchar",
						"size" => 32,
						"null" => true,
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
		);

		$tableName = "user";
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

}
