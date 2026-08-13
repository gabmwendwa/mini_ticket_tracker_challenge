<?php

session_start();

// Create a global variable that hold the necessary components for successful
$GLOBALS['config']= array(
	'mysql' => array(
		'host' => 'localhost',
		'username' => '{username}', // Set the username for the database connection
		'password' => '{password}', // Set the password for the database connection
		'db' => 'mtt_db'
	)
);

// Automatically load all classes in the class directory
spl_autoload_register(function($class) {
	require_once ('classes/' . $class . '.php');
});

// Include string functions
require_once ('functions/string.php');

// Set the default timezone to Nairobi time
date_default_timezone_set("Africa/Nairobi");

?>



