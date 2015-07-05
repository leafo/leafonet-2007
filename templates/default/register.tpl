<form action="{linkme}" method="post" name="register">
  <input type="hidden" name="form_submit" value="1" />
  <fieldset>
    <legend>Register Account</legend>
	
	{if $errors}
	<div class="error"><strong>Error</strong><br />
	{foreach item=e from=$errors}
		{$e}<br />
	{/foreach}
	</div>
	{/if}
	
	<label for="user">Username</label>
	<input type="text" name="user" id="user" value="{$smarty.post.user}" />
	
	<label for="pass">Password</label>
	<input type="password" name="pass" id="pass" value="" />
	
	<label for="pass_2">Repeat Password</label>
	<input type="password" name="pass_2" id="pass_2" value="" />
	
	<label for="email">Email Address</label>
	<input type="email" name="email" id="email" value="{$smarty.post.email}" />
	
	<hr />
	
	<label>Type the following to continue</label>
	<img class="captcha" src="captcha.php5" alt="go" /><br />
	<input type="text" name="captcha" id="captcha" value="" />
	
	
	<br /><br />
	<input type="submit" name="submit" value="Submit" />
  </fieldset>
</form>
