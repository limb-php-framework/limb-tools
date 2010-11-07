<table width=800>
<tr><td width=200>

<table bgcolor=#000000 cellspacing=2 cellpadding=4 border=0 width=100%>
<tr><td bgcolor=#ffffff><h1>SmartyLightExample</h1></td></tr>
</table>
</td>
{foreach key=i value=advert from=$adverts}
  {include file="adverts_item.tpl"}
{/foreach}
</td>
</tr>

<tr valign=top>
<td width=200>
<table width=100% cellpadding=3>
{foreach key=i from=$sections value=section}
<tr>
<td bgcolor={if $i % 2}#eeeeee{else}#dddddd{/if}>
<font color=#ffffff><b>
<a href="/section.phtml?id={$section[id]}">{$section[name]}</a>
{if $section[rip] }<font color=#999999>R.I.P.</font></font>{/if}
</td>
</tr>
{/foreach}
</table>

<p><b>Users</b>: {$num_total}<br>

<b>Online</b>: {$num_online}<br>

<small>
<i>
{foreach from=$users value=user}
<a href="/user.phtml?id={$user[id]}">{$user[name]}</a>
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
{foreach from=$poll_answers value=poll}
<tr valign=center><td><small><input type=radio name=a>{$poll}<br></td></tr>
{/foreach}
<tr><td align=center><input type=submit name="OK" value="{$poll_button}"></td></tr>
</table>
</form>
</td>

<td width=400 colspan=3>
{include file=$page}
</td>
</tr>
<tr>
<td colspan=4 align=center>
<hr>
<small>
<i>Lebowski test (Copyleft) korchasa<br>
based on BlitzTest (Alexey A. Rybak).<br>
Texts are taken from IMDB.com, Memorable Quotes from "The Big Lebowski" (Ethan & Joel Coen, 1998). <br>
</i>
</td>
</tr>
</table>
