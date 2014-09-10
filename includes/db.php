<?php

class Db {
	var $_Error = "";
	var $_ErrorLevel = "";
	
	var $connection = null;
	
	var $magic_quotes_runtime_on = false;

	var $EscapeChar = '`';
	
	var $_transaction_counter = 0;
	
	var $_transaction_names = array();
	
	var $_unbuffered_query = false;
	
	function Db($hostname='', $username='', $password='', $databasename='')
	{
		$this->use_real_escape = version_compare(PHP_VERSION, '4.3.0', '>=');
		$this->magic_quotes_runtime_on = get_magic_quotes_runtime();
	
		if ($hostname && $username && $databasename) {
			$connection = $this->Connect($hostname, $username, $password, $databasename);
			return $connection;
		}
		return null;
	}
	
	function Connect($hostname=null, $username=null, $password=null, $databasename=null)
	{
	
		if ($hostname === null && $username === null && $password === null && $databasename === null) {
			$hostname = $this->_hostname;
			$username = $this->_username;
			$password = $this->_password;
			$databasename = $this->_databasename;
		}
	
		if ($hostname == '') {
			$this->SetError('No server name to connect to');
			return false;
		}
	
		if ($username == '') {
			$this->SetError('No username name to connect to server '.$hostname.' with');
			return false;
		}
	
		if ($databasename == '') {
			$this->SetError('No database name to connect to');
			return false;
		}
	
		$connection_result = @mysql_connect($hostname, $username, $password, true);
		if (!$connection_result) {
			$this->SetError(mysql_error());
			return false;
		}
		$this->connection = &$connection_result;
	
		$db_result = @mysql_select_db($databasename, $connection_result);
		if (!$db_result) {
			$this->SetError('Unable to select database \''.$databasename.'\': '.mysql_error());
			return false;
		}
		$this->_hostname = $hostname;
		$this->_username = $username;
		$this->_password = $password;
		$this->_databasename = $databasename;
	
		return $this->connection;
	}
	
	function Disconnect($resource=null)
	{
		if ($resource === null) {
			$this->SetError('Resource is a null object');
			return false;
		}
		if (!is_resource($resource)) {
			$this->SetError('Resource '.$resource.' is not really a resource');
			return false;
		}
		$close_success = mysql_close($resource);
		if ($close_success) {
			$this->connection = null;
		}
		return $close_success;
	}
	
	function SetError($error='', $errorlevel=E_USER_ERROR, $query='')
	{
		$this->_Error = $error;
		$this->_ErrorLevel = $errorlevel;
	}
	
	function GetError(){
		return $this->_Error;
	}
	
	function Query($query='')
	{
		$query = trim($query);
	
		if (!$query) {
			$this->SetError('Query passed in is empty');
			return false;
		}
	
		if (!$this->connection) {
			$this->SetError('No valid connection');
			return false;
		}
		
		if (!$this->_unbuffered_query) {
			$result = mysql_query($query, $this->connection);
		} else {
			$result = mysql_unbuffered_query($query, $this->connection);
			$this->_unbuffered_query = false;
		}
	
		if (!$result) {
			$error = mysql_error($this->connection);
			$errno = mysql_errno($this->connection);
	
			$this->SetError($error, E_USER_ERROR, $query);

			return $result;
		}
		
		return $result;
	}
	
	function UnbufferedQuery($query='')
	{
		$this->_unbuffered_query = true;
		return $this->Query($query);
	}
	
	function Fetch($resource=null)
	{
		if ($resource === null) {
			$this->SetError('Resource is a null object');
			return false;
		}
		if (!is_resource($resource)) {
			$this->SetError('Resource '.$resource.' is not really a resource');
			return false;
		}
	
		if($this->magic_quotes_runtime_on) {
			return $this->StripslashesArray(mysql_fetch_assoc($resource));
		}
		else {
			return mysql_fetch_assoc($resource);
		}
	}
	
