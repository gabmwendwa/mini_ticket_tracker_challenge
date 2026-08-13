<?php

class DB{
	private static $_instance = null; // instance that belong to DB class itself to be used in every instance once defined
	private $_pdo, 
			$_query,
			$_error = false,
			$_results,
			$_count = 0;
			
	public function __construct () {
		// Use PHP Data Object to connect to MySQL database
		try{
			$this->_pdo = new PDO('mysql:host='. Config::get('mysql/host') .';dbname=' . Config::get('mysql/db'), Config::get('mysql/username'), Config::get('mysql/password'));
		} catch(PDOException $e) {
			// Terminate method with an exception
			
			// Create an error response payload variable
			$response = array(
				"status" => 403, // Unknown Error
				"message" => $e->getMessage()
			);
			
			// Output payload
			echo json_encode($response);
			exit;
		}
	}
	
	// Perform database instance as a getter method
	public static function getInstance(){
		if(!isset(self::$_instance)){
			self::$_instance = new DB();
		}
		return self::$_instance;
	}
	
	// Perform MySQL query
	public function query($sql, $params = array()){
		$this->_error = false; //reset error back to false
		if($this->_query = $this->_pdo->prepare($sql)){
			$x = 1;
			if(count($params)) {
				foreach($params as $param){
					$this->_query->bindValue($x, $param); 
					$x++;
				}
			}
			
			if($this->_query->execute()){
				$this->_results = $this->_query->fetchAll(PDO::FETCH_OBJ);
				$this->_count = $this->_query->rowCount();
			}
			else {
				$this->_error = true;
			}
		}
		
		return $this;
	}

	// Quick database query database
	public function action($action, $table, $where = array()){
		if(count($where) === 3){
			$operators = array('=', '>', '<', '>=', '<=');
			
			$field    = $where[0];
			$operator = $where[1];
			$value    = $where[2];
			
			if(in_array($operator, $operators)){
				$sql = "{$action} FROM {$table} WHERE {$field} {$operator} ?";

				if (!$this->query($sql, array($value))->error()){
					return $this;
				}
			}
		}
		return false;
	}

	// Perform read all with limit and page
	public function action_all($action, $table, $column, $limit = null, $page = null){
		$sql = "";
		$values = array();

		if(is_array($column)){
			// Check if it's a multi-dimensional array with multiple conditions
			if(isset($column[0]) && is_array($column[0])){
				$whereClauses = array();
				$operators = array('=', '>', '<', '>=', '<=');

				foreach($column as $condition){
					if(count($condition) === 3){
						$field    = $condition[0]; // define field
						$operator = $condition[1]; // define operator
						$value    = $condition[2]; // define value

						if(in_array($operator, $operators)){
							$whereClauses[] = "{$field} {$operator} ?";
							$values[] = $value;
						}
					}
				}

				if(!empty($whereClauses)){
					$sql = "{$action} FROM {$table} WHERE " . implode(' AND ', $whereClauses);
				}
			} 
			elseif(count($column) === 3){
				// Otherwise, handle the original single condition format: array(field, operator, value)
				$operators = array('=', '>', '<', '>=', '<=');
				
				$field    = $column[0];
				$operator = $column[1];
				$value    = $column[2];
				
				if(in_array($operator, $operators)){
					$sql = "{$action} FROM {$table} WHERE {$field} {$operator} ?";
					$values[] = $value;
				}
			}
		}
		else{
			// Fallback if $column is just a string (used for ORDER BY)
			$sql = "{$action} FROM {$table}";
			if(!empty($column)){
				$sql .= " ORDER BY {$column} ASC";
			}           
		}

		// Append Pagination (LIMIT and OFFSET)
		if($limit !== null && is_numeric($limit)){
			$sql .= " LIMIT " . (int)$limit;
			
			if($page !== null && is_numeric($page) && $page > 0){
				$offset = ((int)$page - 1) * (int)$limit;
				$sql .= " OFFSET " . (int)$offset;
			}
		}

		if(!empty($sql)){
			if (!$this->query($sql, $values)->error()){
				return $this;
			}
		}
		return false;
	}

	// Quick read function
	public function get($table, $where = null){
		return $this->action('SELECT *', $table, $where);
	}
	
	// Read all with limit and page
	public function get_all($table, $column, $limit = null, $page = null){
		return $this->action_all('SELECT *', $table, $column, $limit, $page);
	}

	// Quick delete function
	public function delete($table, $where) {
		return $this->action('DELETE', $table, $where);
	}
	
	// Perform instert query
	public function insert($table, $fields = array()) {
		
		$keys = array_keys($fields);
		$values = null;
		$x = 1;
		
		foreach($fields as $field){
			$values .= '?';
			
			if($x < count($fields)){
				 $values .= ', ';
			}
			
			$x++;
		}
		
		$sql = "INSERT INTO {$table} (`" . implode('`, `', $keys) . "`) VALUES({$values})"; //{$table}
		
		if(!$this->query($sql, $fields)->error()) {
			return true;
		}
		return false;
	}
	
	// Perform update query
	public function update($table, $id, $fields){ 
		$set = '';
		$x = 1;
		
		foreach($fields as $name => $value) {
			$set .= "{$name} = ?";
			
			if($x < count($fields)){
				$set .= ', ';
			}
			
			$x++;
		}
		
		$sql = "UPDATE {$table} SET {$set} WHERE id = {$id}";
		
		if(!$this->query($sql, $fields)->error()){
			return true;
		}
		
		return false;
	}
	
	// Results getter method
	public function results() {
		return $this->_results;
	}
	
	// Data count getter method
	public function count() {
		return $this->_count;
	}
	
	// Query error getter method	
	public function error() {
		return $this->_error;
	}
}