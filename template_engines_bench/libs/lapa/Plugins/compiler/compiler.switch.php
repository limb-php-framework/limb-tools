<?php 
/**
 * switch
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

function lapa_compiler_switch($act, $parser)
{
    if ( 1 == $act) {
        $plugin_name = 'switch';
        
        $attr = $parser->parseDirectiveAttributes();
        $result = $parser->_parseExpression();
        $parser->openBlock($plugin_name, false);
        if ('' != $result ) {
            return 'switch (' . $result . '):' . "\n";
        }else {
            throw new LapaEngineException('”кажите параметры дл€ SWITCH в строке %s.', $parser->templateLine());
        }
    }else {
        return lapa_compiler_endswitch($act, $parser);
    }
}

function lapa_compiler_endswitch($act, $parser)
{
    $plugin_name = 'switch';
    if ( !$parser->getBlock($plugin_name, $plugin_params) ) {
        throw new LapaEngineException('Syntax: "switch", проверьте уровень вложенности, cтрока %s', $parser->templateLine() );
    }
    $parser->closeBlock($plugin_name); 
    return 'endswitch;' . "\n";
  
}

function lapa_compiler_case($act, $parser)
{
    if ( 1 == $act) {
        $plugin_name = 'switch';
        if ( !$parser->getBlock($plugin_name, $plugin_params) || $plugin_params) {
            throw new LapaEngineException('Syntax: "switch", проверьте уровень вложенности, cтрока %s', $parser->templateLine() );
        }
        $result = '';
        $params = $parser->parseParams(true);
        if (count($params) > 0) {
            foreach ($params as $val) {
                $result .= '    case ' . $val . ':' . "\n";
            }
            if ( key_exists('break', $parser->directives) && !$parser->directives['break']) {
                $result .= 'break;' . "\n";
            }
            return $result;
        }
        throw new LapaEngineException('Syntax: "switch", пустое выражение, cтрока %s', $parser->templateLine() );   
    }else {
        return lapa_compiler_endcase($act, $parser);
    }
}

function lapa_compiler_endcase($act, $parser)
{
    $plugin_name = 'switch';
    if ( !$parser->getBlock($plugin_name, $plugin_params) || $plugin_params) {
        throw new LapaEngineException('Syntax: "switch", пустое выражение, cтрока %s', $parser->templateLine() );
    }
    $parser->setBlock($plugin_name, true);
    return '    default:' . "\n";
}