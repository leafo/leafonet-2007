<?php

// make sure gw is loaded
if (!defined('leafo'))
	exit(0);


class DBResult {
	private $result;
	public $num_rows;
	public $num_affected;
	
	function __construct(&$r) {
		$this->result = $r;
		$this->num_rows = @mysql_num_rows($r);
		$this->num_affected = @mysql_affected_rows($r);
	}
	
	function fetch_assoc() {
		return mysql_fetch_assoc($this->result);
	}
	
	function fetch_row() {
		return mysql_fetch_row($this->result);
	}
	
	function close() {
		return mysql_free_result($this->result);
	}
	
}

class DBHandle {
	public $num_queries = 0;
	public $query_history;
	private $link;
	
	function __construct($username, $password, $database) {
		$this->link = @mysql_connect("localhost", $username, $password);
		if (!$this->link) {
			exit("Could not connect to mysql.");
		}
		
		if (!mysql_select_db($database)) {
			exit("Could not select mysql database");
		}
		
	}
	
	function query($q, $file = 0, $line = 0) {
		$this->num_queries++;
		$r = mysql_query($q);
		if (!$r) {
			exit("<b>Query Error:</b> file: {$file}, line: {$line}<br/>".mysql_error()."<br/><pre>".$q."</pre>");
		}
		$this->query_history[] = $q;
		return new DBResult($r);
	}
	
	function query_result($q, $file = 0, $line = 0) {
		$this->num_queries++;
		$r = mysql_query($q);
		$a = mysql_fetch_assoc($r);
		mysql_free_result($r);
		return $a;
	}
	
	// Returns first value from a select query that selects one value
	// eg. $db->query("SELECT COUNT(*) FROM table")
	function query_value($q, $file = 0, $line = 0) {
		$this->num_queries++;
		list($rows) = mysql_fetch_row(mysql_query($q));
		return $rows;
	}
}


$db = new DBHandle("leaf", "", "leaf_leafo");


?>