	function FetchOne($result=null, $item=null)
	{
		if ($result === null) {
			return false;
		}
		if (!is_resource($result)) {
			$result = $this->Query($result);
		}
		$row = $this->Fetch($result);
		if (!$row) {
			return false;
		}
		if ($item === null) {
			$item = key($row);
		}
		if (!isset($row[$item])) {
			return false;
		}
		if($this->magic_quotes_runtime_on) {
			$row[$item] = stripslashes($row[$item]);
		}
		return $row[$item];
	}
	
	function NextId($sequencename=false, $idcolumn='id')
	{
		if (!$sequencename) {
			return false;
		}
		$query = 'UPDATE '.$sequencename.' SET ' . $idcolumn . '=LAST_INSERT_ID(' . $idcolumn . '+1)';
		$result = $this->Query($query);
		if (!$result) {
			return false;
		}
		return mysql_insert_id($this->connection);
	}
	
	function FullText($fields=null, $searchstring=null, $booleanmode=false)
	{
		if ($fields === null || $searchstring === null) {
			return false;
		}
		if (is_array($fields)) {
			$fields = implode(',', $fields);
		}
		if ($booleanmode) {
			$query = 'MATCH ('.$fields.') AGAINST (\''.$this->Quote($this->CleanFullTextString($searchstring)).'\' IN BOOLEAN MODE)';
		} else {
			$query = 'MATCH ('.$fields.') AGAINST (\''.$this->Quote($searchstring).'\')';
		}
		return $query;
	}
	
	function CleanFullTextString($searchstring)
	{
		$searchstring = strtolower($searchstring);
		$searchstring = str_replace("%", "\\%", $searchstring);
		$searchstring = preg_replace("#\*{2,}#s", "*", $searchstring);
		$searchstring = preg_replace("#([\[\]\|\.\,:])#s", " ", $searchstring);
		$searchstring = preg_replace("#\s+#s", " ", $searchstring);
	
		if (substr_count($searchstring, '"') % 2 != 0) {
			// ISC-1412: odd number of double quote present, strip all
			$searchstring = str_replace('"', '', $searchstring);
		}
	
		$words = array();
	
		// Does this search string contain one or more phrases?
		$quoted_string = false;
		if (strpos($searchstring, "\"") !== false) {
			$quoted_string = true;
		}
		$in_quote = false;
		$searchstring = explode("\"", $searchstring);
		foreach ($searchstring as $phrase) {
			$phrase = trim($phrase);
			if ($phrase != "") {
				if ($in_quote == true) {
					$words[] = "\"{$phrase}\"";
				} else {
					$split_words = preg_split("#\s{1,}#", $phrase, -1);
					if (!is_array($split_words)) {
						continue;
					}
	
					foreach ($split_words as $word) {
						if (!$word) {
							continue;
						}
						$words[] = trim($word);
					}
				}
			}
			if ($quoted_string) {
				$in_quote = !$in_quote;
			}
		}
		$searchstring = ''; // Reset search string
		$boolean = '';
		$first_boolean = '';
		foreach ($words as $k => $word) {
			if ($word == "or") {
				$boolean = "";
			} else if ($word == "and") {
				$boolean = "+";
			} else if ($word == "not") {
				$boolean = "-";
			} else {
				$searchstring .= " ".$boolean.$word;
				$boolean = '';
			}
			if ($k == 1) {
				if ($boolean == "-") {
					$first_boolean = "+";
				} else {
					$first_boolean = $boolean;
				}
			}
		}
		$searchstring = $first_boolean.trim($searchstring);
		return $searchstring;
	}
	
	function AddLimit($offset=0, $numtofetch=0)
	{
		$offset = intval($offset);
		$numtofetch = intval($numtofetch);
	
		if ($offset < 0) {
			$offset = 0;
		}
		if ($numtofetch <= 0) {
			$numtofetch = 10;
		}
		$query = ' LIMIT '.$offset.', '.$numtofetch;
		return $query;
	}
	
	function FreeResult($resource=null)
	{
		if ($resource === null) {
			$this->SetError('Resource is a null object');
			return false;
		}
		if (!is_resource($resource)) {
			$this->SetError('Resource '.$resource.' is not really a resource');
			return false;
		}
		$result = mysql_free_result($resource);
		return $result;
	}
	
