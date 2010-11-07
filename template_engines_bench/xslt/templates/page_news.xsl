<?xml version='1.0' encoding='utf-8' ?>
<t:stylesheet version="1.0" xmlns:t="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" xmlns:h="http://www.w3.org/1999/xhtml" xmlns:m="urn:model">

<t:include href="page.xsl" />

<t:template match="/">
	<t:apply-templates select="*" />
</t:template>

<t:template name="introduction">
	<h2>Introduction</h2>
	<p>Mr. Treehorn draws a lot of water in this town. You don't draw shit, Lebowski. Now we got a nice, quiet little beach community here, and I aim to keep it nice and quiet. So let me make something plain. I don't like you sucking around, bothering our citizens, Lebowski. I don't like your jerk-off name. I don't like your jerk-off face. I don't like your jerk-off behavior, and I don't like you, jerk-off.</p>
</t:template>

<t:include href="news.xsl" />

</t:stylesheet>