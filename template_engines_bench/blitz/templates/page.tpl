<table width=800>
<tr><td width=200>

<table bgcolor=#000000 cellspacing=2 cellpadding=4 border=0 width=100%>
<tr><td bgcolor=#ffffff><h1>BlitzExample</h1></td></tr>
</table>
</td>
{{ adverts() }}
</tr>

<tr valign=top>
<td width=200>
<table width=100% cellpadding=3>
{{ BEGIN sections }}
<tr>
<td bgcolor=#{{ if($odd,'eeeeee','dddddd') }}>
<font color=#ffffff><b>
<a href="/section.phtml?id={{ $id }}">{{ $name }}</a> {{ if($rip,'<font color=#999999>R.I.P.</font>') }}</font>
</td>
</tr>
{{ END }}
</table>

<p><b>Users</b>: {{ $total_users }}<br>
<b>Online</b>: {{ $total_online_users }}<br>
<small>
<i>
{{BEGIN users_online}}
<a href="/user.phtml?id={{ $id }}">{{ $name }}</a>
{{ END }}
</i>

</small>

</small>
<p><b>{{ $poll_title }}</b><br>
<small>
{{ $poll_question }}
<small><br>
<form method=post>
<table>
{{BEGIN answers}}
<tr valign=center><td><small><input type=radio name=a value={{ $id }}>{{ $answer }}<br></td></tr>
{{ END }}
<tr><td align=center><input type=submit name="OK" value="{{ $poll_button }}"></td></tr>
</table>
</form>
</td>

<td width=400 colspan=3>
{{ page_content() }}
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