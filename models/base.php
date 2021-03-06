<?php

class APPMODELBASE
{
	const logError = false;

	/** @var Db hacking in a shortcut to $GLOBALS['APP_CLASS_DB'] for models since it's unlikely to change during script execution */
	protected $db;

	protected $error;
	protected $schema;
	protected $tableName;
	protected $primaryKeyName;
	protected $searchFields;
	protected $customKeyName;
	protected $useTransactions;
	protected $allowedSQLFunctions;
	
	protected $adminAction;

	/**
	 * Constructor
	 *
	 * Base constructor
	 *
	 * @access public
	 */
	public function __construct($schema=array(), $tableName="", $primaryKeyName="", $searchFields=array(), $customKeyName="")
	{
		$this->db = $GLOBALS['APP_CLASS_DB'];

		$this->schema = $schema;
		// ALWAYS MAKE THIS EQUAL TO THE MODEL NAME IN SINGULAR
		$this->tableName = $tableName;
		$this->primaryKeyName = $primaryKeyName;
		$this->searchFields = $searchFields;
		$this->customKeyName = $customKeyName;

		$this->error = "";
		$this->useTransactions = true;
		$this->allowedSQLFunctions = array(
				'LOWER',
				'UPPER',
				'TRIM'
		);
	}

	/**
	 * Get the last error message
	 *
	 * Method will return the last error message
	 *
	 * @access public
	 * @return string The last error message
	 */
	public function getError()
	{
		return $this->error;
	}

	/**
	 * Set the error message
	 *
	 * Method will set the error message
	 *
	 * @access protected
	 * @param string $error The error message
	 * @return void
	 */
	protected function setError($error)
	{
		$this->error = $error;
	}

	/**
	 * Build the WHERE clause
	 *
	 * Method will build the WHERE clause from either an ID, an array of IDs or an associative array
	 *
	 * @access protected
	 * @param mixed $nodeId The database record ID OR numeric array of IDs if multiple primary keys OR an associative
	 *                      array of fields to match up againt (fields must be in the record schema)
	 * @return string The WHERE clause on success, FALSE on error
	 */
	protected function buildPrimaryKeyClause($nodeId)
	{
		if (isId($nodeId) && !is_array($this->primaryKeyName)) {
			return $this->primaryKeyName . " = " . (int)$nodeId;
		} else if (is_array($nodeId) && !empty($nodeId)) {

			$whereClause = array();

			/**
			 * This will check for the multiple primary keys. This will not order them so make sure you put it in the
			 * same order as the $this->primaryKeyName array
			 */
			if (!is_associative_array($nodeId) && is_array($this->primaryKeyName)) {
				$keys = array_values($this->primaryKeyName);
				$idx = array_values($nodeId);

				foreach (array_keys($idx) as $k) {
					if (!isId($idx[$k])) {
						return false;
					}

					$whereClause[] = $keys[$k] . " = " . (int)$idx[$k];
				}

			/**
			 * Else this will build the where clause from an associative array. The column MUST be in the $this->schema
			 * array so no funny business
			 */
			} else if (is_associative_array($nodeId)) {
				foreach ($nodeId as $column => $value) {
					if (trim($column) == "" || trim($value) == "" || !array_key_exists($column, $this->schema)) {
						return false;
					}

					$whereClause[] = $column . " = '" . $GLOBALS["APP_CLASS_DB"]->Quote($value) . "'";
				}
			}

			$whereClause = implode(" AND ", $whereClause);
			return $whereClause;
		}

		return false;
	}

	/**
	 * Create the data array for the database
	 *
	 * Method will create the data array that will be used in the database
	 *
	 * @access private
	 * @param array $input The raw input array
	 * @return array The parsed array to be used in the database on success, FALSE on error
	 */
	protected function parseInput($input)
	{
		if (!is_array($input)) {
			return false;
		}

		$parsed = array();

		foreach ($input as $column => $value) {
			if (!array_key_exists($column, $this->schema)) {
				continue;
			}

			switch (app_strtolower($this->schema[$column]['type'])) {
				case "text":
					$value = (string)$value;
					break;

				case "date":
				case "int":
					$value = intval($value);
					break;
				case 'price':
				case 'measurement':
					$value = doubleval($value);
					break;
				case "bool":
					if (is_bool($value)) {
						$value = (int)$value;
					} else if ((string)$value == "1") {
						$value = 1;
					} else {
						$value = 0;
					}

					break;
				default:
					if (method_exists($this, "format" . $this->schema[$column]['type'] . "Hook")) {
						$methodName = "format" . $this->schema[$column] . "Hook";
						$value = $this->$methodName($value);
					}

					break;
			}

			if (method_exists($this, "parse" . $column . "Hook")) {
				$methodName = "parse" . $column . "Hook";
				$value = $this->$methodName($value);
			}

			$parsed[$column] = $value;
		}

		return $parsed;
	}

