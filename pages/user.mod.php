<?php

############################################################
# User Panel
#
class Page extends PageTemplate {
	
	public $title = "User Panel";
	
	############################################################
	# 
	#
	function _home() {
		global $user;
		
		if (intval($_GET['id']) != 0)
			return $this->_info();
		
		if ($user->logged_in)
			return $this->_panel();
		else
			return $this->_login();
	}
	
	function _info() {
		global $db, $smarty;
		$id = intval($_GET['id']);
		
		$q = $db->query("SELECT * FROM users WHERE id = {$id}");
		
		if ($q->num_rows == 0) return "Could not find user";
		
		$smarty->assign('member', $q->fetch_assoc());
		
		return fetch_template('userprofile.tpl');
	}
	
	
	function _panel() {
		
		return fetch_template('userpanel.tpl');
	}
	
	function _list() {
		global $user, $db,$smarty;
		
		$q = $db->query("SELECT * FROM users ORDER BY is_admin DESC, name ASC");
		while ($member = $q->fetch_assoc())
			$members[] = $member;
		
		$smarty->assign('members', $members);
		
		return fetch_template('memberlist.tpl');
	}
	
	function _logout() {
		global $_cookie_name, $user;
		setcookie($_cookie_name, "");
		$_COOKIES[$_cookie_name] = "";
		$user = new Guest();
		return "You are logged out";
	}
	
	function _login() {
		
		if ($_POST['form_submit']) {
			global $db;
			
			$user = mysql_real_escape_string($_POST['user']);
			$pass = md5($_POST['pass']);
			
			$q = $db->query("SELECT id FROM users WHERE name = '{$user}' AND password = '{$pass}'");
			
			if ($q->num_rows) {
				global $_cookie_name;
				list($id) = $q->fetch_row();
				setcookie($_cookie_name, serialize(array($id, $pass)));
				global $user;
				$user = new User($id, $pass);
				return "You are now logged in";
			} else {
				global $smarty;
				$smarty->assign('errors', array('Account information not found'));
			}
			
		}
		
		return fetch_template("login.tpl");
	}
	
	############################################################
	# Create a new user
	#
	function _register() {
		global $db, $smarty, $user;
		
		if (time() - $_SESSION['heat'] < 60) {
			return "Wait a bit before creating a new account.";
		}
		
		// Try to add the user
		if ($_POST['form_submit']) {
			$username = mysql_real_escape_string(htmlentities($_POST['user']));
			$email = mysql_real_escape_string($_POST['email']);
			$password = $_POST['pass'];
			
			if (empty($username))
				$errors[] = "You must fill out a username";
			
			if (empty($password))
				$errors[] = "You must fill out a password";
			else if ($_POST['pass'] !== $_POST['pass_2'])
				$errors[] = "Your passwords do not match";
			else 
				$password = md5($_POST['pass']);
			
			$pattern = "/[a-zA-Z][\w\.-]*[a-zA-Z0-9]@[a-zA-Z0-9][\w\.-]*[a-zA-Z0-9]\.[a-zA-Z][a-zA-Z\.]*[a-zA-Z]/";
			
			if (empty($email))
				$errors[] = "You must specify email address";
			else if (!preg_match($pattern, $email))
				$errors[] = "You must provide valid email address";
			
			if ($_POST['captcha'] != $_SESSION['code'])
				$errors[] = "Human verification code does not match";
			
			
			if (!empty($errors)) {
				unset($_POST['form_submit']);
				$smarty->assign('errors', $errors);
				return $this->_register();
			}
			
			// Check if account exists
			$q = $db->query("SELECT COUNT(*) FROM users WHERE name = '{$username}'", __file__, __line__);
			list($count) = $q->fetch_row();
			if ($count == 1) return "Username exists";
			
			$user = new User();
			$user->name = $username;
			$user->password = $password;
			$user->update();
			
			$_SESSION['heat'] = time();
			
			return "User created.";
		}
		
		// create captcha
		$c = rand();
		$c = md5($c);
		$c = substr($c, 0, 5);
		
		$_SESSION['code'] = $c;
		
		return fetch_template('register.tpl');
	}
	

}


?>
