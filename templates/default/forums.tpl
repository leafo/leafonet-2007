{foreach item=f from=$forums}
{if $f.depth == 0}
  {if $f.depth_change != 1}</table>{/if}{*close old forum*}
  <table class="forum" width="100%" cellspacing="0" cellpadding="4">
   <tr class="forum-header">
	<td width="75%">{$f.name}</td>
	<td width="40">Topics</td>
	<td width="40">Posts</td>
	<td>Last Post</td>
   </tr>
   {if $f.lft == $f.rgt-1 }<tr><td colspan=3>This forum has no sub-forums visible to you.</td></tr>{/if}
{else}
  <tr>
	<td><a href="{link act=forums id=$f.lft}">{$f.name}</a>
	{if $f.depth == 1}<div>{$f.description}</div>{/if}</td>
	<td>{$f.topics}</td>
	<td>{$f.replies+$f.topics}</td>
	<td>{if $f.topic_id}<a href="{link act=forums f=view id=$f.topic_id}#latest">{$f.topic_title|truncate:30}</a><br />
		by <a href="{link act=user id=$f.author_id}">{$f.author}</a>{/if}</td>
  </tr>
{/if}
{/foreach}
</table>