	/**
	 * Get a record
	 *
	 * Method will get a record
	 *
	 * @param mixed $nodeId The database record id - note this is written internally to work with arrays but pre- and postHooks are NOT so they will fail - use ids only only until this is fixed
	 * @return array The database record on success, FALSE on error
	 */
	public function get($nodeId)
	{
		if(!is_array($nodeId)){
			$nodeId = array($nodeId);
		}
		$whereClause = $this->buildPrimaryKeyClause($nodeId);

		if (trim($whereClause) == "") {
			return false;
		}

		/**
		 * Get the record. No need for a prehook as we have nothing to fo beforehand
		 */
		$query = "SELECT *
					FROM " . $this->tableName . "
					WHERE " . $whereClause;

		$result = $GLOBALS["APP_CLASS_DB"]->Query($query);
		$node = $GLOBALS["APP_CLASS_DB"]->Fetch($result);

		if (!is_array($node)) {
			return false;
		}

		/**
		 * Do we have a posthook method? If so then call it. If that fails then the whole thing fails. Use
		 * $node as a reference if you want
		 */
		if (method_exists($this, "getPosthook") && $this->getPosthook($nodeId, $node) === false) {
			return false;
		}

		return $node;
	}
	
	public function getResultSet($offset = 0, $amount = 10, $where = array(), $order = array(), $columns = array()){
			$query = "SELECT";
		if(!is_array($columns) || empty($columns)){
			$query .= " *";
		}
		else {
			$new_columns = array();
			foreach($columns as $alias => $column){
				if(is_string($alias)){
					$new_columns[] = " ".$column." AS '".$alias."'";
				}
				else {
					$new_columns[] = " ".$column;
				}
			}
			$query .= " " . implode(', ', $new_columns);
		}
		
		$query .= " FROM ".$this->tableName;
		
		if(is_array($where) && !empty($where)){
			$query .= " WHERE 1=1";
			foreach($where as $field => $value){
				$query .= " AND ".$field . " = '".$value."' ";
			}
		}
		else if(is_string($where) && trim($where) != ""){
			$query .= " WHERE ".$where;
		}
		
		if(is_array($order) && !empty($order)){
			foreach($order as $col => $dir){
				if(!in_array(strtoupper($dir), array("DESC", "DES", "ASC")) || !in_array($col, array_keys($this->schema))){
					unset($order[$col]);
				}
			}

			$query .= " ORDER BY ";
			foreach($order as $col => $dir){
				$query .= $col . " ".$dir. ", ";
			}
			$query = substr($query, 0, strlen($query)-2);
		}
		else if(is_string($order) && trim($order) != ""){
			$query .= " ORDER BY ".$order;
		}
		
		if ($amount == "*"){
			$query .= "";
		}
		else {
			if(is_numeric($offset)){
				if(is_int($amount)){
					$query .= " LIMIT ".$offset. ", ".$amount;
				}
				else {
					$query .= " LIMIT ".$offset. ", 10";
				}
			}
			else if(is_int($amount)){
				$query .= " LIMIT 0, ".$amount;
			}
			else {
				$query .= " LIMIT 0, 10";
			}
		}

		$result = $this->db->Query($query);
		if(!$result){
			return array();
		}
		
		$resultSet = array();
		
		while($row = $this->db->Fetch($result)){
			$resultSet[] = $row;
		}

		return $resultSet;
	}
	
	public function getSingleResultSet($offset = 0, $amount = 10, $where = array(), $order = array(), $columns = array()){
		$resultSet = $this->getResultSet($offset, $amount, $where, $order, $columns);
		
		if(isset($resultSet[0])){
			return $resultSet[0];
		}
		else {
			return false;
		}
	}

