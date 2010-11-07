{*debug.tpl.php* }

{capture assign=$$debug_output}
<html>
<head>
    <title>Lapa Debug Console</title>
</head>
<body>
<h1>Lapa Debug Console</h1><br />
{foreach from = $$debug_info key = '_ldebug_key' item = '_ldebug_item' }   
{if $_ldebug_item.id == 0}
{continue 1}
{endif}
{='&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'|str_repeat:$_ldebug_item.depth}������:&nbsp;{$_ldebug_item.template}<br />
��� �������:&nbsp;<b>{$_ldebug_item.type}</b>{if isset($_ldebug_item.cache) and $_ldebug_item.cache}&nbsp;<b>���</b>{endif}<br />
{if isset($_ldebug_item.lex)}
����� �������:&nbsp;<i>({$_ldebug_item.lex.lex_time|string_format:'%.5f'})(ms)</i><br />
���������� ���������� ������ ��� ������:&nbsp;<i>({$_ldebug_item.debug_parser_count})</i><br />
���������� �����:&nbsp;<i>({$_ldebug_item.lex.lex_line})</i><br /> 
{endif}
{if isset($_ldebug_item.debug_parser_time)}
����� ������: &nbsp;<i>({$_ldebug_item.debug_parser_time|string_format:'%.5f'})(ms)</i><br />
{endif}
{if isset($_ldebug_item.debug_compile_time)}
����� ����� ���������� (������� ������������� �������, insert_template � ������ �������):&nbsp;<i>({$_ldebug_item.debug_compile_time|string_format:'%.5f'})(ms)</i><br />
{endif}
{if isset($_ldebug_item.debug_compile_path)}
������ ���� � ����� php:&nbsp;<i>({$_ldebug_item.debug_compile_path|escape:'html' })</i><br />
{endif}
{if isset($_ldebug_item.debug_cache_try)} 
������� ������ ����:&nbsp;<i>({if $_ldebug_item.debug_cache_try}�������{else}���������{endif})</i><br />
{endif}
{if isset($_ldebug_item.debug_cache_read_time)}
����� ������ ����:&nbsp;<i>({$_ldebug_item.debug_cache_read_time|string_format:'%.5f'})(ms)</i><br />
{endif}
{if isset($_ldebug_item.debug_cache_write_time)}
����� ������ ����:&nbsp;<i>({$_ldebug_item.debug_cache_write_time|string_format:'%.5f'})(ms)</i><br />
{endif}
{if isset($_ldebug_item.debug_cache_path)}
������ ���� � ����� ����:&nbsp;<i>({$_ldebug_item.debug_cache_path|escape:'html'})</i><br />
{endif}
{if isset($_ldebug_item.debug_exes_time)}
����� ����� ���������� (������� �����������):&nbsp;<i>({$_ldebug_item.debug_exes_time|string_format:'%.5f'})(ms)</i><br />
{endif}{if isset($_ldebug_item.debug_all_time)}
����� ����������� �����:&nbsp;<i>({$_ldebug_item.debug_all_time|string_format:'%.5f' })(ms)</i><br />
{endif }<hr />

{if not empty($_ldebug_item.debug_local_var)} 

<h3>Local Variable</h3>
<table>
{ foreach from=$_ldebug_item.debug_local_var key=$_ldebug_var_key item = $_ldebug_var_item}
<tr><th>{$_ldebug_var_key|escape:'html'}</th>
<td>{$_ldebug_var_item|debug_print_var }</td></tr>
{ foreachelse }
        <tr><td><p>no template local variables</p></td></tr>
{endforeach}
</table><hr />
{endif}
{if not empty($_ldebug_item.debug_var) }
<h3>Global Variable</h3>
<table>
{ foreach from = $_ldebug_item.debug_var key=$_ldebug_var_key item = $_ldebug_var_item}
<tr><th>{$_ldebug_var_key|escape:'html'}</th>
<td>{$_ldebug_var_item|debug_print_var}</td></tr>
{ foreachelse }
        <tr><td><p>no template global variables</p></td></tr>
        {endforeach}
</table><hr />
{endif }
{endforeach}
<pre>
{get print_r($$debug_info) }
</pre>
</body>
</html>
{ endcapture}
<script type="text/javascript">
// <![CDATA[
    if ( self.name == '' ) {ldelim}
       var title = 'Console';
    {rdelim}
    else {ldelim}
       var title = 'Console_' + self.name;
    {rdelim}
    _lapa_console = window.open("",title.value,"width=680,height=600,resizable,scrollbars=yes");
    _lapa_console.document.write('{$$debug_output|escape:'javascript'}');
    _lapa_console.document.close();
// ]]>
</script>