<?php

if (!defined('leafo'))
	exit(0);

class User {
	private $info, $altered;
	public $table_name, $logged_in;
	
	############################################################
	# Create a new user..
	# Use ID to fetch a user
	function __construct($first = NULL, $pass_hash = NULL) {
		global $db;
		$this->table_name = $table_name = "users";
		$this->logged_in = false;
		if ($first == NULL && $pass_hash == NULL) { // Create a new blank user
			// load table information
			$q = $db->query("DESCRIBE {$table_name}");
			while ($field = $q->fetch_assoc()) {
				$this->info[$field['Field']] = $field['Default'];
			}
			$this->info['id'] = NULL;
			$this->info['join_date'] = time();
			$this->info['register_ip'] = getip();
			
		} else if ($first != NULL && $pass_hash == NULL) { // load a user
			$id = intval($first);
			
			$q = $db->query("SELECT * FROM {$table_name} WHERE id = {$id}");
			if ($q->num_rows == 0) throw new Exception("User could not be loaded");
			
			$this->info = $q->fetch_assoc();
			$q->close();
			
		} else { // try to login with account information
			$id = intval($first);
			$pass_hash = mysql_real_escape_string($pass_hash);
			$q = $db->query("SELECT * FROM {$table_name} WHERE id = '{$id}' AND password = '{$pass_hash}'");
			if ($q->num_rows == 0) throw new Exception("User ({$id}, {$pass_hash}) could not be loaded");
			
			$this->info = $q->fetch_assoc();
			$q->close();
			
			$this->logged_in = true;
		}
	}
	
	############################################################
	# Update the user, insert instead if user doesn't exist
	#
	function update() {
		global $db;
		
		if ($this->id == NULL) { // insert the user
			$c = 0;
			$query = "INSERT INTO {$this->table_name} SET";
			foreach ($this->info as $key=>$value) {
				$c++;
				$query.= " {$key}='{$value}'". ($c != count($this->info) ? "," : "");
			}
			
			$db->query($query, __file__, __line__);
			
			$this->id = $db->insert_id;
			
		} else { // update the user
			
			$c = 0;
			$query = "UPDATE {$this->table_name} SET ";
			foreach ($this->altered as $key=>$value) {
				$c++;
				$query.= " {$key}='{$value}'". ($c != count($this->altered) ? "," : "");
			}
			$query .= " WHERE id = ".$this->id;
			
			$db->query($query, __file__, __line__);
			
		}
		
		// clear altered rows
		$this->altered = array();
	}
	
	############################################################
	# Magic function to get property
	# 
	function __get($name) {
		return array_key_exists($name, $this->info) ? $this->info[$name] : null;
	}
	
	############################################################
	# Magic function to set property
	# 
	function __set($name, $value) {
		// only change keys that exist to prevent error on update
		if (array_key_exists($name, $this->info))
			$this->info[$name] = $this->altered[$name] = $value;
	}
	
	
}

class Guest {
	public $id = 0;
	public $name = "Guest";
	public $logged_in = 0;
	public $is_admin = false;
}

// Attempt to read cookie
list($id, $pass) = @unserialize($_COOKIE[$_cookie_name]);
if ($id && $pass) {
	try {$user = new User($id, $pass);} catch (Exception $e) {
		// Invalid cookie
		$user = new Guest();
	}
} else {
	$user = new Guest();
}

?>