	/**
	 * Add a record
	 *
	 * Method will add a record. Doesn't need to be extended if you define your database record correctly
	 *
	 * @access public
	 * @param array $input The data array
	 * @return mixed The record ID if a primary key was defined, TRUE if not, FLASE on error
	 */
	public function add($input)
	{
		if (!is_array($input)) {
			return false;
		}

		$savedata = $this->parseInput($input);

		if (!is_array($savedata)) {
			return false;
		}

		if ($this->useTransactions) {
			$GLOBALS["APP_CLASS_DB"]->StartTransaction();
		}

		/**
		 * Do we have a prehook method? If so then call it. If that fails then the whole thing fails. The $savedata
		 * variable should be used as a reference
		 */
		if (method_exists($this, "addPrehook") && $this->addPrehook($savedata, $input) === false) {
			if ($this->useTransactions) {
				$GLOBALS["APP_CLASS_DB"]->RollbackTransaction();
			}

			return false;
		}

		/**
		 * Do the insert
		 */
		$nodeId = $GLOBALS["APP_CLASS_DB"]->InsertQuery($this->tableName, $savedata, true);

		if ($nodeId === false) {
			$error = $GLOBALS["APP_CLASS_DB"]->Error();
			$this->setError($error);

			if ($this->useTransactions) {
				$GLOBALS["APP_CLASS_DB"]->RollbackTransaction();
			}

			return false;
		}

		/**
		 * Do we have a posthook method? If so then call it. If that fails then the whole thing fails
		 */
		if (method_exists($this, "addPosthook") && $this->addPosthook($nodeId, $savedata, $input) === false) {
			if ($this->useTransactions) {
				$GLOBALS["APP_CLASS_DB"]->RollbackTransaction();
			}

			return false;
		}

		if ($this->useTransactions) {
			$GLOBALS["APP_CLASS_DB"]->CommitTransaction();
		}

		return $nodeId;
	}

	/**
	 * Edit a record
	 *
	 * Method will edit a record. Doesn't need to be extended if you define your database record correctly
	 *
	 * @access public
	 * @param array $input The data array
	 * @param mixed $nodeId The database record id - note this is written internally to work with arrays but pre- and postHooks are NOT so they will fail - use ids only only until this is fixed
	 * @return bool TRUE if the record was successfully updated, FALSE on error
	 */
	public function edit($input, $nodeId/*=null*/)
	{
		/*
		if (trim($nodeId) == "" && is_array($this->primaryKeyName) && array_key_exists($this->primaryKeyName, $input)) {
			$nodeId = $input[$this->primaryKeyName];
		}
		*/

		$whereClause = $this->buildPrimaryKeyClause($nodeId);

		if (trim($whereClause) == "" || !is_array($input)) {
			return false;
		}

		$savedata = $this->parseInput($input);

		if (!is_array($savedata)) {
			return false;
		}

		if ($this->useTransactions) {
			$GLOBALS["APP_CLASS_DB"]->StartTransaction();
		}

		/**
		 * Do we have a prehook method? If so then call it. If that fails then the whole thing fails. The $savedata
		 * variable should be used as a reference
		 */
		if (method_exists($this, "editPrehook") && $this->editPrehook($nodeId, $savedata, $input) === false) {
			if ($this->useTransactions) {
				$GLOBALS["APP_CLASS_DB"]->RollbackTransaction();
			}

			return false;
		}

		/**
		 * Do the update
		 */
		$rtn = $GLOBALS["APP_CLASS_DB"]->UpdateQuery($this->tableName, $savedata, $whereClause);

		if ($rtn === false) {
			$error = $GLOBALS["APP_CLASS_DB"]->Error();
			$this->setError($error);

			if ($this->useTransactions) {
				$GLOBALS["APP_CLASS_DB"]->RollbackTransaction();
			}

			return false;
		}

		/**
		 * Do we have a posthook method? If so then call it. If that fails then the whole thing fails
		 */
		if (method_exists($this, "editPosthook") && $this->editPosthook($nodeId, $savedata, $input) === false) {
			if ($this->useTransactions) {
				$GLOBALS["APP_CLASS_DB"]->RollbackTransaction();
			}

			return false;
		}

		if ($this->useTransactions) {
			$GLOBALS["APP_CLASS_DB"]->CommitTransaction();
		}

		return true;
	}

