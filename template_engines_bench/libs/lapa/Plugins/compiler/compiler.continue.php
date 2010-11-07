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

function lapa_compiler_continue($act, $parser)
{
    $depth = $parser->_parseExpression(); 
    return 'continue ' . ( (int)$depth > 0 ? $depth: '' ) . ";\n";
}