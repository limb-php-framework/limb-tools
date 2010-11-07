<t:stylesheet version="1.0" xmlns:t="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" xmlns:h="http://www.w3.org/1999/xhtml" xmlns:m="urn:model">

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

</t:stylesheet>