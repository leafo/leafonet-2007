<form action="{linkme}" method="post" name="newtopic">
  <input type="hidden" name="form_submit" id="form_submit" value="1" />
  <fieldset>
    <legend>Post New Topic in '{$forum.name}'</legend>
	
	{if $errors}
	<div class="error"><strong>Error</strong><br />
	{foreach item=e from=$errors}
		{$e}<br />
	{/foreach}
	</div>
	{/if}
	
	<label for="title">Title</label>
	<input type="text" name="title" id="title" value="{$smarty.post.title}" size="40" />
	<br/>
	<label for="body">Body</label>
	<textarea name="body" id="body">{$smarty.post.body}</textarea>
	<br/>
	<input type="submit" name="submit" id="submit" value="Submit New Topic" />
  </fieldset>
  
  
</form>
