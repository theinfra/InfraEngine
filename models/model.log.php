<?php

require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'base.php');

class APPMODEL_LOG extends APPMODELBASE
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
				"logid" => array(
							"type" => "int",
							"size" => 11,
							"auto_increment" => true,
							"null" => false,
						),
				"logsummary" => array(
						"type" => "varchar",
						"size" => "50",
						"null" => false,
				),
				"logmsg" => array(
						"type" => "text",
						"null" => false,
				),
				"logseverity" => array(
						"type" => "smallint",
						"size" => 4,
						"null" => false,
				),
				"logmodule" => array(
						"type" => "varchar",
						"size" => 50,
						"null" => false,
				),
				"logdate" => array(
						"type" => "int",
						"size" => 11,
						"null" => false,
				),
		);

		$tableName = "log";
		$primaryKeyName = array(
				"logid",
				);
		$searchFields = array(
				"logseverity",
				"logdate",
				"logmodule",
				"logsummary",
		);
		
		$customKeyName = array();

		parent::__construct($schema, $tableName, $primaryKeyName, $searchFields, $customKeyName);
	}

}