	/**
	 * Delete a record
	 *
	 * Method will delete a record
	 *
	 *
	 * @access public
	 * @param mixed $nodeId The database record id - note this is written internally to work with arrays but pre- and postHooks are NOT so they will fail - use ids only only until this is fixed
	 * @return bool TRUE if the record was successfully updated, FALSE on error
	 */
	public function delete($nodeId, $extraOption=false)
	{
		if(!is_array($nodeId)){
			$nodeId = array($nodeId);
		}
		
		$whereClause = $this->buildPrimaryKeyClause($nodeId);

		if (trim($whereClause) == "") {
			return false;
		}

		/**
		 * Get the record first as we might need the data in the posthook plus also we don't want to delete something that
		 * isn't there
		 */
		$node = $this->get($nodeId);

		if (!is_array($node)) {
			return false;
		}

		if ($this->useTransactions) {
			$GLOBALS["APP_CLASS_DB"]->StartTransaction();
		}

		/**
		 * Do we have a prehook method? If so then call it. If that fails then the whole thing fails
		 */
		if (method_exists($this, "deletePrehook") && $this->deletePrehook($nodeId, $node, $extraOption) === false) {
			if ($this->useTransactions) {
				$GLOBALS["APP_CLASS_DB"]->RollbackTransaction();
			}

			return false;
		}

		/**
		 * Do the delete
		 */
		$rtn = $GLOBALS["APP_CLASS_DB"]->DeleteQuery($this->tableName, "WHERE " . $whereClause);

		if ($rtn === false) {
			$error = $GLOBALS["APP_CLASS_DB"]->Error();
			$this->setError($error);

			if ($this->useTransactions) {
				$GLOBALS["APP_CLASS_DB"]->RollbackTransaction();
			}

			return false;
		}

		/**
		 * Do we have a posthook method? If so then call it. If that fails then the whole thing fails
		 */
		if (method_exists($this, "deletePosthook") && $this->deletePosthook($nodeId, $node) === false) {
			if ($this->useTransactions) {
				$GLOBALS["APP_CLASS_DB"]->RollbackTransaction();
			}

			return false;
		}

		if ($this->useTransactions) {
			$GLOBALS["APP_CLASS_DB"]->CommitTransaction();
		}

		return true;
	}

	/**
	 * Delete multiple records
	 *
	 * Method will delete multiple records
	 *
	 * @access public
	 * @param array $nodeIdx An array of node IDs
	 * @return bool TRUE if all the records were deleted, FALSE on error
	 */
	public function multiDelete($nodeIdx)
	{
		if (!is_array($nodeIdx)) {
			$nodeIdx = array($nodeIdx);
		}

		$nodeIdx = array_filter($nodeIdx, "isId");

		if (!is_array($nodeIdx) || empty($nodeIdx)) {
			return false;
		}

		foreach ($nodeIdx as $nodeId) {
			$this->delete($nodeId);
		}

		return true;
	}

