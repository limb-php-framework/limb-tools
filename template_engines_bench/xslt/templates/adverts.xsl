<t:stylesheet version="1.0" xmlns:t="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" xmlns:h="http://www.w3.org/1999/xhtml" xmlns:m="urn:model">

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

</t:stylesheet>