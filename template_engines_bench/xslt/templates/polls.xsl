<t:stylesheet version="1.0" xmlns:t="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" xmlns:h="http://www.w3.org/1999/xhtml" xmlns:m="urn:model">

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

</t:stylesheet>