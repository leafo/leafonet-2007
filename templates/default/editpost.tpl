<div class="path">{foreach item=p from=$path name=path}
<a href="{link act=forums id=$p.lft}">{$p.name}</a> &raquo;
{/foreach}
Topic '<a href="{link act=forums f=view id=$post.topic_id}">{$post.topic_title}'</a> &raquo;
Editing Post
</div>

<form action="{linkme}" method="post" name="newtopic">
  <input type="hidden" name="form_submit" id="form_submit" value="1" />
  <fieldset>
    <legend>Edit Topic</legend>
	<label for="body">Body</label>
	<textarea name="body" id="body">{$post.body}</textarea>
	<br/>
	<input type="submit" name="submit" id="submit" value="Save Changes" />
  </fieldset>
  
  
</form>
