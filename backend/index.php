<?php
require_once 'core/init.php';


//Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header("Access-Control-Allow-Methods: POST, GET, PATCH, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Set directory/document server-level error responses - Forbidden Access.
$access_app_error = json_encode(
	array(
		"status" => 403,
		"message"=>"Access is forbidden. You need to use a published application to access this resource."
	)
);


$access_method_error = json_encode(
	array(
		"status" => 403,
		"message"=>"Access is forbidden. You need to use an allowed method to access this resource."
	)
);


// Handle browser preflight OPTIONS requests immediately
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
	http_response_code(204); // No content
    exit; // Stop execution for preflight requests
}

// Then check for allowed methods
if (!in_array($_SERVER['REQUEST_METHOD'], ['POST', 'GET', 'PATCH'])) {    
    echo $access_method_error; // Forbidden Access
    exit; 
}

// Get api from query parameter
$app = Input::get('app');
$status = Input::get('status');
$id = Input::get('id');
$page = Input::get('page');
$limit = Input::get('limit');



if (!$app || $app != "tickets"){
	// Restrict API to app parameter equal to "tickets"
	echo $access_app_error; // Forbidden Access
	exit;
}
else {
	// Database table
	$table = 'tickets';

	// Get all input values
	$raw_input = file_get_contents("php://input");
	$input = json_decode($raw_input, true);

	// Create a new Ticket object
	$ticket = new Ticket();
	
    if($_SERVER['REQUEST_METHOD'] == 'POST') {
		// Handle standard HTTP POST method
		
		// Escape special characters from input payload values and store them in variables
		$title = escape($input['title']);
		$description = escape($input['description'] ?? "");
		$status = escape($input['status']);
		$priority = escape($input['priority']);

		try{
			// Insert new ticket record in database table
			$ticket->create($table, array(
				"title" => $title,
				"description" => $description,
				"status" => $status,
				"priority" => $priority
			));

			// Create a response payload variable
			$response = array(
				"status" => 200,
				"message" => "New entry is successfully submitted"
			);
			
			// Output payload
			echo json_encode($response);
			exit;
		}
		catch(Exception $e) {
			// Terminate method with an exception
			
			// Create an error response payload variable
			$response = array(
				"status" => 520, // Unknown Error
				"message" => $e->getMessage()
			);
			
			// Output payload
			echo json_encode($response);
			exit;
		}
	}
	else if($_SERVER['REQUEST_METHOD'] == 'GET') {
		// Handle standard HTTP GET method
		
		try{			
			// Create an empty results variable
			$results = null;

			// Extract page and limit from query string (e.g., ?page=2&limit=10)
			$page_set = $page && $limit ? 1 : 0; // Bool to check if page and limit are set
			$page = $page ? $page : 1; // Default to page 1
			$limit = $limit ? $limit : 10; // Default to 10 items per page


			if ($id && $id == 'stats'){
				// Fetch stats results
				$results = $ticket->find_stats($table);
			}
			else if($id && is_numeric($id)) {
				// Fetch ticket data by id
				$results = $ticket->find($table, 'id', $id);
			}
			else if ($status){
				// Fetch ticket data where status is equal to 'open' or 'in-progress' or 'closed'.
				// Should be able to handle pagnation with page count and range limit.
				$results = $ticket->find($table, 'status', $status, $limit, $page);
			}
			else{
				// Handle all tickets
				if($page_set) {
					// Fetch all tickets where parameters include page count and range limit for pagnation
					$results = $ticket->find($table, 'id', null, $limit, $page);
				}
				else {
					// Fetch all tickets
					$results = $ticket->find($table, 'id');
				}
			}
			
			// Handle output payload
			if ($results) {
				if($page_set) {
					// Respond with an output payload that has both page and limit
					echo json_encode(array(
						"status" => 200,
						"page" => $page,
						"limit" => $limit,
						"data_count" => count($results),
						"data" => $results
					));
				}
				else {
					// Respond with an output payload that does not have both page and limit
					echo json_encode(array(
						"status" => 200,
						"data_count" => count($results),
						"data" => $results
					));
				}
			} else {
				// Respond with an output payload that shows ticket was not found
				echo json_encode(array(
					"status" => 404,
					"message" => "No tickets found."
				));
			}
			exit;
		}
		catch(Exception $e) {
			// Terminate method with an exception
			
			// Create an error response payload variable
			$response = array(
				"status" => 520, // Unknown Error
				"message" => $e->getMessage()
			);
			
			// Output payload
			echo json_encode($response);
			exit;
		}
	}
    else if($_SERVER['REQUEST_METHOD'] == 'PATCH') {
		
		if (!$id || !is_numeric($id)){
			// Restrict PATCH method to set ticket ID's only
			echo $access_app_error;
			exit;
		}
		else {
			// Escape special characters from input payload values and store them in variables
			$status = escape($input['status']);
			$priority = escape($input['priority']);

			try{			
				// Update existing ticket record in database table
				$ticket->update($table, array(
					"status" => $status,
					"priority" => $priority
				), $id);

				// Create a response payload variable
				$response = array(
					"status" => 200,
					"message" => "Entry is successfully updated"
				);
				
				// Output payload
				echo json_encode($response);
				exit;
			}
			catch(Exception $e) {
				// Terminate method with an exception
				
				// Create an error response payload variable
				$response = array(
					"status" => 520, // Unknown Error
					"message" => $e->getMessage()
				);
				
				// Output payload
				echo json_encode($response);
				exit;
			}
		}
	}		
}

?>