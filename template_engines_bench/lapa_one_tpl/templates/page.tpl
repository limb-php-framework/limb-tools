<table width=800>
<tr><td width=200>

<table bgcolor=#000000 cellspacing=2 cellpadding=4 border=0 width=100%>
<tr><td bgcolor=#ffffff><h1>SmartyExample</h1></td></tr>
</table>
</td>
{section name=i loop=$adverts max=3}
  <td width=200 valign=top>
  <table bgcolor=#000000 cellspacing=2 cellpadding=2 border=0 width=100%>
  <tr><td><font color=#ffffff><b>{$adverts[i].section}</b></font></td></tr>
  <tr><td bgcolor=#ffffff><small><a href="{$adverts[i].url}">{$adverts[i].title}</a></small>
  </td></tr>
  </table>
{/section}
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
<h2>Introduction</h2>
<p>Mr. Treehorn draws a lot of water in this town. You don't draw shit, Lebowski. Now we got a nice, quiet little beach community here, and I aim to keep it nice and quiet. So let me make something plain. I don't like you sucking around, bothering our citizens, Lebowski. I don't like your jerk-off name. I don't like your jerk-off face. I don't like your jerk-off behavior, and I don't like you, jerk-off.</p>
<h2>News</h2>
{foreach item=i from=$news}
  <b>{$i.time} {$i.title}</b><br>
  <small>{$i.short}<a href="/news.phtml?id={$i.id}">[ read full story ]</a></small>
  <br>
{/foreach}
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
