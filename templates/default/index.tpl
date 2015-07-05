<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
  <head>
	<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
	<link rel="stylesheet" type="text/css" href="{$tem_dir}/style.css" media="screen" />
	<link rel="openid.server" href="http://www.myopenid.com/server" />
	<link rel="openid.delegate" href="http://leafo.myopenid.com/" />
	<title>leafo.net</title>
  </head>
  <body>
	
	<h1><a href="{link}">Leafo.net</a></h1>
	<div id="wrapper">

	<ul>
	  <li><a href="{link act=user f=list}">Member List</a></li>
	  {if $user->logged_in}
	  <li><a href="{link act=user}">User</a></li>
	  <li><a href="{link act=user f=logout}">Log Out</a></li>
	  {else}
	  <li><a href="{link act=user}">Log In</a> (<a href="{link act=user f=register}">Register</a>)</li>
	  {/if}
	</ul>

	
	{$content}
	
	{*
	<hr />
	
	{foreach item=q from=$db->query_history}
	<div style="font-family: consolas;">{$q}</div>
	{/foreach}
	*}
	</div>
	
	
  </body>
</html>
