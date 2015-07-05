<?php
/**
 * Common setup/functions
 *
 */

if (!defined('leafo'))
	exit(0);

set_magic_quotes_runtime(0);

if (get_magic_quotes_gpc()) {
	// Strip all inserted slashes
	foreach($_POST as &$value) $value = stripslashes($value);
	foreach($_GET as &$value) $value = stripslashes($value);
	foreach($_COOKIE as &$value) $value = stripslashes($value);
}

// Trim the post data
foreach($_POST as &$value)
	$value = trim($value);


function bb_code($text) {
	$text = htmlentities($text);
	
	$search = array(
		'/(?<!\])(?<!=)(?<!\/)http\:\/\/(.*?)(\s|\z)/i',
		'/\[url\]http\:\/\/(.*?)\[\/url\]/i',
		'/\[url\](.*?)\[\/url\]/i',
		'/\[b\](.*?)\[\/b\]/is',
		'/\[i\](.*?)\[\/i\]/is',
		'/\[u\](.*?)\[\/u\]/is',
		'/\[color=(.*?)\](.*?)\[\/color\]/is',
		'/\[hr\]/s',
		'/\[img\](.*?)\[\/img\]/i',
	);
	
	$replace = array(
		'<a href="http://$1">$1</a> ',
		'<a href="http://$1">$1</a> ',
		'<a href="http://$1">$1</a> ',
		'<strong>$1</strong>',
		'<em>$1</em>',
		'<u>$1</u>',
		'<span style="color: $1">$2</span>',
		'<hr />',
		'<img src="$1" alt="user posted image"/>',
	);
	
	
	$text = preg_replace($search, $replace, $text);
	
	return $text;
}


############################################################
# Grab and use smarty template from dynamic directory
# no idea what root does
function fetch_template($fname, $root = 0) {
	global $smarty, $_default_skin, $_act, $_skin;
	
	// first see if it is 
	if (is_file($smarty->template_dir.'/'.$_skin.'/'.$_act.'/'.$fname) && $root == 0)
		return $smarty->fetch($_skin.'/'.$_act.'/'.$fname);
	elseif(is_file($smarty->template_dir.'/'.$_skin.'/'.$fname))
		return $smarty->fetch($_skin.'/'.$fname); 
	elseif (is_file($smarty->template_dir.'/'.$_default_skin.'/'.$_act.'/'.$fname) && $root == 0)
		return $smarty->fetch($_default_skin.'/'.$_act.'/'.$fname);
	else
		return $smarty->fetch($_default_skin.'/'.$fname);
}


############################################################
# Create a link to someplace
#
function slink($params) {
	$link = "?";
	foreach ($params as $key=> $value)
		$link .= $key.($value ? "=".$value."&amp;" : "");
	
	return $link;
}


############################################################
# Build auto forwarding message html:
# error or success, message, forward url, wait time
function forward($time, $link, $class = 1, $msg = "") {
	global $smarty;
	
	
	if ($class == 0) $title = "Error";  // 0 FOR ERROR
	if ($class == 1) $title = "Success"; // 1 FOR SUCCESS
	
	$smarty->assign("etitle", $title);
	$smarty->assign("message", $msg);
	$smarty->assign("redirect", $link);
	$smarty->assign("time", $time);
	
	return smartFetch('redirect.tpl');
}


############################################################
# Makes a thumbnail for an image, src -> dest
# 
function makeThumb($src, $dest, $max_width = 120, $max_height = 90, $truecolor = true) {
	$ratio = $max_width / $max_height;
	
	$image_info = getimagesize($src);
	
	if (!$image_info) return false; // Couldn't do anything
	
	$image = NULL;
	switch ($image_info['mime']) {
		case "image/gif":
			$image = imagecreatefromgif($src);
			$func = "imagegif";
			break;
		case "image/png":
			$image = imagecreatefrompng($src);
			$func = "imagepng";
			break;
		case "image/jpeg":
			$image = imagecreatefromjpeg($src);
			$func = "imagejpeg";
			break;
		default:
			return; // can't do anything
	}
	if ($image == NULL) return false;
	
	$iratio = $image_info[0] / $image_info[1];
	if ($ratio <= $iratio) { // width dominates
		$new_width = $max_width;
		$r = $image_info[0] / $new_width;
		$new_height = round($image_info[1] / $r);
	} else {
		$new_height = $max_height;
		$r = $image_info[1] / $new_height;
		$new_width = round($image_info[0] / $r);
	}
	
	$resized = imagecreatetruecolor($new_width, $new_height);
	
	imagecopyresampled($resized, $image, 0,0,0,0, $new_width, $new_height, $image_info[0], $image_info[1]);
	
	if (!$truecolor)
		$resized = ImageTrueColorToPalette2($resized, false, 255);
	
	return $func($resized, $dest, 9);
}

// comments @ http://us.php.net/manual/en/function.imagepng.php
// makes a nice 8bit image
function ImageTrueColorToPalette2($image, $dither, $ncolors) {
    $width = imagesx( $image );
    $height = imagesy( $image );
    $colors_handle = ImageCreateTrueColor( $width, $height );
    ImageCopyMerge( $colors_handle, $image, 0, 0, 0, 0, $width, $height, 100 );
    ImageTrueColorToPalette( $image, $dither, $ncolors );
    ImageColorMatch( $colors_handle, $image );
    ImageDestroy($colors_handle);
    return $image;
}

function getip() {
	if (isSet($_SERVER)) {
	if (isSet($_SERVER["HTTP_X_FORWARDED_FOR"])) {
		$realip = $_SERVER["HTTP_X_FORWARDED_FOR"];
	} elseif (isSet($_SERVER["HTTP_CLIENT_IP"])) {
		$realip = $_SERVER["HTTP_CLIENT_IP"];
	} else {
		$realip = $_SERVER["REMOTE_ADDR"];
	}
	
	} else {
	if ( getenv( 'HTTP_X_FORWARDED_FOR' ) ) {
		$realip = getenv( 'HTTP_X_FORWARDED_FOR' );
	} elseif ( getenv( 'HTTP_CLIENT_IP' ) ) {
		$realip = getenv( 'HTTP_CLIENT_IP' );
	} else {
		$realip = getenv( 'REMOTE_ADDR' );
	}
	}
	return $realip;
}



?>
