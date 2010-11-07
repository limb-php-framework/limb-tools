<?php 
/**
 * sub
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

function lapa_compiler_sub($act, $parser)
{
    $plugin_name    = 'sub';
    if ( 1 == $act ) {
        $attr = $parser->parseDirectiveAttributes();
        $property_name = key_exists('name', $attr) ? trim($attr['name'], "'") : 'default';
        if ( $parser->isPluginProperty($plugin_name, $property_name) ) {
            $parser->warning_error('sub "%s" уже существует и будет переписан, строка %s, шаблон "%s"',$property_name, $parser->templateLine(), $parser->templateName());
        }
        $parser->setPluginProperty($plugin_name, $property_name, '', $parser->templateLine());
        $parser->obStart($plugin_name);
        $parser->openBlock($plugin_name, array('name' => $property_name));
        return null;
    } else {
        return lapa_compiler_endsub($act, $parser);
    }
}

function lapa_compiler_endsub($act, $parser)
{
    $plugin_name    = 'sub';
    $plugin_params = array();
    
    if ( !$parser->getBlock($plugin_name, $plugin_params) ) {
        throw new LapaEngineException('Syntax: "sub", проверьте уровень вложенности, cтрока %s', $parser->templateLine());
    }
    $property_name = $plugin_params['name'];
    
    $result = "\$$property_name = create_function ('\$params, & \$_var, & \$_var_local',  ' ";
    $sub_header = "
    \$old = array();
    foreach (\$params as \$var_name => \$var_value):
        if ( key_exists(\$var_name, \$_var) ):
            \$old[\$var_name] = \$_var[\$var_name]; 
            unset(\$_var[\$var_name]);
            \$_var[\$var_name] = \$var_value;
        endif;
    endforeach;";
    $sub_footer = "
    foreach (\$old as \$var_name => \$var_value):
        unset(\$_var[\$var_name]);
        \$_var[\$var_name] = \$var_value;
    endforeach;";    

    $temp_output = $parser->obGetContents($plugin_name);
    $result .= $sub_header . $parser->escapeString($temp_output) . $sub_footer . '\');';
   
    $parser->closeBlock($plugin_name); 
    
    return $result;
}

function lapa_compiler_call_sub($act, $parser)
{
    $plugin_name    = 'sub';
    $result         = '';
    
    $attr = $parser->parseDirectiveAttributes();
    $property_name = key_exists('name', $attr) ? trim($attr['name'], "'") : 'default';
    unset($attr['name']);
    if ( !$parser->isPluginProperty($plugin_name, $property_name) ) {
        $parser->warning_error('Syntax: данных для "%s" не существует, строка %s, шаблон "%s"',$property_name, $parser->templateLine(), $parser->templateName());
        $result = '\'\'';
    }else {
        $result = "\$$property_name( array(";
        foreach ($attr as $var_name => $var_value) {
            $result .= "'$var_name' => $var_value, "; 
        } 
        $result .= '), $_var, $var_local );';
    }
    return $result;
    
}