<?php
/**
 * wrap
 * 
 * Lapa Template ToolKit
 * 
 * PHP versions 5
 *
 * @package    Lapa
 * @subpackage LapaPluginsCompiler
 * @author     Stepanov Sergey <StepanovSergey@tut.by>
 * @copyright  2007 Stepanov Sergey
 * @license    http://www.opensource.org/licenses/lgpl-license.php LGPL
 */
 
function lapa_compiler_wrap($act, $parser)
{
    $plugin_name    = 'wrap';
    
    if ( 1 == $act ) {
        $attr = $parser->parseDirectiveAttributes();
        $property_name = isset($attr['name']) ? trim($attr['name'], "'") : 'default';
        
        $parser->setPluginProperty($plugin_name, $property_name, '', $parser->templateLine());
        // создадим новый буфер для php кода
        $parser->obStart($plugin_name);
        $parser->openBlock($plugin_name, $property_name);
    } else {
        lapa_compiler_endwrap($act, $parser);
    }
    
    return null;
}

function lapa_compiler_endwrap($act, $parser)
{
    $plugin_name    = 'wrap';
    $property_name  = '';
    if ( !$parser->getBlock($plugin_name, $property_name) || !$parser->closeBlock($plugin_name)) {
        throw new LapaEngineException('Syntax: "capture", проверьте уровень вложенности, cтрока %s', $parser->templateLine() );
    }        
    $temp_output = $parser->obGetContents($plugin_name);
    $parser->setPluginProperty($plugin_name, $property_name, $temp_output, null, 0);
    return null;
}


function lapa_compiler_wrap_var($params, $parser)
{
    $plugin_name    = 'wrap';
    $property_name  = count($params) > 1 ? $params[1] : 'default';
    $parser->printVar(false);
    if ( $parser->isPluginProperty($plugin_name, $property_name) ) {
        return $parser->getPluginProperty($plugin_name, $property_name);
    }else {
        $parser->warning_error('Блок "wrap->%s" не зарегистрирован , строка %s', $property_name, $parser->templateLine() );
        return "''";
    }
}