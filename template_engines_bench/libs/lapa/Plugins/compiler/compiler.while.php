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
 
function lapa_compiler_while($act, $parser)
{
    if ( 1 == $act ) {
        $plugin_name = 'while'; 
        
        $attr     = $parser->parseDirectiveAttributes();
        $parser->openBlock($plugin_name, array('if' => false, 'name'=> $parser->templateLine()) );
        $str_name = "\$_while[{$parser->templateLine()}]";
        $result   = $str_name . "['iteration'] = 0;\n";
        $params   = $parser->_parseExpression();
        
        if ( key_exists('max', $attr) ) {
            $result .= "{$str_name}['max'] = (int){$attr['max']};\n";
            if ( '' ==  $params ) {
                $params = 'true';
            }
        }else {
            if ( '' ==  $params ) {
                throw new LapaEngineException('Syntax: "while", пустое выражение, cтрока %s', $parser->templateLine() );
            }
        }
        
        $result .= 'while (' . $params . "): \n";
        if ( key_exists('max', $attr) ) {
            $result .= "    if ( {$str_name}['iteration'] > {$str_name}['max'] - 1 ): break; endif;";
        }
        $result .= "    ++{$str_name}['iteration'];";
    }else {
        $result = lapa_compiler_endwhile($act, $parser);
    }
    return $result;
}

function lapa_compiler_whileelse($act, $parser)
{
    $plugin_name   = 'while';
    
    if ( $parser->getBlock($plugin_name, $plugin_params) && !$plugin_params['if'] ) {
        $parser->setBlock($plugin_name, array('if' => true, 'name' => $plugin_params['name']) );
    }else {
        throw new LapaEngineException('Syntax: "whileelse", проверьте уровень вложенности, cтрока %s', $parser->templateLine() );
    }
    $str_name = "\$_while[{$plugin_params['name']}]";
    return "endwhile;\nif ({$str_name}['iteration'] == 0 ):\n";
}

function lapa_compiler_endwhile($act, $parser)
{
    $plugin_name   = 'while';
    if ( $parser->getBlock($plugin_name, $plugin_params) ) {
        if ( $plugin_params['if'] ) {
            $result = 'endif;';
        }else {
            $result = "\nendwhile;";
        }
    }else {
        throw new LapaEngineException('Syntax: "whileelse", проверьте уровень вложенности, cтрока %s', $parser->templateLine() );
    }
    $parser->closeBlock($plugin_name); 
    return $result;

}