<form action="{linkme}" method="post" name="register">
  <input type="hidden" name="form_submit" value="1" />
  <fieldset>
    <legend>Log In</legend>
	
	{if $errors}
	<div class="error"><strong>Error</strong><br />
	{foreach item=e from=$errors}
		{$e}<br />
	{/foreach}
	</div>
	{/if}
	
	<label for="user">Username</label>
	<input type="text" name="user" id="user" value="" />
	
	<label for="pass">Password</label>
	<input type="password" name="pass" id="pass" value="" />
	
	<br /><br />
	<input type="submit" name="submit" value="Log In" />
  </fieldset>
</form>

<p>Don't have an account? Then <a href="{link act=user f=register}">go register one</a>.</p>
