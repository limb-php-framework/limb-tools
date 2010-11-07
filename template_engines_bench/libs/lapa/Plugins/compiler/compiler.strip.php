<?php 
/**
 * strip
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
function lapa_compiler_strip($act, $parser)
{
    $plugin_name    = 'strip';
    $buff = '';
    if (1 == $act) {
        $parser->openBlock($plugin_name);
        $parser->obStart();            
    }else {
        return lapa_compiler_endcapture($act, $parser);
    }
    return;
}

function lapa_compiler_endstrip($act, $parser)
{
    $plugin_name = 'strip';
    if ( !$parser->getBlock($plugin_name) || !$parser->closeBlock($plugin_name) ) {
        throw new LapaEngineException('Syntax: "strip", проверьте уровень вложенности, cтрока %s', $parser->templateLine() );
    }
    
    return preg_replace('![\t ]*[\r\n]+[\t ]*!', '', $parser->obGetContents() );
}