	/**
	 * Generic search for an model
	 *
	 * Method will search for an model using the search fields in the associative $searchFields. The
	 * keys in the $searchFields array are the table column names and the value is the value to search
	 * for. The value can either be a string or an array with the key 'value' pointing to the value to
	 * search for and the optional 'func' pointing to the SQL function(s) to preform on the column
	 * (this can be an array of functions)
	 *
	 * If value, either the normal value or the 'value' key value, is an array, then the searchbe changed
	 * from an '=' is an 'IN()' match
	 *
	 * The $likeFields is an array containing the keys in $searchFields in where the search will be
	 * a 'LIKE' instead of an exact match.
	 *
	 * The $negateFields is an array containing the keys in $searchFields in where the will be a non
	 * equals ('!=' or 'NOT IN()'). If that same field is in the $likeFields array then the search will
	 * be 'NOT LIKE'
	 *
	 * The $formSession is an ID/array of form field session data to also match against
	 *
	 * @access public
	 * @param array $searchFields The search field associative array
	 * @param array $likeFields The optional array to turn the $searchFields into a LIKE search
	 * @param array $negateFields The optional array to turn the $searchFields into a non equals search
	 * @param mixed $formSession The form session ID/array to also match against
	 * @return int The matched model ID on success, FALSE if no match
	 */
	public function search($searchFields, $likeFields=array(), $negateFields=array(), $formSession=0)
	{
		if (!is_array($searchFields)) {
			return false;
		}

		/**
		 * First we need to filter out any keys in the $searchFields that are not contained in the
		 * $this->searchFields child array
		 */
		foreach (array_keys($searchFields) as $key) {
			if (in_array($key, $this->searchFields) == false) {
				unset($searchFields[$key]);
			}
		}

		if (empty($searchFields)) {
			return false;
		}

		/**
		 * Fix up our args before we start using them
		 */
		if (!is_array($likeFields)) {
			$likeFields = array();
		}

		if (!is_array($negateFields)) {
			$negateFields = array();
		}

		/**
		 * Now to contstruct there search clause. Fix up our args before we start using them
		 */
		$where = '';

		foreach ($searchFields as $column => $keyword) {

			$originalColumn = $column;

			/**
			 * Special case here for the $keyword. If the $keyword is an array then check for the 'value'
			 * and 'func' keys. If they are present then 'value' will be the search keyword and 'func'
			 * will be the SQL function(s) to preform on the column (this can be an array of functions)
			 */
			if (is_array($keyword)) {
				if (!array_key_exists("value", $keyword)) {
					continue;
				}

				if (array_key_exists("func", $keyword)) {
					if (!is_array($keyword["func"])) {
						$keyword["func"] = array($keyword["func"]);
					}

					foreach ($keyword["func"] as $func) {
						if (trim($func) == "") {
							continue;
						}

						if (!in_array($func, $this->allowedSQLFunctions)) {
							continue;
						}

						$column = $func . "(" . $column . ")";
					}
				}

				$keyword = $keyword["value"];
			}

			/**
			 * Ignore this if it is empty
			 */
			if (!is_array($keyword)) {
				$keyword = array($keyword);
			}

			$keyword = array_map("trim", $keyword);
			$keyword = array_filter($keyword);

			if (empty($keyword)) {
				continue;
			}

			/**
			 * Is this a 'LIKE' match?
			 */
			if (in_array($column, $likeFields) !== false) {

				$tmpWhere = array();

				foreach ($keyword as $key) {

					$clause = $column;

					/**
					 * Is this a 'NOT LIKE' match?
					 */
					if (in_array($originalColumn, $negateFields) !== false) {
						$clause .= " NOT";
					}

					$clause .= " LIKE '%" . $GLOBALS["APP_CLASS_DB"]->Quote($keyword) . "%'";
					$tmpWhere[] = $clause;
				}

				if (count($tmpWhere) == 1) {
					$where = " AND " . implode("", $tmpWhere) . " ";
				} else if (in_array($column, $negateFields) !== false) {
					$where .= " AND (" . implode(" AND ", $tmpWhere) . ") ";
				} else {
					$where .= " AND (" . implode(" OR ", $tmpWhere) . ") ";
				}

			} else {

				$tmpKeyword = array();

				foreach ($keyword as $key) {
					$tmpKeyword[] = "'" . $GLOBALS["APP_CLASS_DB"]->Quote($key) . "'";
				}

				$tmpKeyword = implode(",", $tmpKeyword);
				$where .= " AND " . $column;

				/**
				 * Else is it a negate (non equals) match?
				 */
				if (in_array($originalColumn, $negateFields) !== false) {
					if (count($keyword) > 1) {
						$where .= " NOT IN(" . $tmpKeyword . ") ";
					} else {
						$where .= " != " . $tmpKeyword;
					}

				/**
				 * Else its just a plain match
				 */
				} else {
					if (count($keyword) > 1) {
						$where .= " IN(" . $tmpKeyword . ") ";
					} else {
						$where .= " = " . $tmpKeyword;
					}
				}
			}
		}

		/**
		 * Just in case
		 */
		if ($where == '') {
			return false;
		}

		$query = "SELECT " . $this->primaryKeyName . " AS modelid";
/*
		if (trim($this->customKeyName) !== "") {
			$query .= ", " . $this->customKeyName . " AS formsessionid";
		}
*/
		$query .= "
					FROM " . $this->tableName . "
					WHERE 1=1 " . $where;

		/**
		 * OK, we have the search SQL. If we don't have to search through the saved form session data
		 * then just return the query result
		 */
		if ($this->customKeyName == "" || !is_array($formSession) || empty($formSession)) {
			return $GLOBALS["APP_CLASS_DB"]->FetchOne($query, "modelid");

		/**
		 * Else we need to loop through all the matches results and then match against their saved
		 * form session data (if any)
		 */
		} 
		/*
		else {
			$result = $GLOBALS["APP_CLASS_DB"]->Query($query);
			while ($row = $GLOBALS["APP_CLASS_DB"]->Fetch($result)) {

				$sessData = array();

				if (!is_array($sessData) || empty($sessData) || count($formSession) !== count($sessData)) {
					continue;
				}

				ksort($sessData);
				ksort($formSession);

				if ($sessData == $formSession) {
					return $row["modelid"];
				}
			}
		}
		*/

		return false;
	}
	
