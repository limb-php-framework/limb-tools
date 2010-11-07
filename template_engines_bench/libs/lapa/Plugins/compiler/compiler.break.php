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

function lapa_compiler_break($act, $parser)
{
    $depth = $parser->_parseExpression(); 
    return 'break ' . ( (int)$depth > 0 ? (int)$depth: '' ) . ";\n";
}