	function CountResult($resource=null)
	{
		if ($resource === null) {
			$this->SetError('Resource is a null object');
			return false;
		}
		
		if (!is_resource($resource)) {
			$resource = $this->Query($resource);
		}

		$count = mysql_num_rows($resource);
		return $count;
	}
	
	function NumAffected($null=null)
	{
		return mysql_affected_rows($this->connection);
	}
	
	function Concat()
	{
		$num_args = func_num_args();
		if ($num_args < 1) {
			return func_get_arg(0);
		}
		$all_args = func_get_args();
		$returnstring = 'CONCAT('.implode(',', $all_args).')';
		return $returnstring;
	}
	
	function Quote($var='')
	{
		if (is_string($var) || is_numeric($var) || is_null($var)) {
			if ($this->use_real_escape) {
				return mysql_real_escape_string($var, $this->connection);
			} else {
				return mysql_escape_string($var, $this->connection);
			}
		} else if (is_array($var)) {
			return array_map(array($this, 'Quote'), $var);
		} else if (is_bool($var)) {
			return (int) $var;
		} else {
			trigger_error("Invalid type passed to DB quote ".gettype($var), E_USER_ERROR);
			return false;
		}
	}
	
	function LastId($seq='')
	{
		return mysql_insert_id($this->connection);
	}
	
	function CheckSequence($seq='')
	{
		if (!$seq) {
			return false;
		}
		$query = "SELECT COUNT(*) AS count FROM " . $seq;
		$count = $this->FetchOne($query, 'count');
		if ($count == 1) {
			return true;
		}
		return false;
	}
	
	function ResetSequence($seq='', $newid=0)
	{
		if (!$seq) {
			return false;
		}
	
		$newid = (int)$newid;
		if ($newid <= 0) {
			return false;
		}
	
		$query = "TRUNCATE TABLE " . $seq;
		$result = $this->Query($query);
		if (!$result) {
			return false;
		}
	
		// since a sequence table only has one field, we don't care what the fieldname is.
		$query = "INSERT INTO " . $seq . " VALUES (" . $newid . ")";
		$result = $this->Query($query);
		if (!$result) {
			return false;
		}
	
		return $this->CheckSequence($seq);
	}
	
	function OptimizeTable($tablename='')
	{
		if (!$tablename) {
			return false;
		}
		$query = "OPTIMIZE TABLE " . $tablename;
		return $this->Query($query);
	}
	
	function FetchRow($query)
	{
		if (empty($query)) {
			return false;
		}
		$result = $this->Query($query);
		return $this->Fetch($result);
	}
	
	function InsertQuery($table, $values, $useNullValues=false)
	{
		$keys = array_keys($values);
		$fields = implode($this->EscapeChar.",".$this->EscapeChar, $keys);
	
		foreach ($keys as $key) {
	
			if ($useNullValues) {
				if (is_null($values[$key])) {
					$values[$key] = "NULL";
				} else {
					$values[$key] = "'" . $this->Quote($values[$key]) . "'";
				}
			} else {
				$values[$key] = "'" . $this->Quote($values[$key]) . "'";
			}
		}
	
		$values = implode(",", $values);
		$query = sprintf('INSERT INTO %1$s%2$s%1$s (%1$s%3$s%1$s) VALUES (%4$s)', $this->EscapeChar, $table, $fields, $values);
	
		if ($this->Query($query)) {
			// only return last id if it contains a valid value, otherwise insertquery reports as failed if it returns a false value (0, null etc)
			$lastId = $this->LastId();
			if ((int)$lastId > 0) {
				return $lastId;
			}
			else {
				return true;
			}
		}
		else {
			return false;
		}
	}
	
