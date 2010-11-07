<table width=800>
<tr><td width=200>

<table bgcolor=#000000 cellspacing=2 cellpadding=4 border=0 width=100%>
<tr><td bgcolor=#ffffff><h1>PHPExample</h1></td></tr>
</table>
</td>
<? 
$random_keys =  array_rand($_DATA['ADVERTS'],3);

foreach($random_keys as $i) {
	$idata = & $_DATA['ADVERTS'][$i];
	?><td width=200 valign=top>
    <table bgcolor=#000000 cellspacing=2 cellpadding=2 border=0 width=100%>
    <tr><td><font color=#ffffff><b><?=$idata['section']; ?></b></font></td></tr>
    <tr><td bgcolor=#ffffff><small><a href="<?=$idata['url']; ?>"><?=$idata['title']; ?></a></small>
    </td></tr>
    </table><?php
}
?>
</td>
</tr>

<tr valign=top>
<td width=200>
<table width=100% cellpadding=3>
<?
$n_sections = count($_DATA['SECTIONS']);

for($i=0; $i<$n_sections; $i++) {
    $sdata = & $_DATA['SECTIONS'][$i];
?>
<tr>
<td bgcolor=#<? echo ($i%2) ? 'eeeeee' : 'dddddd'; ?>>
<font color=#ffffff><b>
<a href="/section.phtml?id=<?=$sdata['id'];?>"><?=$sdata['name'];?></a>
<? if($sdata['rip']) { ?><font color=#999999>R.I.P.</font><? } ?></font>
</td>
</tr
<? } ?>
</table>

<p><b>Users</b>: <?=$_DATA['STAT']['TOTAL']; ?><br>

<?
$n_online = count($_DATA['STAT']['ONLINE']); 
?>

<b>Online</b>: <?=$n_online;?><br>

<small>
<i>
<? 
for($i=0;$i<$n_online; $i++) { 
    $udata = $_DATA['STAT']['ONLINE'][$i]; ?>
<a href="/user.phtml?id=<?=$udata['id'];?>"><?=$udata['name'];?></a>
<? } ?>
</i>
</small>

</small>
<p><b><?=$_POLL['TITLE'];?></b><br>
<small>
<?=$_POLL['QUESTION'];?>
<small><br>
<form method=post>
<table>
<? $n_answer = count($_POLL['ANSWERS']);
for($i=0;$i<$n_answer;$i++) { ?>
<tr valign=center><td><small><input type=radio name=a><?=$_POLL['ANSWERS'][$i];?><br></td></tr>
<? } ?>
<tr><td align=center><input type=submit name="OK" value="<?=$_POLL['BUTTON'];?>"></td></tr>
</table>
</form>
</td>

<td width=400 colspan=3>
<h2>Introduction</h2>
<p>Mr. Treehorn draws a lot of water in this town. You don't draw shit, Lebowski. Now we got a nice, quiet little beach community here, and I aim to keep it nice and quiet. So let me make something plain. I don't like you sucking around, bothering our citizens, Lebowski. I don't like your jerk-off name. I don't like your jerk-off face. I don't like your jerk-off behavior, and I don't like you, jerk-off.</p>
<h2>News</h2>
<?php
$n_news = count($_NEWS);
for($i=0;$i<$n_news;$i++) {
  $item = $_NEWS[$i];
  ?><b><?=$item['time'];?> <?=$item['title'];?></b><br>
  <small><?=$item['short'];?><a href="/news.phtml?id=<?=$item['id'];?>">[ read full story ]</a></small>
  <br><?php
} ?>
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