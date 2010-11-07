<?xml version='1.0' encoding='utf-8' ?>
<t:stylesheet version="1.0" xmlns:t="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" xmlns:h="http://www.w3.org/1999/xhtml" xmlns:m="urn:model">

<t:template match="/">
	<t:apply-templates select="*" />
</t:template>

<!-- page -->
<t:template match="/m:page">
	<style>
	 .even-0 { background: #eee}
	 .even-1 { background: #ddd }
	</style>
	<table width="800">
		<tr>
			<td width="200">
				<t:apply-templates select="m:title" />
			</td>			
		  <t:apply-templates select="m:adverts" />			
		</tr>
		<tr valign="top">
			<td width="200">
				<t:apply-templates select="m:sections" />
				<t:apply-templates select="m:users" />
				<t:apply-templates select="m:poll" />
			</td>
			<td width="400" colspan="3">
				<t:call-template name="introduction" />
				<t:apply-templates select="m:news" />
			</td>
		</tr>
		<tr>
			<td colspan="4" align="center">
				<t:call-template name="copyright" />
			</td>
		</tr>
	</table>
</t:template>

<t:template match="/m:page/m:title">
	<table  bgcolor="#000000" cellspacing="2" cellpadding="4" border="0" width="100%">
	 <tr><td bgcolor="#ffffff">
	   <h1><t:apply-templates select="@*|node()" /></h1>
	 </td></tr>
  </table>
</t:template>

<t:template name="introduction">
	<h2>Introduction</h2>
	<p>Mr. Treehorn draws a lot of water in this town. You don't draw shit, Lebowski. Now we got a nice, quiet little beach community here, and I aim to keep it nice and quiet. So let me make something plain. I don't like you sucking around, bothering our citizens, Lebowski. I don't like your jerk-off name. I don't like your jerk-off face. I don't like your jerk-off behavior, and I don't like you, jerk-off.</p>
</t:template>

<t:template name="copyright">
	<hr/>
	<small>
		<i>	Lebowski test (Copyleft) korchasa<br/>
			based on BlitzTest (Alexey A. Rybak).<br/>
			Texts are taken from IMDB.com, Memorable Quotes from "The Big Lebowski" (Ethan &amp; Joel Coen, 1998). <br/>
		</i>
	</small>
</t:template>

<!-- /page -->

<!-- adverts -->

<t:template match="/m:page/m:adverts">
	<t:apply-templates select="m:item" />
</t:template>

<t:template match="/m:page/m:adverts/m:item">
 <td>
  <table bgcolor="#000000" cellspacing="2" cellpadding="2" border="0" width="100%">
	  <tr><td>
	  	<t:apply-templates select="m:section" />
	  </td></tr>
  	<tr><td bgcolor="#ffffff"><small>
	  	<a href="{m:url}">
  			<t:apply-templates select="m:title" />
  		</a>
  	</small></td></tr>
  </table>
 </td>
</t:template>

<t:template match="/m:page/m:adverts/m:item/m:section">
	<font color="#ffffff">
		<b>
			<t:apply-templates select="@*|node()" />
		</b>
	</font>
</t:template>

<!-- /adverts -->

<!-- sections -->

<t:template match="/m:page/m:sections">
	<table>
		<t:apply-templates select="m:item" />
	</table>
</t:template>

<t:template match="/m:page/m:sections/m:item">
	<tr>
		<td>
			<t:attribute name="class">
				<t:apply-templates select="m:even" />
			</t:attribute>
			<font><b>
				<a href="/section.phtml?id={m:id}">
					<t:apply-templates select="m:name" />
				</a>
				<t:apply-templates select="m:rip" />
			</b></font>
		</td>
	</tr>
</t:template>

<t:template match="/m:page/m:sections/m:item/m:even">
	<t:text>even-</t:text>
	<t:apply-templates select="@*|node()" />
</t:template>

<t:template match="/m:page/m:sections/m:item/m:rip">
	<t:text> </t:text>
	<font color="#999999">R.I.P.</font>
</t:template>

<!-- /sections -->

<!-- users -->

<t:template match="/m:page/m:users">
	<p>
		<b>Users</b>: <t:value-of select="m:TOTAL"/><br/>
		<b>Online</b>: <t:value-of select="m:ONLINE/m:count"/><br/>
		<t:apply-templates select="m:ONLINE" />
	</p>
</t:template>

<t:template match="/m:page/m:users/m:ONLINE">
	<small><i>
		<t:apply-templates select="m:item" />
	</i></small>
</t:template>

<t:template match="/m:page/m:users/m:ONLINE/m:item">
	<t:text> </t:text>
	<a href="/user.phtml?id={m:id}">
		<t:apply-templates select="m:name"/>
	</a>
</t:template>

<!-- /users -->

<!-- polls -->

<t:template match="/m:page/m:poll">
	<p>
		<b>
			<t:apply-templates select="m:TITLE"/>
		</b><br/>
		<small>
			<t:apply-templates select="m:QUESTION"/>
		</small>
	</p>
	<form>
		<table>
			<t:apply-templates select="m:ANSWERS"/>
			<tr><td>
				<input type="submit" name="OK" value="{m:BUTTON}"/>
			</td></tr>
		</table>
	</form>
</t:template>

<t:template match="/m:page/m:poll/m:ANSWERS">
	<t:apply-templates select="m:item"/>
</t:template>

<t:template match="/m:page/m:poll/m:ANSWERS/m:item">
	<tr><td><small>
		<input type="radio"/>
		<t:apply-templates select="@*|node()"/>
	</small><br/></td></tr>
</t:template>

<!-- /polls -->

<!-- news -->

<t:template match="/m:page/m:news">
	<h2>News</h2>
	<t:apply-templates select="m:item" />
</t:template>

<t:template match="/m:page/m:news/m:item">
	<b>
		<t:apply-templates select="m:time"/>
		<t:apply-templates select="m:title"/>
	</b><br/>
	<t:apply-templates select="m:short" />
</t:template>

<t:template match="/m:page/m:news/m:item/m:time">
	<t:apply-templates select="@*|node()"/>
	<t:text> </t:text>
</t:template>

<t:template match="/m:page/m:news/m:item/m:short">
  	<small>
  		<t:apply-templates select="@*|node()"/>
  		<a href="/news.phtml?id={../m:id}">[ read full story ]</a>
  	</small><br/>
</t:template>

<!-- /news -->

</t:stylesheet>