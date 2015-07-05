<?php 
/**
 * Registers some functions for use inside smarty templates
 *
 */


$smarty->register_modifier('stripslashes', 'stripslashes');
$smarty->register_function('sfetch', 'smartyfetch');
$smarty->register_function('link', 'smartyLink');
$smarty->register_function('linkme', 'smartyLinkMe');
$smarty->register_function('stars', 'stars');

############################################################
# Fetch template from another template
#
function smartyfetch($params, &$smarty) {
	global $_skin, $conf;
	
	if (empty($params['fname'])) return '';
	
	if (is_file($smarty->template_dir.'/'.$_skin.'/'.$params['fname']))
		return $smarty->fetch($_skin.'/'.$params['fname']);
	else
		return $smarty->fetch($conf['def_skin'].'/'.$params['fname']);	
}

############################################################
# Create a link
#
function smartyLink($params, &$smarty) {
	return slink($params);
}

############################################################
# create a link to current page
#
function smartyLinkMe($params, &$smart) {
	return LinkMe();
}

function LinkMe($amp = "&amp;") {
	$link = "?";
	foreach ($_GET as $key => $value) {
		if ($key == substr($_GET['act'], 1)) continue; // strange smf bug I think..
		$link .= $key.($value ? "=".$value.$amp : "");
	}

	return $link;
}

function stars($params, &$smarty) {
	$c = $params['c'];
	if (!$c) return;
	
	$c = round($c);
	settype($c, "int"); 
	
	$star_width = 10;
	$star_height = 10;
	
	$out = "<div style=\"float:left; width:".$c*$star_width."px; height: {$star_height}px; background-image: url('resources/star.png');\"></div>";
	return $out;
}






?>