	function UpdateQuery($table, $values, $where="", $useNullValues=false)
	{
		$fields = array();
		foreach ($values as $k => $v) {
	
			if ($useNullValues) {
				if (is_null($v)) {
					$v = "NULL";
				} else {
					$v = "'" . $this->Quote($v) . "'";
				}
			} else {
				$v = "'" . $this->Quote($v) . "'";
			}
	
			$fields[] = $k . "=" . $v;
		}
		$fields = implode(", ", $fields);
		if ($where != "") {
			$fields .= " WHERE " . $where;
		}
	
		$query = "UPDATE " . $table . " SET " . $fields;
		if ($this->Query($query)) {
			return true;
		}
		else {
			return false;
		}
	}
	
	function DeleteQuery($table='', $query=null, $limit=0)
	{
		if ($query === null) {
			return false;
		}
	
		$limit = intval($limit);
	
		if ($limit < 0) {
			return false;
		}
	
		$query = 'DELETE FROM ' . $table . ' ' . $query;
	
		if ($limit > 0) {
			$query .= ' LIMIT ' . $limit;
		}
	
		return $this->Query($query);
	}
	
	function StartTransaction()
	{
		/**
		 * If there are no transactions open, start one up.
		 */
		if ($this->_transaction_counter == 0) {
			$this->_transaction_counter++;
			return (bool)$this->Query("START TRANSACTION");
		}
	
		/**
		 * If there is a transaction open, work out a new "name" and issue a "SAVEPOINT" command.
		 */
		$name = $this->_generate_transaction_name();
		$this->_transaction_counter++;
		return (bool)$this->Query("SAVEPOINT " . $name);
	}
	
	function CommitAllTransactions()
	{
		$this->_transaction_counter = 0;
		$this->_transaction_names = array();
		return (bool)$this->Query("COMMIT");
	}
	
	function CommitTransaction()
	{
		/**
		 * If there are no transactions open, return false.
		 */
		if ($this->_transaction_counter < 1) {
			return false;
		}
	
		if ($this->_transaction_counter == 1) {
			$this->_transaction_counter--;
			return (bool)$this->Query("COMMIT");
		}
	
		/**
		 * If we're in a transaction, all we need to do is get rid of the last 'savepoint' name
		 * We can't actually "commit" a savepoint.
		 */
		$name = array_pop($this->_transaction_names);
		$this->_transaction_counter--;
		return true;
	}
	
	function RollbackTransaction()
	{
		/**
		 * If there are no transactions open, return false.
		 */
		if ($this->_transaction_counter < 1) {
			return false;
		}
	
		if ($this->_transaction_counter == 1) {
			$this->_transaction_counter--;
			return (bool)$this->Query("ROLLBACK");
		}
	
		$this->_transaction_counter--;
		$name = array_pop($this->_transaction_names);
		return (bool)$this->Query("ROLLBACK TO SAVEPOINT " . $name);
	}
	
	function RollbackAllTransactions()
	{
		$this->_transaction_counter = 0;
		$this->_transaction_names = array();
		return (bool)$this->Query("ROLLBACK");
	}
	
	function _generate_transaction_name()
	{
		while (true) {
			$name = uniqid('LongviewPriceLists');
			if (!in_array($name, $this->_transaction_names)) {
				$this->_transaction_names[] = $name;
				return $name;
			}
		}
	}
	
	function Version()
	{
		$result = $this->Query("SELECT VERSION()");
		return $this->FetchOne($result);
	}
	
	function SubString($str = '', $from = 1, $len = 1)
	{
		if ($str == '') {
			return '';
		}
		return " SUBSTRING(".$this->Quote($str).", $from, $len) ";
	}
	
	function StripslashesArray($value)
	{
		if(is_array($value)) {
			$value = array_map(array($this, 'StripslashesArray'), $value);
		}
		else {
			$value = stripslashes($value);
		}
		return $value;
	}
	
	function tableExists($table){
		$result = $this->Query('SELECT 1 FROM '.$table);
		if($result){
			return true;
		}
		else {
			return false;
		}
	}
	
