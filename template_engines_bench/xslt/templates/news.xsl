<t:stylesheet version="1.0" xmlns:t="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" xmlns:h="http://www.w3.org/1999/xhtml" xmlns:m="urn:model">

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