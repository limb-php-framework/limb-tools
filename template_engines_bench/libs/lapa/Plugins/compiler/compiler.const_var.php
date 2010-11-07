<?php 
/**
 * const
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

function lapa_compiler_const_var($params, $parser)
{
    if (!$parser->allow_constants) {
        $parser->warning_error('Использование констант запрещено в текущих настройках приложения');
        return "''";
    }
    if ( count($params) > 1 ) {
        if ( defined($params[1]) ) {
            return $params[1];
        }
    }
    return "''";
}