	function CreateTableForModel($modelname){
		$model = getModel($modelname);
		if(!$model){
			return false;
		}
		
		$tablename = $model->getTableName();
		$tablefields = $model->getSchema();
		$tableprimarykey = $model->getPrimaryKeyName();
		$query = 'CREATE TABLE IF NOT EXISTS `'.$tablename.'` (';
		
		foreach($tablefields as $field => $definition){
			$query .= "\t`".$field."` ";
			$query .= $definition['type'];
			if(isset($definition['size'])) $query .= '('.$definition['size'].')';
			
			if(isset($definition['null']) && $definition['null']){
				$query .= ' NULL';
			}
			else {
				$query .= ' NOT NULL';
			}
			
			if(isset($definition['auto_increment']) && $definition['auto_increment']){
				$query .= ' AUTO_INCREMENT';
			}
			
			$query .= ','.PHP_EOL;
		}
		
		$query .= "\tPRIMARY KEY (`";
		$query .= implode('`, `', $tableprimarykey);
		$query .= "`)".PHP_EOL;
		
		$query .= ')'.PHP_EOL;
		if($model->getUseTransactions()){
			$query .= "\tENGINE=InnoDB";
		}
		else {
			$query .= "\tENGINE=MyISAM";
		}

		$query .= "\tCHARSET=utf8 COLLATE utf8_general_ci".PHP_EOL;
		
		$result = $this->Query($query);
		
		if($result) {
			return true;
		}
		else {
			return false;
		}
	}
	
	public function fieldExists($tableName, $fieldName, $fieldDetails){
		$query = "SELECT COLUMN_NAME FROM `information_schema`.`columns`
			WHERE TABLE_SCHEMA = '".GetConfig('db_name')."' AND TABLE_NAME = '".$tableName."' AND COLUMN_NAME = '".$fieldName."'".PHP_EOL;

		if(!empty($fieldDetails)){
			if(!isset($fieldDetails['type'])){
				return false;
			}
			
			$typesNoSize = array('date', 'datetime', 'timestamp', 'time', 'tinyblob', 'blob', 'mediumblob', 'longblob', 'tinytext', 'text', 'mediumtext', 'longtext');
			if(in_array(strtolower($fieldDetails['type']), $typesNoSize)){
				$query .= " AND LOWER(COLUMN_TYPE) = '".strtolower($fieldDetails['type']."'".PHP_EOL);
			}
			elseif(!in_array(strtolower($fieldDetails['type']), $typesNoSize) && !isset($fieldDetails['size'])){
				return false;
			}
			else {
				$query .= " AND LOWER(COLUMN_TYPE) = '".strtolower($fieldDetails['type']."(".$fieldDetails['size'].")'".PHP_EOL);
			}
			
			if(isset($fieldDetails['null']) && $fieldDetails['null']){
				$query .= " AND IS_NULLABLE = 'YES'";
			}
			else {
				$query .= " AND IS_NULLABLE = 'NO'";
			}
		}

		if($this->CountResult($query) > 0) {
			return true;
		}
		else {
			return false;
		}
	}
	
	public function createField($tableName, $fieldName, $fieldDetails){
		if($this->fieldExists($tableName, $fieldName, array())){
			$query = "ALTER TABLE ".$tableName." MODIFY COLUMN ".$fieldName." ";
			$query .= $fieldDetails['type'] ." ";
			if(isset($fieldDetails['size'])) $query.= "(".$fieldDetails['size'].") ";
			$query .= (isset($fieldDetails['null']) && $fieldDetails['null']) ? "NULL " : "NOT NULL ";
			if(isset($fieldDetails['default'])) $query .= "DEFAULT '".$fieldDetails['default']."'";
			
			$result = $this->Query($query);
		}
		else{
			$query = "ALTER TABLE ".$tableName." ADD COLUMN ".$fieldName." ";
			$query .= $fieldDetails['type'] ." ";
			if(isset($fieldDetails['size'])) $query.= "(".$fieldDetails['size'].") ";
			$query .= (isset($fieldDetails['null']) && $fieldDetails['null']) ? "NULL " : "NOT NULL ";
			if(isset($fieldDetails['default'])) $query .= "DEFAULT '".$fieldDetails['default']."'";

			$result = $this->Query($query);
		}
		
		if($result) {
			return true;
		}
		else{
			return false;
		}
	}
	
