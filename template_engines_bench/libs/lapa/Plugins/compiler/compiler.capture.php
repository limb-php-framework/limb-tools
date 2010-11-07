<?php 
/**
 * capture
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

function lapa_compiler_capture($act, $parser)
{
    $plugin_name    = 'capture';
    if ( 1 == $act) {
        $attr = $parser->parseDirectiveAttributes();

        if (isset($attr['assign'])) {
            $parser->openBlock($plugin_name, array('var' => trim($attr['assign'], "'")));
        }else {
            $property_name = isset($attr['name']) ? trim($attr['name'], "'"): 'default';
            $parser->setPluginProperty($plugin_name, $property_name, '',  $parser->templateLine());
            $parser->openBlock($plugin_name, array('name' => $property_name));
        }
        return "ob_start();";
    }else {
        return lapa_compiler_endcapture($act, $parser);
    }             
}

function lapa_compiler_endcapture($act, $parser)
{
    $plugin_name   = 'capture';
    $plugin_params = array();
    if ( !$parser->getBlock($plugin_name, $plugin_params) ) {
        throw new LapaEngineException('Syntax: "capture", проверьте уровень вложенности, cтрока %s', $parser->templateLine() );
    }
    if ( key_exists('name', $plugin_params) ) { 
        $property_name = $plugin_params['name'];
        $result  = "\$_var_capture['$property_name'] = ob_get_contents(); ob_end_clean();\n";
    }elseif ( key_exists('var', $plugin_params) ) {
        $property_name = $plugin_params['var'];
        if ( '$' == $property_name[0] ) {
            $result = $property_name . " = ob_get_contents(); ob_end_clean();\n";
        }else {
            $result = "\$_var['$property_name'] = ob_get_contents();\nob_end_clean();\n";
        }
    }
    $parser->closeBlock($plugin_name); 
    return $result;
}

function lapa_compiler_capture_var($params, $parser)
{
    $plugin_name    = 'capture';
    $property_name  = count($params) > 1 ? $params[1] : 'default';
    if ( $parser->isPluginProperty($plugin_name, $property_name) ) {
        return "\$_var_capture['$property_name']";
    }else {
        $parser->warning_error('Блок "capture->%s" не зарегистрирован , строка %s', $property_name, $parser->templateLine() );
        return "''";
    }
}