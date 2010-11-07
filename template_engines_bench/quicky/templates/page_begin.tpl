<table width=800>
<tr><td width=200>

<table bgcolor=#000000 cellspacing=2 cellpadding=4 border=0 width=100%>
<tr><td bgcolor=#ffffff><h1>QuickyExample</h1></td></tr>
</table>
</td>
{foreach item=advert from=$adverts}
  {include file="adverts_item.tpl"}
{/foreach}
</td>
</tr>

<tr valign=top>
<td width=200>
<table width=100% cellpadding=3>
{section name=i loop=$sections}
<tr>
<td bgcolor={if $smarty.section.i.index is odd}#eeeeee{else}#dddddd{/if}>
<font color=#ffffff><b>
<a href="/section.phtml?id={$sections[i].id}">{$sections[i].name}</a>
{if $sections[i].rip }<font color=#999999>R.I.P.</font></font>{/if}
</td>
</tr>
{/section}
</table>

<p><b>Users</b>: {$num_total}<br>

<b>Online</b>: {$num_online}<br>

<small>
<i>
{foreach from=$users item=i}
<a href="/user.phtml?id={$i.id}">{$i.name}</a>
{/foreach}
</i>
</small>

</small>
<p><b>{$poll_title}</b><br>
<small>
{$poll_question}
<small><br>
<form method=post>
<table>
{foreach item=i from=$poll_answers}
<tr valign=center><td><small><input type=radio name=a>{$i}<br></td></tr>
{/foreach}
<tr><td align=center><input type=submit name="OK" value="{$poll_button}"></td></tr>
</table>
</form>
</td>

<td width=400 colspan=3>