<table width=800>
<tr><td width=200>

<table bgcolor=#000000 cellspacing=2 cellpadding=4 border=0 width=100%>
<tr><td bgcolor=#ffffff><h1>SmartyLightExample</h1></td></tr>
</table>
</td>
{foreach key=i value=advert from=$adverts}
  <td width=200 valign=top>
  <table bgcolor=#000000 cellspacing=2 cellpadding=2 border=0 width=100%>
  <tr><td><font color=#ffffff><b>{$advert[section]}</b></font></td></tr>
  <tr><td bgcolor=#ffffff><small><a href="{$advert[url]}">{$advert[title]}</a></small>
  </td></tr>
  </table>
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
<h2>Introduction</h2>
<p>Mr. Treehorn draws a lot of water in this town. You don't draw shit, Lebowski. Now we got a nice, quiet little beach community here, and I aim to keep it nice and quiet. So let me make something plain. I don't like you sucking around, bothering our citizens, Lebowski. I don't like your jerk-off name. I don't like your jerk-off face. I don't like your jerk-off behavior, and I don't like you, jerk-off.</p>
<h2>News</h2>
{foreach value=one_news from=$news}
  <b>{$one_news[time]} {$one_news[title]}</b><br>
  <small>{$one_news[short]}<a href="/news.phtml?id={$one_news[id]}">[ read full story ]</a></small>
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
