<?php
/**
 * Don't balls this one up
 *
 */

define("leafo", 1);
ini_set("arg_separator.output","&amp;");  
session_start();

// Set up some variables
require "config.php";
// * * *

// Enable gzip compression if possible
if ($_gzip_compression && extension_loaded('zlib') && 
	(strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false || 
	strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'deflate') !== false))
{	
	ob_start("ob_gzhandler");
} else {
	ob_start();
}

require "include/dbhandle.php";
require "include/common.php";
require "include/user.php";

require "smarty/libs/Smarty.class.php";
require "include/page_template.php";

$smarty = new Smarty();
$smarty->template_dir = $_template_dir;
$smarty->compile_dir = "smarty/templates_c";
$smarty->cache_dir 	= "smarty/cache";


require "include/smarty_functions.php";

// Requesting a skin change
/*if ($_REQUEST['skin']) {
	if (is_dir($conf['tem_dir'].$_GET['skin'])) {
		$_skin = $_GET['skin'];
		$func->makecookie($_prefix.'skin',$_skin);
	}
}*/

// Pick the skin
if (!isset($_skin) && isset($_COOKIE[$_prefix.'skin']))
	$_skin = $_COOKIE[$_prefix.'skin'];
elseif (!isset($_skin)) 
	$_skin = $_default_skin;


$smarty->assign('tem_dir', $_template_dir.$_skin);
$smarty->assign('img_dir', $_template_dir.$_skin.'/images/');

$smarty->assign('act', $_act);
$smarty->assign('func', $_func);
$smarty->assign('skin', $_skin);

$smarty->assign_by_ref('user', $user);
$smarty->assign_by_ref('db', $db);

// Load the page module
require $_page_dir.$_act.".mod.php";
	
// Load page module
$page = new Page();
$smarty->assign_by_ref('content', $page->fetchPage());

if ($_no_wrapper == 1)
	echo $smarty->get_template_vars('content');
else
	echo fetch_template($_index_tpl);


ob_flush();

//echo "<pre>".print_r($_COOKIE,1)."<br /><br />".print_r($user, 1);

?>