	public function checkTablePK($tableName, $tablePKFields){
		$query_count = "SELECT COLUMN_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE CONSTRAINT_SCHEMA = '".GetConfig('db_name')."' AND TABLE_NAME = '".$tableName."'
						AND CONSTRAINT_NAME = 'PRIMARY'";
		if($this->CountResult($query_count) != count($tablePKFields)){
			if(!$this->createTablePK($tableName, $tablePKFields)){
				return false;
			}
		}
		
		if(is_array($tablePKFields) && !empty($tablePKFields)){
			foreach($tablePKFields as $fieldName){
				$query = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE CONSTRAINT_SCHEMA = '".GetConfig('db_name')."' AND TABLE_NAME = '".$tableName."'
						AND CONSTRAINT_NAME = 'PRIMARY' AND COLUMN_NAME = '".$fieldName."'";
				if($this->CountResult($query) == 0){
					//print "el campo ".$fieldName." no esta en la PK de ".$tableName;
					return false;
				}
			}
			//print ". Los campos (".implode(',', $tablePKFields).") de la tabla ".$tableName. "estan bien";
			return true;
		}
		else {
			return true;
		}
	}
	
	public function createTablePK($tableName, $tablePKFields){
		$query_hasPK = "SELECT 1 FROM information_schema.columns WHERE table_schema = '".GetConfig('db_name')."' AND table_name = '".$tableName."' AND column_key = 'PRI'";
		if($this->CountResult($query_hasPK) > 0){
			$query_drop = "ALTER TABLE ".$tableName." DROP PRIMARY KEY";
			if(!$this->Query($query_drop)){
				return false;
			}
		}
		
		if(is_array($tablePKFields) && !empty($tablePKFields)){
			$query_add = "ALTER TABLE ".$tableName." ADD PRIMARY KEY (".implode(',', $tablePKFields).")";
			if(!$this->Query($query_add)){
				return false;
			}
		}
		
		return true;
	}
	
	public function checkTableIndex($tableName, $tableKeyFields, $tableAllFields){
		$query_keys = "select COLUMN_NAME from information_schema.columns where table_schema = '".GetConfig('db_name')."' AND table_name = '".$tableName."' AND COLUMN_KEY = 'MUL'";
		$result_keys = $this->Query($query_keys);
		$existing_keys = array();
		while($row_keys = $this->Fetch($result_keys)){
			$existing_keys[] = $row_keys['COLUMN_NAME'];
		}

		//ToDo: checar el TAMAÑO del index y si son diferentes tirarlo y crearlo
		foreach($tableAllFields as $fieldName => $fieldDetails){
			if(!is_int(array_search($fieldName, $existing_keys)) && array_key_exists($fieldName, $tableKeyFields)){
				if(in_array(strtoupper($fieldDetails['type']), array('TINYBLOB', 'BLOB', 'MEDIUMBLOB', 'LONGBLOB', 'TINYTEXT', 'TEXT', 'MEDIUMTEXT', 'LONGTEXT'))){
					if(is_int($tableKeyFields[$fieldName])){
						$query_add = "ALTER TABLE ".$tableName." ADD INDEX k_".$fieldName." (".$fieldName."(".$tableKeyFields[$fieldName]."))";
					}
					else {
						$this->SetError(sprintf(GetLang("ErrorTextIndexNoSize"), $fieldName, $tableName, $fieldDetails['type']));
						return false;
					}
				}
				else {
					$query_add = "ALTER TABLE ".$tableName." ADD INDEX k_".$fieldName." (".$fieldName.")";
				}
				
				if(!$this->Query($query_add)){
					return false;
				}
			}
			elseif(is_int(array_search($fieldName, $existing_keys)) && !array_key_exists($fieldName, $tableKeyFields)){
				$query_drop = "ALTER TABLE ".$tableName." DROP INDEX k_".$fieldName;
				if(!$this->Query($query_drop)){
					return false;
				}
			}
		}
		
		return true;
	}
	
}