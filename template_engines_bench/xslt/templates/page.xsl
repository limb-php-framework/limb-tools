<t:stylesheet version="1.0" xmlns:t="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" xmlns:h="http://www.w3.org/1999/xhtml" xmlns:m="urn:model">

<!-- page -->

<t:include href="sections.xsl" />
<t:include href="adverts.xsl" />
<t:include href="users.xsl" />
<t:include href="polls.xsl" />

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

</t:stylesheet>