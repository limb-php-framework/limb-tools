<?php
/**
 * assign
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

function lapa_compiler_assign($act, $parser)
{
    $link = '';
    $attr = $parser->parseDirectiveAttributes();
    if ( !key_exists('var', $attr) ) 
        throw new LapaEngineException('В функции "assign" не задан параметр "var". cтрока %s.', $parser->templateLine());
    if ( !key_exists('value', $attr) )
        throw new LapaEngineException('В функции "assign" не задан параметр "value". cтрока %s.', $parser->templateLine());
    
    if (key_exists('link', $attr) && $attr['link'] && $attr['value'][0] == '$') 
        $link = '&';
        
    if ($attr['var'][0] == '$') {
        return $attr['var'] . " = $link {$attr['value']};";
    }
    return "\$this->assign({$attr['var']}, {$attr['value']});";
}