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

function lapa_compiler_php_code($act, $parser)
{
    if ($act == 1) {
        $buff = '';
        if (!isset(LapaEngineParser::$_plugins_property['php_code'])) {
            LapaEngineParser::$_plugins_property['php_code'] = array();
        }
        $attr = $parser->parseDirectiveAttributes();
        $name = isset($attr['name']) ? $attr['name']: 'default';
        $parser->newStackCommand(array('php_code', $name));
        $parser->_outputNew();
    }else {
        if ( (!$stack_commands = $parser->getStackCommand() ) || $stack_commands[0] != 'php_code' ) {
            throw new LapaEngineException('Неверный вызов "/php_code" cтрока %s.', $parser->templateLine());
        }
        /* вырезали буффер php кода, его надо еще вернуть */
        $buff = $parser->_outputGet();
        /* создаем переменную с значением */ 
        LapaEngineParser::$_plugins_property['php_code'][$stack_commands[1]] = $stack_commands[1];
        $buff  .= "\$_var_php_code['{$stack_commands[1]}'] = '" . preg_replace("/(?!\\\\)\\'/", '\\\'','<?php ' . str_replace('\\', '\\\\',$buff) . ' ?>') . "';";     
        $parser->removeStackCommand(); 
        LapaEngineParser::$_plugins_property['php_code'][$stack_commands[1]] = $stack_commands[1];
    }
    return $buff;
}

function lapa_compiler_php_code_var($params, $parser)
{
    if (key_exists($params[1], LapaEngineParser::$_plugins_property['php_code'])) {
        return "highlight_string( \$_var_php_code['{$params[1]}'])";
    }else {
        return "''";
    }
}
/**
 * Заглушка для {endphp_code}
 * 
 * 
 */
function lapa_compiler_endphp_code($act, $parser)
{
    return lapa_compiler_php_code(2, $parser);
}