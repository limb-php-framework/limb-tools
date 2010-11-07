<t:stylesheet version="1.0" xmlns:t="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" xmlns:h="http://www.w3.org/1999/xhtml" xmlns:m="urn:model">

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

</t:stylesheet>