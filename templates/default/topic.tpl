<div class="path">{foreach item=p from=$path name=path}
<a href="{link act=forums id=$p.lft}">{$p.name}</a> &raquo;
{/foreach}
{$topic.title}
</div>


{foreach item=p from=$posts name=posts}
<a name="{$p.id}" />{if $smarty.foreach.posts.last}<a name="latest" />{/if}

<div class="forum">
<div class="forum-header">
  <div style="float: right">{if $user->is_admin}({if $p.topic_post}<a href="{link act=forums f=deletetopic id=$smarty.get.id}">delete topic</a>{else}<a href="{link act=forums f=deletepost id=$p.id}">delete post</a>{/if}){/if}
  {if $user->is_admin || $user->id == $p.author_id }(<a href="{link act=forums f=edit id=$p.id}">edit post</a>){/if}
  </div>
  by <a href="{link act=user id=$p.author_id}">{$p.author}</a> on {$p.post_date|date_format:"%B %e, %Y, %I:%M %p"}
</div>
<p>{$p.body|nl2br}</p>
</div>
{/foreach}

{if $user->logged_in}
<form action="{link act=forums f=newreply id=$smarty.get.id}#post" method="post" name="reply">
  <input type="hidden" name="form_submit" id="form_submit" value="1" />
  <fieldset>
    <legend>Post a Reply</legend>
	<a name="post" />
	{if $errors}
	<div class="error"><strong>Error</strong><br />
	{foreach item=e from=$errors}
		{$e}<br />
	{/foreach}
	</div>
	{/if}
	
	<label for="body">Body</label>
	<textarea name="body" id="body"></textarea>
	<br />
	<input type="submit" name="submit" id="submit" value="Submit Reply" />
	
  </fieldset>
</form>
{/if}
