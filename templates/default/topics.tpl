<div class="path">{foreach item=p from=$path name=path}
{if !$smarty.foreach.path.last}<a href="{link act=forums id=$p.lft}">{$p.name}</a> &raquo; {else} {$p.name} {/if}
{/foreach}</div>


{if !$topics}There are no topics in this forum yet{else}
<table class="forum" width="100%" cellspacing="0" cellpadding="4">
<tr class="forum-header">
  <td>Topics in '{$forum.name}'</td>
  <td>Author</td>
  <td>Replies</td>
  <td>Views</td>
</tr>
{foreach item=t from=$topics}
<tr>
  <td>
    {if $user->is_admin}<div style="float:right;">(<a href="{link act=forums f=deletetopic id=$t.id}">delete me</a>)</div>{/if}
    <a href="{link act=forums f=view id=$t.id}">{$t.title}</a>
  </td>
  <td><a href="{link act=user id=$t.author_id}">{$t.author}</a></td>
  <td>{$t.replies}</td>
  <td>{$t.views}</td>
</tr>
{/foreach}
</table>
{/if}

<a href="{link act=forums f=newtopic id=$smarty.get.id}">Post New Topic</a>
