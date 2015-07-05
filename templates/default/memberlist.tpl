<table class="forum" width="100%" cellspacing="0" cellpadding="4">
   <tr class="forum-header">
	<td width="75%">Name</td>
	<td width="40">Join Date</td>
	<td width="40">Posts</td>
   </tr>
{foreach item=m from=$members}   
  <tr>
    <td><a href="#{$m.id}">{$m.name}</a> {if $m.is_admin}(Administrator){/if}</td>
	<td>{$m.join_date|date_format}</td>
	<td>{$m.posts}</td>
  </tr>
{/foreach}
</table>
