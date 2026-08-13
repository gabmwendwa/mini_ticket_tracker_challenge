<?php

class Ticket{
	// private database and data variables
	private $_db,
			$_data;
	
	public function __construct(){
		// Create a database instance
		$this->_db = DB::getInstance();		
	}
	
	public function create($table = null, $fields = array()){
		// Handle table insert function for a Ticket object
		if(!$this->_db->insert($table, $fields)){
			throw new Exception('There was a problem inserting in '.$table.'. Please try again.');
		}
	}

	public function find($table = null, $field = null, $value = null, $limit = null, $page = null){
		// Handle table read function for a Ticket object
		$res = [];
		
		if(is_array($field)) {
			$conditions = []; // Empty condtions array for select query
			// Check if $value is an array
			if(is_array($value)) {
				foreach($field as $index => $col) {
					// Set each column to its corresponding value
					$conditions[] = array($col, '=', $value[$index] ?? null);
				}
			} else {
				// Give $conditions the value of $field 
				$conditions = $field; 
			}
			// Get table data
			$data = $this->_db->get_all($table, $conditions, $limit, $page);
		} 
		elseif($value !== null) {
			// Parse hard coded condition array if $value is not an array and is not empty 
			$data = $this->_db->get_all($table, array($field, '=', $value), $limit, $page);
		}
		elseif($value == null && $limit !== null && $page !== null) {
			// Parse $table, $field, $limit and $page if $value is empty
			$data = $this->_db->get_all($table, $field, $limit, $page);
		}
		else {
			// Parse $field, $limit and $page if $value is empty
			$data = $this->_db->get_all($table, $field);
		}
		
		if($data && $data->count()){
			// Assign data object from database to Ticket's
			$this->_data = $data->results();
			
			// Convert object to array
			$result = (array) $this->_data;
			
			// Removes duplicate values from an array, show unique elements only
			$result = array_unique($result, SORT_REGULAR);
			
			// Return results
			return $result;
		}
		return false;
	}
	
	public function update($table = null, $fields = array(), $id = null){
		// Handle table update function for a Ticket object  
		if(!$this->_db->update($table, $id, $fields)){
			throw new Exception('There was a problem updating. Please try again.');
		}
	}
	
	public function find_stats($table) {
		// Find stats grouped by status 'o = Open', 'i = In-progress' and, 'c = Closed'
        $sql = "SELECT status, COUNT(*) as count FROM {$table} GROUP BY status";
        $query = $this->_db->query($sql);
        
        if(!$query->error()) {
			// Return query results
            return $query->results();
        }
        return false;
    }
}

?>