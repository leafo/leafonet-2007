<?php
/*
 * Module Class
 * Created on Apr 26, 2005
 * Converted to PageTemplate April 15, 2007
 *
 * Author: Leafo
 * Web: http://www.leafo.net
 * 
 */

############################################################
# Template for a page class, handles some variables
# 
class PageTemplate {
 	private $template;
	
	function _home() {
		return "You forgot to make a home method for this page! *_*";
	}
	
	function fetchPage() {
		if ($template) return $func->sfetch($template);
		$fname = "_".$_GET['f'];
		if (isset($_GET['f']) && is_callable(array($this, $fname)))
			return $this->$fname();
		else return $this->_home();
		
	}
	
}



?>