	public function setAdminAction($action){
		if(is_a($action, 'ADMINACTIONBASE')){
			$this->adminAction = $action;
		}
	}
	
	public function checkModelSchema(){
		if(!$GLOBALS["APP_CLASS_DB"]->tableExists($this->tableName)){
			$this->adminAction->addToLog(sprintf(GetLang('ErrorTableNotExists'), $this->tableName).APP_EOL);
			
			if(!$GLOBALS["APP_CLASS_DB"]->CreateTableForModel($this->tableName)){
				$this->adminAction->addToLog(sprintf(GetLang('ErrorCreatingTable'), $this->tableName).':'.$GLOBALS["APP_CLASS_DB"]->GetError().APP_EOL);
				return false;
			}
			else {
				$this->adminAction->addToLog("Se creo la tabla ".$this->tableName." que faltaba");
			}
		}
		
		foreach($this->schema as $fieldName => $fieldDetails){
			if(!$GLOBALS["APP_CLASS_DB"]->fieldExists($this->tableName, $fieldName, $fieldDetails)){
				if($GLOBALS["APP_CLASS_DB"]->GetError() != ''){
					$this->adminAction->addToLog("Ocurrio un error al revisar el campo ".$fieldName." de la tabla ".$this->tableName.". Error: ".$GLOBALS["APP_CLASS_DB"]->GetError());
				}
				
				if(!$GLOBALS["APP_CLASS_DB"]->createfield($this->tableName, $fieldName, $fieldDetails)){
					$this->adminAction->addToLog(sprintf(GetLang('ErrorCreatingField'), $fieldName, $this->tableName).". Error: ".$GLOBALS["APP_CLASS_DB"]->GetError());
				}
				else {
					$this->adminAction->addToLog("Se creo o edito el campo ".$fieldName." de la tabla ".$this->tableName." que faltaba");
				}
			}
		}
		
		if(!$GLOBALS["APP_CLASS_DB"]->checkTablePK($this->tableName, $this->primaryKeyName)){
			if(!$GLOBALS["APP_CLASS_DB"]->createTablePK($this->tableName, $this->primaryKeyName)){
				$this->adminAction->addToLog(sprintf(GetLang('ErrorWhileCreatingTablePK'), $this->tableName, implode(',', $this->primaryKeyName)).': "'.$GLOBALS["APP_CLASS_DB"]->GetError().'"');
			}
			else {
				$this->adminAction->addToLog("Se creo o edito la Llave Primaria (".implode(',', $this->primaryKeyName).") de la tabla ".$this->tableName." que faltaba");
			}
		}
		
		if(!$GLOBALS["APP_CLASS_DB"]->checkTableIndex($this->tableName, $this->searchFields, $this->schema)){
			$this->adminAction->addToLog(sprintf(GetLang('ErrorWhileCreatingTableIndex'), $this->tableName, implode(',', $this->searchFields)).': "'.$GLOBALS["APP_CLASS_DB"]->GetError().'"');
		}
		else {
			$this->adminAction->addToLog("Se revisaron las Llaves Indices (".implode(',', array_keys($this->searchFields)).") de la tabla ".$this->tableName." y se editaron si faltaban");
		}

		return true;
	}
	
	public function getTableName(){
		return $this->tableName;
	}
	
	public function getSchema(){
		return $this->schema;
	}
	
	public function getPrimaryKeyName(){
		return $this->primaryKeyName;
	}
	
	public function getSearchFields(){
		return $this->searchFields;
	}
	
	public function getCustomKeyName(){
		return $this->customKeyName;
	}
	
	public function getUseTransactions(){
		return $this->useTransactions;
	}
}
