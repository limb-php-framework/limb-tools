<table width=800>
<tr><td width=200>

<table bgcolor=#000000 cellspacing=2 cellpadding=4 border=0 width=100%>
<tr><td bgcolor=#ffffff><h1>BlitzExample</h1></td></tr>
</table>
</td>

{{ BEGIN adverts }}

<td width=200 valign=top>
<table bgcolor=#000000 cellspacing=2 cellpadding=2 border=0 width=100%>
<tr><td><font color=#ffffff><b>{{ $section }}</b></font></td></tr>
<tr><td bgcolor=#ffffff><small><a href="{{ $url }}">{{ $title }}</a></small>
</td></tr>
</table>

{{ END }}

</td>
</tr>

<tr valign=top>
<td width=200>
<table width=100% cellpadding=3>

{{ BEGIN sections }}

<tr>
<td bgcolor={{ BEGIN odd }}#eeeeee{{ END }}{{ BEGIN even }}#dddddd{{ END }}
<font color=#ffffff><b>
<a href="/section.phtml?id={{ $id }}">{{ $name }}</a>
{{ BEGIN rip }}<font color=#999999>R.I.P.</font></font>{{ END }}
</td>
</tr>

{{ END }}

</table>

<p><b>Users</b>: {{ $num_total }}<br>

<b>Online</b>: {{ $num_online }}<br>

<small>
<i>
{{ BEGIN  users }}
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
{{ BEGIN poll_answers }}
<tr valign=center><td><small><input type=radio name=a>{{ $answer }}<br></td></tr>
{{ END }}

<tr><td align=center><input type=submit name="OK" value="{{ $poll_button }}"></td></tr>
</table>
</form>
</td>

<td width=400 colspan=3>
{{ BEGIN news }}
<b>{{ $time }} {{ $title }}</b><br>
<small>{{ $short }}<a href="/news.phtml?id={{ $id }}">[ read full story ]</a></small>
<br>
{{ END }}

</td>
</tr>
<tr>
<td colspan=4 align=center>
<hr>
<small>
<i>BlitzExample (Copyleft) Alexey A. Rybak, 2005.<br>
Texts are taken from IMDB.com, Memorable Quotes from "The Big Lebowski" (Ethan & Joel Coen, 1998). <br>

You are welcome to send any suggestions or comments to <b>raa@phpclub.net</b> </i>

</td>
</tr>
</table>
