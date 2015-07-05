<?php

if (!defined('leafo'))
	exit(0);

$_img_dir = "resources/";
$_template_dir = "templates/";
$_page_dir = "pages/";

$_gzip_compression = true;
$_default_skin = "default";
$_default_page = "forums";
$_base_url = "http://leafo.net/";
$_cookie_name = "leafo_net";

$_prefix = ""; // table prefix
$_no_wrapper = ($_GET['no_wrapper'] ? $_GET['wrapper'] : 0);
$_index_tpl = "index.tpl"; // The main template, content wrapper

$_act 	= $_GET['act'] ? $_GET['act'] : $_default_page;
$_func 	= $_GET['f'];

?>
