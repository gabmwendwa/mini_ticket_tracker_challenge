<?php

class Config {
	// Get and assign global project configurations, e.g, database connection parameter, user sessions, etc. 
	public static function get($path = null) {
		if($path) {
			$config = $GLOBALS['config'];
			$path = explode('/', $path);
			
			foreach($path as $bit) {
				if(isset($config[$bit])) {
					$config = $config[$bit];
				}
			}
			
			return $config;
		}
		
		return false;
	}
}

?>