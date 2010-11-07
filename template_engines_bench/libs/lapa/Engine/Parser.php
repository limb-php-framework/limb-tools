<?php
/**
 * Parser
 * 
 * Lapa Template ToolKit
 * 
 * PHP versions 5
 *
 * Copyright (c) 2000-2006 Stepanov Sergey
 * 
 * LICENSE:
 * 
 * This library is free software; you can redistribute it
 * and/or modify it under the terms of the GNU Lesser General
 * Public License as published by the Free Software Foundation;
 * either version 2.1 of the License, or (at your option) any
 * later version.
 * 
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
 * 
 * @package  Lapa
 * @subpackage LapaEngine
 * @author     Stepanov Sergey <StepanovSergey@tut.by>
 * @copyright  2007 Stepanov Sergey
 * @license    http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @version    v 0.5.7.3 2007/10/27
 */

require_once('Engine' . DIRECTORY_SEPARATOR . 'Base.php'); 
require_once('Engine' . DIRECTORY_SEPARATOR . 'Exception.php'); 
require_once('Engine' . DIRECTORY_SEPARATOR . 'LexisSmart.php');

/**
 *
 * The class receives for the line with a template, breaks into lexemes by means of Lexis
 * also creates php a code.
 * @author     Stepanov Sergey <StepanovSergey@tut.by>
 * @version    v 0.5.7.1 2007/10/17
 * @package    Lapa
 * @subpackage LapaEngine
 */

class LapaEngineParser extends LapaEngineBase
{
    /**
     * ��������� �����
     * 
     * $template_header['mod_function']['str_tr'] = array('function_name'   = > 'lapa_modifiler_str_tr', 'ext' =>false);
     * $template_header['functions']    = array('var_dump' = > 'var_dump', 'ext' =>true);
     *
     * @var array
     *
     */
    public $template_header = array('function' => array(), 'modifier' => array());
    
    /**
     * ������ ������� �����������, ���� ��� ����������
     * 
     * @var array
     */   
    protected $compiler_functions = array();
    
    
    
    protected $template_object;
    
    /**
     * ������ ���������� ������
     *
     * @var array
     */
    protected $tokens = array();
    
    /**
     * ������� ������
     *
     * @var string
     */
    protected $offset    = 0;
    
    /**
     * ������� �������������� ������ �������
     *
     * @var string
     */
    protected $template_line;
    
    /**
     * ���������� ������ � �������
     *
     * @var int 
     */
    protected $tokens_count = 0;
    
    /**
     * ������������ ������� � ������� �����������
     *
     * @var int
     */
    protected $count_loop = 0;
    
    /**
     * ��������� ����� ����������������� ����  
     *
     * @var string
     */
    protected $php_code = '';
    
    /**
     * �������� ����� ����������������� ����
     *
     * @var string 
     */
    protected $output_PHP;
    
    /**
     * ������� ����� (if, section � �.�.)
     * 
     * @var array
     */
    protected $stack_commands = array();
    
    /**
     * ���� ������������ ����������
     *
     * @var bool
     */
    protected $is_equal = false;
    
    /**
     * ��� �������
     *
     * @var string
     */
    protected $template_name;
    
    /**
    * ���� ���������� echo
    *
    * @var bool
    */
    protected $flag_print_vars = true;
    
    protected $template_block  = array();
        
    
    protected $plugins_property = array();
    
    
    
    public function isPluginProperty($pluginName, $propertyName)
    {
        if ( empty($pluginName) || !key_exists($pluginName, $this->plugins_property) ) {
            return false;
        }
        if ( empty($propertyName) || !key_exists($propertyName, $this->plugins_property[$pluginName]) ) {
            return false;
        }
        return true;
    }
    
    public function setPluginProperty($pluginName, $propertyName, $propertValue, $templateLine)
    {
        $this->plugins_property[$pluginName][$propertyName]['property']          = $propertValue;
        
        if ( $templateLine > 0 ) {
            $this->plugins_property[$pluginName][$propertyName]['template_line'] = $templateLine;
        }
    }
    
    public function getPluginProperty($pluginName, $propertyName)
    {
        if ( empty($pluginName) || !key_exists($pluginName, $this->plugins_property) ) {
            $this->warning_error('���������� "%s" �� ����������������, ������ %s, ������ "%s"', $pluginName, $this->template_line, $this->template_name);
            return null;
        }
        
        if ( empty($propertyName) || !key_exists($propertyName, $this->plugins_property[$pluginName]) ) {
            $this->warning_error('�������� "%s" �� ����������, ���������� "%s", ������ %s, ������ "%s"', $propertyName, $pluginName, $this->template_line, $this->template_name);
            return null;
        }
        
        return $this->plugins_property[$pluginName][$propertyName]['property'];
    }
    
    public function escapeString($code)
    {
        return preg_replace("%(?!\\\\)\\'%", '\\\'', str_replace('\\', '\\\\', $code));
    }
    
    public function printVar($value = null)
    {
        $flag = $this->flag_print_vars;
        if ( !is_null($value) ) {
            $this->flag_print_vars = (bool)$value;
        }
        return $flag;
    }
    
    /**
     * ������������ ����� ������ 
     * 
     * @param  $pluginName - ��� ����������
     * @return void
     */
    public function obStart($pluginName)
    {
        $this->output_PHP[] = $this->php_code;
        $this->php_code = '';
    }
    /**
     * ��������� ��������� ������������������ ������ 
     * 
     * @param $pluginName ��� ����������
     * @return string - ����������� ������
     */
    public function obGetContents($pluginName)
    {
        $count = count($this->output_PHP);
        if ($count > 0) {
            $buff = $this->php_code; 
            $this->php_code = array_pop($this->output_PHP);
        }else {  
            $this->warning_error('������ ��� php ���� �� ��������, ������ %s', $this->template_line); 
        }
        return $buff;
    }
    
    protected function obFlush()
    {
        if (count($this->output_PHP) > 0) {
            $buff = implode('', $this->output_PHP) . $this->php_code;
            $this->output_PHP = array(); 
        }else {
            $buff = $this->php_code;
        }
        $this->php_code = '';
        return $buff;
    }
    /**
     * ������� ��� �������� �� ��������� � �������
     *
     * ��������� ����� ����
     *
     * @param string $pluginName   - ��� �������
     * @param string $blockName    - ��� �����
     * @param mixed  $blockParams  - ��������� �����
     *
     * @return void
     */
    public function openBlock($pluginName, $blockParams)
    {
        $this->template_block[] =
         array('plugin_name'  => $pluginName,  'block_params' => $blockParams, 'block_line' => $this->template_line);
        
         return;
    }
    
    /**
     * ��������� ������� �����
     *
     * @param string $pluginName   - ��� �������
     * @param string $blockName    - ��� �����
     *
     * @return bool true ���� ����������
     */
    public function isBlock($pluginName)
    {
        $count = count($this->template_block);
        if ( $count > 0 ) {
            $block = & $this->template_block[$count - 1];
            if ( $block['plugin_name'] == $pluginName ) {
                return true;
            }
        }
        return false;
    }
    /**
     * ��������� �������� �����
     *
     * @param string $pluginName   - ��� �������
     * @param string $blockName    - ��� �����
     * @param mixed  $blockParams  - ���������� ������� ������ ��������� �����
     *
     * @return bool - true ���� ����������
     */
     
    public function getBlock($pluginName, &$blockParams)
    {
        $id = count($this->template_block) - 1;
        if ( $id >= 0 ) {
            if ( $this->template_block[$id]['plugin_name'] == $pluginName ) {
                $blockParams = $this->template_block[$id]['block_params'];
                return true;
            }
        }
        return false;
    }
    
    public function setBlock($pluginName, $blockParams)
    {
        $id = count($this->template_block) - 1;
        if ( $id >= 0 ) {
            if ( $this->template_block[$id]['plugin_name'] == $pluginName ) {
                $this->template_block[$id]['block_params'] = $blockParams;
                return true;
            }
        }
        return false;
    }
    
    /**
     * ��������� ����
     *
     * @param string $pluginName   - ��� �������
     * @param string $blockName    - ��� �����
     *
     * @return bool - true ���� ������
     */
    
    public function closeBlock($pluginName)
    {
        if ( $this->isBlock($pluginName) ) {
            array_pop($this->template_block);
            return true;
        }
        return false;
    }
    
    protected function checkBlocks()
    {
        return !count($this->template_block);
    }
    
    protected function errorBlocks()
    {
        $str_block = '������� ����� ...' . "\n";
        foreach ($this->template_block as $val) {
            $str_block .= $val['plugin_name'] . ' � ������ ' . $val['block_line'] . "\n";
        }
        throw  new LapaEngineException($str_block);
    }
    
    /**
     * ������� ������ �������
     *
     * ��������� ��� �������� �����������
     *
     * @return int
     */
    public function templateLine()
    {
        return $this->template_line;
    }

    /**
     * ��� �������� �������
     *
     * @return string
     */
    public function templateName()
    {
        return $this->template_name;
    }
    
    /**
     * ��������� ������ ���������� �� ��������� 
     * ������ ��� �������
     *
     * @param object $templateObject
     */
    public function setTemplateObject($template_object)
    {
        $this->template_object = $template_object;
    }
    /**
     * ������� �������� ������ ������ � ������ ������� php ���
     *
     * @param $template_name
     * @param $template_string
     *
     * @return string
     */
    public function parse($template_name, $template_string)
    {
        return $this->parseString($template_name, $template_string);
    }
    
    /**
     * ������� �������� ������ ������ � ������ ������� php ���
     *
     * @param string $template_name
     * @param string $template_string
     * @param bool   $process - �������������� ���� ��� ������� �������
     *
     * @return string
     */
    protected function parseString($template_name, & $template_string, $insertTemplate = false)
    {
        $this->template_name   = $template_name;
        $this->template_line   = 0;
        $this->tokens          = array();
        $this->stack_commands  = array();    
        $this->php_code        = '';
        
        $lexis_object = new LapaEngineLexisSmart();
        $lexis_object->__setOptions($this->options);
        
        $this->tokens = & $lexis_object->lex($template_string, $template_name);
            
        unset($template_string);
        
        if ( !is_array($this->tokens) ) {
            if ($lexis_object->isError() ) {
                // ���� ������
                throw $lexis_object->lastError();
            }
        }
        unset($lexis_object); 
        
        /**
         * ��������� ������� ��� �������
         */  
        $debug = $this->options['debugging'];       
        if ( $debug ) {
            /* ������� ������ ������� */
            $template_self_index = count($this->options['debug_info']) - 1;
            $this->options['debug_info'][$template_self_index]['lex'] = $this->tokens[1];
        }
        
        try {
            if (is_array($this->tokens) && count($this->tokens) >= 1) {
                $this->tokens[] = array(LT_END_LAPA_SCRIPT);
                $this->tokens_count = count($this->tokens);
                $this->tokens[] = array(LT_NEW_LINE, $this->tokens[1]['lex_line']);
                $this->tokens[] = array(0);
                $this->offset = 2;
            }
            if ( $debug ) {
                $debug_time_tmp = microtime(true);
            }
            
            $php_code = $this->compileTemplate($insertTemplate);            
            
            
        } catch (LapaEngineException $err) {
            if ( empty($err->template_name) ) {
                $err->setTemplate($this->template_name, $this->template_line);
            }
            throw $err;
        }
        
        /**
         * ������ ������ ������� <?php ?> | ?>   <?php 
         */
        $php_code = preg_replace('!(?:\<\?php\s*\?\>)|(?:\?\>\s*\<\?php)!s', '', $php_code);
            
        if ( $debug ) {
            $this->options['debug_info'][$template_self_index]['debug_parser_time']  = microtime(true) - $debug_time_tmp;
            $this->options['debug_info'][$template_self_index]['debug_parser_count'] = $this->tokens_count;    
        }
        
        /**
         * ��������� ���������, �������� �������� �������������, �������
         * ������������ ���� ��� �� �������
         */
        if (!$insertTemplate ) {
            $header = '';
            if ( count($this->template_header['modifier']) > 0 ) {
                $header .= '<?php $this->loadFunction(array(' . "\n    ";
                foreach ($this->template_header['modifier'] as $name => $function) {
                    if ( !$function['ext'] ) {
                        $header .= "'$name' => array('function_name' => '{$function['function_name']}', 'path' => '{$function['path']}'), \n    "; 
                    }
                }
                $header .= ') , \'modifier\'); ?>' . "\n";
            }
            if ( count($this->template_header['function']) > 0 ) {
                $header .= '<?php $this->loadFunction(array(' . "\n    ";
                foreach ($this->template_header['function'] as $name => $function) {
                    if ( !$function['ext'] ) {
                        $header .= "'$name' => array('function_name' => '{$function['function_name']}', 'path' => '{$function['path']}'), \n    "; 
                    }
                }
                $header .= ') , \'function\'); ?>';
            }
            $php_code = $header . $php_code;
        }
        return $php_code;
    }

    protected function compileTemplate($insertFile = false)
    {
        $br = "\n";
        $buff = '<?php ';
        
        for (; ; ++$this->offset) {
            $this->is_equal = false;
            
            // �������� ����� ����������
            if ( !$this->flag_print_vars ) {
                $this->flag_print_vars = true;
            }
            
            $token = $this->tokens[$this->offset];
            if (LAPA_INLINE_HTML == $token[0]) {
                $buff .= ' ?>' . $br . $token[1] . '<?php ';
                    $this->template_line = $token[2];
                    continue;
            }elseif (LT_NEW_LINE == $token[0]) {
                    $this->template_line = $token[1];
                    $buff .= $br;
                    continue;
            }
            
            switch ($token[0]) {
                case LT_VARIABLE:
                    $tmp = $this->_parseExpression(0);
                    if ($this->is_equal) {
                        $buff .= $tmp . ';';
                    }else {
                        $buff.= 'echo ' . $tmp . ';';
                    }
                    unset($tmp);
                break;
                case LT_CLASS_STATIC:
                    $buff.= 'echo ' . $this->_parseExpression(0) . ';';
                break;
                case LT_VALUE: case LT_BOOL:
                    $buff.= 'echo ' . $this->_parseExpression(0) . ';';
                break;
                case LT_VARIABLE_GLOBAL: 
                    $tmp_buff = $this->parseGlobalVariable() ;
                    $buff .= $this->flag_print_vars ? 'echo ' . $tmp_buff . ';': $tmp_buff;
                    unset($tmp_buff); 
                break;
                case LT_INSERT_TEMPLATE:
                    $this->php_code .= $buff; $buff = '';
                    $this->php_code .= $this->insertTemplate();
                break;
                case LT_INCLUDE_TEMPLATE:
                    $buff .= $this->includeTemplate();
                break;
                case LT_EXT_FUNCTION:
                    $this->php_code .= $buff; $buff = '';
                    $this->php_code .= $this->compileExtensFunction();
                break;
                case LT_END:
                    $this->php_code .= $buff; $buff = '';
                    $this->php_code .= $this->compileExtensFunctionEnd();
                break;
                case LT_CALL:
                    $buff .= $this->_parseExpression() . ';';
                break;
                case LT_GET:
                    $buff .= 'echo ' . $this->_parseExpression() . ';';
                break;
                case LT_DIRECTIVE:
                    $this->php_code .= $buff; $buff = '';
                    $this->parseEngineDirective();
                break;
                default:
                    if ($token[0] == LT_END_LAPA_SCRIPT) {
                        $this->php_code .= $buff . ' ?>'; $buff = '';
                        break 2;
                    }
                    throw  new LapaEngineException('Syntax: ������������ ������������� "%s", ����� %s, token#%s', $token[1], $this->template_line, $this->offset);
            }
            
            $token = $this->tokens[$this->offset += 1];
            switch ($token[0]) {
                case LAPA_INLINE_HTML:
                    $buff .= ' ?>' . $token[1]  . '<?php ';
                    $this->template_line = $token[2];
                break;
                case LT_NEW_LINE:
                    $this->template_line = $token[1];
                    $buff .= $br;
                break;
                case LT_END_LAPA_SCRIPT:
                    $this->php_code .= $buff . ' ?>'; $buff = '';
                    break 2;
                default:
                    //echo '<pre>'; print_r($this->tokens);
                    throw  new LapaEngineException('Syntax: ������������ ������������� "%s", ����� %s, token#%s', isset($token[1])? $token[1] : $token[0], $this->template_line, $this->offset);
            }
        } 
        // ��� ����� �������
        if ( !$this->checkBlocks() ) {
            $this->errorBlocks();
        }
        
        if (is_array($this->stack_commands)) {
            $stack_commands = end($this->stack_commands);
            if ( $stack_commands ) {
                throw  new LapaEngineException('We expect closing all blocks, the block %s it is not closed', $stack_commands[1]); 
            }
        }
               
        return $this->obFlush();
    }
    
    /** 
     * ������� ������� �������
     *
     */
    protected function insertTemplate()
    {   
        $attr = $this->parseDirectiveAttributes();
        if (isset($attr['file'])) {
            $file = trim($attr['file'], '\'');
        } else {
            throw new LapaEngineException('Syntax: the parameter "file", a line %s is missed', $this->template_line);
        }
        
        if ($file == '') {
            throw new LapaEngineException('Syntax: the parameter "file", a line %s is missed', $this->template_line);
        }    
        
        $source = ''; 
        
        if ( is_object($this->template_object) ) {
            if ( !$this->template_object->_getProcessResource($file, $source) ) {                
                throw new LapaEngineException('����������� ������ "%s" �� ������� �� ������, ������ %s', $file, $this->template_line); 
            }
        } else {
            throw new LapaEngineException('������ ���������� �� ������� �� ������');
        }
        $buff = "/* insert_template $file */?>\n";   
        
        $debug = $this->options['debugging'];
        
        if ( $debug ) {
            $last_parent_id = $this->options['debug_parent_id']; 
            $this->options['debug_info'][] = array(
                
                'id' => count($this->options['debug_info']), 
                'parent_id' => $last_parent_id, 
                'type' => 'insert_template', 
                'template' => $file, 
                'depth' => $this->options['debug_include_depth'] += 1);
            $template_self_index = count($this->options['debug_info']) - 1;
            /* ������� ������� ������� */
            $debug_time_tmp = microtime(true);
        }

        $parser = new LapaEngineParser();
        $parser->__setOptions($this->options);
        $parser->setTemplateObject($this->template_object);
        
        $buff .= $parser->parseString($file, $source);
        
        $this->template_header = array_merge_recursive($this->template_header, $parser->template_header);
        //print_r($this->template_header);
        if ( $debug ) {
            $this->options['debug_info'][$template_self_index]['debug_compile_time'] = microtime(true) - $debug_time_tmp;
            $this->options['debug_include_depth'] -= 1;
            $this->options['debug_parent_id'] = $last_parent_id;
        }
        
        $buff .= "<?php /* end insert_template $file */\n";            
        return $buff;
    }
    
    
    protected function compileExtensFunctionEnd() 
    {
        $this->offset += 1;
        $token = $this->tokens[$this->offset];
        if ($token[0] != LT_END_FUNCTION) {
            throw new LapaEngineException('Syntax: ������ %s', $this->template_line); 
        }
        $function = $token[1];
                
        if ($plugin_function = $this->isExtFunction($function) ) {
            if ( 'compiler' == $plugin_function[0] ) {
                return $plugin_function[1](2, $this);
            }
        }
        return "echo $plugin_function[1](null, \$this, 2);";
        
        
        throw new LapaEngineException('������� ����������� "%s" �� �������, ������ %s, token#%s', $function_name, $this->template_line, $this->offset);
    }
    
    protected function compileExtensFunction()
    {
        $function = $this->tokens[$this->offset][1];
         
        if ($plugin_function = $this->isExtFunction($function) ) {
            if ( 'compiler' == $plugin_function[0] ) {
                return $plugin_function[1](1, $this);
            }
            $attr = $this->parseDirectiveAttributes();
            $params = 'array(';
            if ( count($attr) ) {
                foreach ( $attr as $param_name => $param_val ) {
                    $params .= "'$param_name' => $param_val,";
                }
            }
            $params .= ')';
            return "echo $plugin_function[1]($params, \$this, 1);";            
        }
        throw new LapaEngineException('������� "%s" �� �������, ������ %s, token#%s', $function, $this->template_line, $this->offset);
        
    }
    
    public function parseEngineDirective()
    {
        $attr = $this->parseDirectiveAttributes();
                
        foreach ($attr as $key => $val) {
            $val = trim($val, "'\" \n");
            switch ($key) {
                case 'break':
                    $this->options['directives']['break'] = (bool)$val;
                break;
                case 'debug':
                    if ( 'list_tokens' == $val ) {
                        echo sprintf('<br />���������� ���������� ��������� �������� ������� � ������ %s, token #%s <br />' , $this->template_line, $this->offset);
                        echo '<pre>'; print_r($this->tokens); echo '</pre>';
                    }else if ( 'exit' == $val ){
                        exit(sprintf('<br />���������� ����� �������� �������� ������� � ������ %s, token #%s<br />' , $this->template_line, $this->offset));
                    }else if ('php_output' == $val) {
                        echo sprintf('<br />���������� ���������� ��������� �������� ������� � ������ %s, token #%s<br />' , $this->template_line, $this->offset);
                        $result = preg_replace('!\<\?php\s*\?\>!s', '', $this->php_code);
                        highlight_string($result);
                    }
                break;
                default:
                    $this->options['directives'][$key] = $val; 
            }
        }
        return;
    }

    /**
     * We form function of an insert in a template
     *
     * Syntax: include file='path to a file' cache=on id='mixed' lifetime=100 
     * It is possible to save separately in file from an end result
     *
     *@return void
     */
    private function includeTemplate()
    {
        $is_assign = false; 
        $buff      = '';
        
        $attr = $this->parseDirectiveAttributes();
        
        if (isset($attr['file'])) {
            $file = trim($attr['file'], '\'');
        } else {
            throw new LapaEngineException('Syntax: the parameter "file", a line %s is missed', $this->template_line);
        }
        
        if ($file == '') {
            throw new LapaEngineException('Syntax: the parameter "file", a line %s is missed', $this->template_line);
        }
        $is_assign = isset($attr['assign']);
        if ( $is_assign ) {
            $var_name = $attr['assign'];
            $buff .= 'ob_start();' . "\n";
        }
        
        $cache_buff = 'null';
        
        //if (isset($attr['cache'])) {
        //    $cache_buff = "array('cache'=>{$attr['cache']}, ";
        //    if (isset($attr['cache_id']))       $cache_buff .= "'cache_id'=>{$attr['cache_id']}, ";
        //    if (isset($attr['cache_lifetime'])) $cache_buff .= "'cache_lifetime'=>{$attr['cache_lifetime']}, ";
        //    $cache_buff .= ')';
        //}
        $params_vars = 'array(';
        foreach ($attr as $k=>$v) {
            switch ($k) {
                case 'file': case 'assign': case 'cache': case 'cache_id': case 'cache_lifetime': break;
                default:
                $params_vars .= "'$k'=>$v, ";
            }
        }
        $params_vars .= ')'; 
                
        $buff .= "\$this->_includeFile('$file', $cache_buff, $params_vars);\n";
        if ($is_assign) {
            if ($var_name[0] == '$') {
                $buff .= $var_name . ' = ob_get_contents(); ob_end_clean();' . "\n";
            }else {
                $buff .= "\$this->assign($var_name, ob_get_contents()); ob_end_clean();\n";
            }
        }
        return $buff;
    }
    
    /**
     * Return attributes of the instruction
     * loop name = 'test' value = $i from = $fr
     * /loop
     * (blanks are supposed)
     *
     * return array('name' => 'test', 'value' => $i1, 'from'=>$fr )
     * @return array
     */
    public function parseDirectiveAttributes($paramName = null)
    {
        $attr = array();
        
        while(true) {
            $token  = $this->tokens[$this->offset + 1]; // 
            if ( $token[0] != LT_OTHER || $this->tokens[$this->offset + 2][0] != LT_EQUAL) {
                break;
            }
            $this->offset += 2;
            
            $key = strtolower($token[1]);
            $buff = $this->_parseExpression();
            if ( !is_null($paramName) ) {
                if ( '_all_' ==$paramName) {
                    $attr['_all_'][] = $attr[$key];
                }else if ( $paramName == $key ) {
                    $attr[$paramName][] = $attr[$key];
                    continue;
                }
            }
            $attr[$key] = $buff;
        }
        return $attr;
    }
 
    /**
    * 
    * ���������: $var|modifier:100:'html'|modifier2|modifier3:1|modifier4:$var
    * (��� ������� ���������)
    *
    * @param string $variable - ��� ����������
    * @return string
    */
    protected function compileModificator($variable)
    {
        $this->offset += 1;
        $token = $this->tokens[$this->offset];
        
        if ($token[0] != LT_OTHER) {
            throw new LapaEngineException('Syntax: ������������ ��� ������������  "%s" � ������ %s', $token[1], $this->template_line);
        }
        $function = $token[1];
        /**
         * ���������� ����������� default
         */
        if ( 'default' == $function || 'd' == $function) {
            $this->offset += 1;
            if ($this->tokens[$this->offset][0] != LT_ARGUMENT) {
                throw new LapaEngineException('Syntax: �������� ������������ �������� ������� "default" � ������ %s', $this->template_line);
            }
            $params = $this->_parseExpression();
            return "($variable = empty($variable) ? $params: $variable)";
        } 
        $function_name = $this->isExtFunction($function, 'modifier', null, $this->options['functions_modifier']);
        $buff = "{$function_name[1]}($variable";
        $params = '';
        
        while (true) {
            $token = $this->tokens[$this->offset += 1];
            
            if ($token[0] == LT_ARGUMENT) { // ����� �������� ������������
                $params = $this->_parseExpression();
                if ($params != '') {
                    $buff .=  ', ' . $params;
                }else {
                    throw new LapaEngineException('������������� �������� ������������ %s', $this->template_line);
                }
            }else if ($token[0] == LT_MODIFICATOR) {
                $buff .= ')';
 
                $buff = $this->compileModificator($buff);
                break; // ������� �� �����
            }else{
                $buff .= ')';
                $this->offset -= 1;
                break;
            }
        } 
        return $buff;
    }
    
    protected function compileFunction()
    {
        $access_params = array(LT_BOOL, LT_VALUE, LT_OTHER);
        
        $function_name = $this->tokens[$this->offset][1];
        $state = 1; $params = array($function_name);
        
        $function = $this->isExtFunction($function_name, null, 'var');
        
        while ( true ) {
            ++$this->offset;
            if ($state == 1) {
                if (LT_ARRAY == $this->tokens[$this->offset][0]) {
                    $state = 0;
                }else {
                    --$this->offset; 
                    break;
                }
            }else{
                $state = 1;
                $token = $this->tokens[$this->offset];
                if ( !in_array($token[0], $access_params) ) {
                    throw new LapaEngineException('Syntax: ��������� ��������� �������, ������ %s, #%s', $this->template_line, $token[1]);
                }
                if ( 'compiler' == $function[0] ) {
                    $params[] = $token[1];
                }else{
                    
                }
            }
        }
        
        if ( 'compiler' != $function[0] ) {
            $result = 'array(';
            foreach ($params as $param) {
                $result .=  $param . ', ';
            }
            $result .= ' )';
            return $function[1] . "($result, \$this)";
        }
        return $function[1]($params, $this);
    }
    
    protected function parseGlobalVariable()
    {
        $access_params   = array(LT_BOOL, LT_VALUE, LT_OTHER);
        $access_function = array('template', 'template_line', 'now', 'microtime', 'version', 'config');
        
        $state = 1; $params = array();
        for (++$this->offset; ; ++$this->offset) {
            if ($state == 1) {
                if (LT_ARRAY == $this->tokens[$this->offset][0]) {
                    $state = 0;
                }else {
                    $this->offset -= 1; 
                    break;
                }
            }else{
                $token = $this->tokens[$this->offset];
                if ( !in_array($token[0], $access_function) ) {
                    return $this->compileFunction();
                }
                if ( !in_array($token[0], $access_params) ) {
                    throw new LapaEngineException('Syntax: ��������� ��������� ���������� $Lapa, ������ %s, #%s', $this->template_line, $token[1]);
                }
                $params[] = $token[1];
                $state = 1;
            }
        }
        
        if (1 > count($params)) {
            throw new LapaEngineException('Syntax: ��������� ��������� ���������� $Lapa, ������ %s, #%s', $this->template_line, $token[1]);
        }
        
        switch ($params[0]) {
            case 'template': 
                $buff = "'$this->template_name'";
            break;
            case 'template_line':
                $buff = "'$this->template_line'";
            break;
            case 'now':
                $buff = 'time()';
            break;
            case 'microtime':
                $buff = 'microtime(1)';
                break;
            case 'version':
                $buff = "'\$this->version'";
                break;
            case 'config':
                $buff = '$this->getConfigVariable(array(\'' . implode('\',\'', array_slice($params, 1)) . '\'))';
            break;
            default:
                throw new LapaEngineException('$Lapa.%s - ����������� �������� "%s", ������ %s, ', $params[0], isset($params[1])?$params[1]:"null", $this->template_line);
        }
        return $buff;
    }
    
    protected function compileVariable($method = true, $modificator = true)
    {        
        $flag_point_array = 0; // Are opened by means of a point
        $flag_not_bracket = 0; // Interdiction on continuation of a list
        
        $buff = $this->tokens[$this->offset][1];
        
        //$this->offset += 1;
        while (true) {
            ++$this->offset;
            $token = $this->tokens[$this->offset];
            
            if ( $flag_point_array == 0 ) {
                if ( $flag_not_bracket == 0 ) {
                    if ( $token[0] == LT_ARRAY ) {
                        $buff .= '['; 
                        $flag_point_array = 1;
                        continue;
                    }else if ( $token[0] == LT_OPEN_ARRAY_BRACKET ) {
                        $tmp_token = $this->tokens[$this->offset += 1];
                        if ( LT_OTHER == $tmp_token[0] ) {
                            $buff .= "[\$_var_section['{$tmp_token[1]}']['index']]";
                        }else if ( LT_VALUE == $tmp_token[0] ) {
                            $buff .= '[' . trim($tmp_token[1], '\'') . ']';
                        }else if ( LT_VARIABLE == $tmp_token[0] ) {
                            $buff .= '[' . $this->compileVariable() . ']';
                        }else {
                            throw new LapaEngineException('Syntax: ������ ����������� ����� � ������ %s',  $this->template_line);
                        }    
                        if ($this->tokens[$this->offset += 1][0] != LT_CLOSE_ARRAY_BRACKET) {
                            throw new LapaEngineException('Syntax: ������ � ����������� ����� � ������ %s',  $this->template_line);
                        }
                        continue;
                    }
                }
                if ($token[0] == LT_OBJECT_GET) {
                    $this->offset += 1;  
                    $token = $this->tokens[$this->offset];
                    $buff .= '->';
                    switch ($token[0]) {
                        case LT_OTHER:
                            if ( '_' == $token[1][0] ) {
                                throw new LapaEngineException('Syntax: ���������� �������� ��������� ��������, ������ %s',  $this->template_line);
                            }
                            $buff  .= $token[1];
                            $flag_not_bracket = 0;
                        break;
                        case LT_FUNCTION:
                            if ( '_' == $token[1][0] ) {
                                throw new LapaEngineException('Syntax: ���������� �������� ��������� ������, ������ %s',  $this->template_line);
                            }
                            $buff .= $token[1] .  $this->parseParams() ;
                            $flag_not_bracket = 1;
                        break;
                        default:
                            throw new LapaEngineException('Syntax: � �������� ���� ������ � ��������, � �� ����� ���������, ������ %s',  $this->template_line);
                    }
                }else {
                    // �� �����
                    break;
                }
            }else {
                switch ($token[0]) {
                    case LT_OTHER:
                        
                        $buff .= "'{$token[1]}'";
                    break;
                    case LT_VALUE:
                        $buff .= $token[1];
                    break;
                    case LT_VARIABLE: 
                        $buff .= $this->compileVariable();
                    break;
                    case LT_VARIABLE_GLOBAL:
                        $buff .= $this->parseGlobalVariable();
                    break;
                    default:
                        throw new LapaEngineException('Syntax: a an error in definition of a key in line %s',  $this->template_line);
                }
                $buff .= ']'; 
                $flag_point_array = 0;
                $flag_not_bracket = 0;
            }  
        }
        if (isset($token) && $token[0] == LT_MODIFICATOR) { // 
            /* If behind a variable the modifier */
            $buff = $this->compileModificator($buff);
        }else {
            /* The index to not move */
            $this->offset -= 1;
        }
        return $buff;
    }
    
    protected function parseStaticObject($method = true)
    {
        $buff = $this->tokens[$this->offset][1] . '::';
        $this->offset += 1; // Method or property
 
        $token = & $this->tokens[$this->offset];
        if ($token[0] == LT_OTHER) {
            $buff .= "\${$token[1]}";
        }else if ($token[0] == LT_FUNCTION) {
                $buff .= $token[1] . $this->parseParams();
        }else throw new LapaEngineException('Syntax: ������� �����, ��� ��������, ����� %s', $this->template_line);
        
        return $buff;
    } 
    
    /**
     * Syntax: array(1=>2, 3, 4)
     * return string
     */
    protected function parseArray() 
    {
        if ($this->tokens[++$this->offset][0] != LT_OPEN_BRACKET) {
            throw new LapaEngineException('Syntax: �������� ��������� ����� ������, ����� %s, token#%s', $this->template_line, $this->offset);
        }    
        
        $buff = 'array(' . $this->parseParams(false, true) . ')';
        
        if ( LT_CLOSE_BRACKET != $this->tokens[++$this->offset][0] ) {
            echo '<pre>'; print_r($this->tokens);
            throw new LapaEngineException('Syntax: �������� ��������� ������ ������, ����� %s, token#%s', $this->template_line, $this->offset);
        }
        return $buff;    
    }
    
    /**
     *
     *
     */
    protected function parseIsOperator($token, $value)
    {
        $not = false;
        if ( $token[2] ) {
            $val2 = $this->_parseExpression();
            if ( empty($val2) ) {
                throw new LapaEngineException('Syntax: ������������ ������������� ��������� "%s" , ����� %s', $token[1], $this->template_line);     
                }
        }
        
        switch ($token[1]) {
            case 'notdivby': case 'divby':
                $result = "( !($value % $val2) )";
                $not = ( 'notdivby' == $token[1] ) ? true : false;
            break;
            case 'noteven': case 'even':
                $result = "( !(1 & $value) )";
                $not = ( 'noteven' == $token[1] ) ? true : false;
            break;
            case 'notevenby': case 'evenby':
                $result = "( !( 1 & ($value / $val2) ) )";
                $not = ( 'notevenby' == $token[1] ) ? true : false;
            break;
            case 'notodd': case 'odd':
                $result = "(1 & $value) ";
                $not = ( 'odd' == $token[1] ) ? true : false;
            break;
            case 'notoddby': case 'oddby':
                $result = "(1 & ($value / $val2) )";
                $not = ( 'notoddby' == $token[1] ) ? true : false;
            break;
            default:
            // � ���� �� �����
        }
        if ( $not ) {
            $result = "( !$result )";
        }
        return $result;
        
    }
    
    protected function isExtFunction($function, $functionPrefix = null, $subPrefix = null, $allowFunction = null, $throw = true)
    {
        
        if ( !is_null($functionPrefix) ) {
            if ( is_array($functionPrefix) ) {
                $plugins = $functionPrefix;
            }else {
                $plugins = array($functionPrefix);
            }
        }else {
            $plugins = array('compiler', 'register', 'modifier');
        }
        
        foreach ($plugins as $plugin) {
            $function_name = 'lapa_' . $plugin . '_' . $function . ( is_null($subPrefix) ? '' : '_' . $subPrefix );
            $function_short_name = $function . ( is_null($subPrefix) ? '' : '_' . $subPrefix );
            
            /* ���� ������� ����������� */
            if ( 'compiler' == $plugin ) {
                if ( function_exists($function_name) ) {
                    return array($plugin, $function_name);
                }
            /* ����������� ��� ������� */
            }else {
                if ( key_exists($function_name, $this->template_header[$functionPrefix]) ) {
                    return array($plugin, $this->template_header[$functionPrefix][$function_short_name]['function_name'] );
                }
            }
            
            if ( $path = $this->getPluginFilePath($function, $plugin) ) {
                if ( 'compiler' == $plugin ) { 
                    $this->compiler_functions[$function_name] = $path;
                    require_once($path);
                    if ( function_exists($function_name) ) {
                        return array($plugin, $function_name);
                    }
                }else {
                    $this->template_header[$functionPrefix][$function_short_name] = array('function_name' => $function_name, 'path' => $path,'ext' => false);
                }
                return array($plugin, $this->template_header[$functionPrefix][$function_short_name]['function_name']);
            } 
        }
        if ( 'compiler' != $plugin ) {
            if ( in_array($function, (array) $allowFunction )) {
                $this->template_header[$functionPrefix][$function_short_name] = array('function_name' => $function_short_name, 'ext' => true);
                return array($plugin, $function_short_name);
            }
        }
        if ( $throw ) {
            throw new LapaEngineException('������� "%s" �� �������, ������ %s', $function_short_name, $this->template_line);     
        }
        return false;
    }
    
    /**
     *
     * ������� ���������� �������������� ���������
     * 
     * ��� �������������� ����� ����� ����� �� ��� �� ������, <br />
     * ���� �� ������ �� �������� ������� ������ ����� � ���������, � ��������� $offset<br />
     * ������� 0, � ������� ������ ���� ������ ������ � ����.
     * � �������� ����� ������ �� ���������, ��� ������� ��� �� ���������.
     *
     * @param int  $offset - ������� ���������
     * @param bool $recursive - ������� ��������, �� ���������� ���� �������� � �������� 
     */
    public function _parseExpression($offset = 1, $recursive = false)
    {    
        $state_operations = 0; //�������� ������������ ������ 
        $flag_minus       = 0; // ������� ����� �����
        $flag_inc         = 0; // ++ -- ������� 
        $last_token       = 0; // ��������� �������
        
        $buff = '';
                
        for ($this->offset += $offset; ; ++$this->offset ):
            $token = $this->tokens[$this->offset];
            /**
             * 0 - ����������, �������, �����, ������
             * 1 - ���������
             * 2 - ��������� ���������
             * 
             * ��� 1 ��������� ������������� ����� �����(-), 
             * ���������� ������ (@), ��������� (not ��� ! )
             */
            if ( 0 == $state_operations):
                
                $flag_inc = 0; // ������� -- ++
                switch ($token[0]) :
                    case LT_VARIABLE:
                        $buff .= $this->compileVariable();  
                    break;
                    case LT_FUNCTION:
                        $funct = $this->isExtFunction($token[1], 'function', null, $this->options['functions']);
                        $buff .= $funct[1]; 
                        ++$this->offset; 
                        $buff .= '(' . $this->parseParams() . ')';
                        if ( LT_CLOSE_BRACKET != $this->tokens[++$this->offset][0] ) {
                            throw new LapaEngineException('Syntax: �������� ��������� ������ ������, ����� %s, token#%s', $this->template_line, $this->offset);
                        }
                    break;
                    case LT_OTHER:
                        if ( LT_ARRAY == $this->tokens[$this->offset + 1][0] ) {
                            $buff .= $this->compileFunction();
                        }else {
                            $buff .= "'{$token[1]}'";
                        }
                    break;
                    case LT_VARIABLE_GLOBAL:
                        $buff .=  $this->parseGlobalVariable();
                    break;
                    case LT_ARRAY_CAST:
                        $buff .=  $this->parseArray();
                    break;
                    case LT_CLASS_STATIC:
                        $buff .=  $this->parseStaticObject();
                    break;
                    case LT_VALUE: case LT_BOOL:
                        $buff .=  $token[1];
                    break;
                    /**
                     * ����� �����
                     */
                    case LT_OPERATOR4:
                        if ($flag_minus) {
                            throw new LapaEngineException('Syntax: ������������ ������������� �������� "-" ����� ����� , ����� %s', $this->template_line);
                        }
                        $state_operations -= 1; 
                        $flag_minus = 1; // ����������
                        $buff .=  '-';
                    break;
                    
                    /**
                     * ������ �������������, ����������� �������� php, 
                     * ������������ @@ ���������, ���� @@@@@@@@@@@@@@@
                     * @$isnotval.value 
                     */
                    case LT_ERROR_AT:
                        $buff .=  '@';
                        $state_operations -= 1; 
                    break;
                    /* ! ��� not */
                    case LT_OPERATOR3:
                        if (!empty($buff) && 0 == $last_token && (LT_OPEN_BRACKET != $this->tokens[$this->offset - 1][0]) ) {
                            throw new LapaEngineException('Syntax: ������������ ������������� ��������� "not", ����� %s, token#%s', $this->template_line, $this->offset);
                        }
                        $buff .=  $token[1]; $state_operations -= 1;
                    break;
                    /**
                     * ������� ������, �������� ���������� _parseExpression 
                     */
                    case LT_OPEN_BRACKET:
                        $buff .= '( '; 
                        $buff .= $this->_parseExpression(1, true);
                        if ( LT_CLOSE_BRACKET != $this->tokens[$this->offset +=1][0] ) {
                            echo '<pre>'; print_r($this->tokens);
                            throw new LapaEngineException('Syntax: ������ �� �������, ����� %s, token#%s', $this->template_line, $this->offset);
                        }
                        $buff .= ' )'; 
                        //$state_operations -= 1;
                    break;
                    
                    /**
                     * ����� �� �������� ��� ���������� �������������� ����������, 
                     * ���������, ����������� �����, ������������
                     */
                    default:
                        if ($last_token == LT_OPERATOR  || $last_token == LT_OPERATOR3 || $last_token == LT_CON_CAT   || $last_token == LT_EQUAL) {
                            throw new LapaEngineException('Syntax: ������ � ���������, ����� %s, token#%s', $this->template_line, $this->offset);
                        }
                        break 2;// ����� �� �����
                endswitch;
                
                $state_operations += 1;
           /**
            * 0 - ����������, �������, �����, ������
            * 1 - ���������
            * 2 - ��������� ���������
            * 
            * ��� 1 ��������� ������������� ����� �����(-), 
            * ���������� ������ (@), ��������� (not ��� ! )
            */    
            elseif ( 1 == $state_operations):
                switch ($token[0]):
                    case LT_OPERATOR: // != <> !== // ==
                        $buff .= ' ' . $token[1] . ' '; 
                    break;
                    /**
                     * �����������, �������� ������ ��������
                     */
                    case LT_CON_CAT:
                        $buff .= ' . ';                       
                    break;
                    case LT_OPERATOR4: // 
                        $buff .=  ' - ';
                    break;
                    /**
                     * ��������� is div, etc.
                     * �������� ���������� _parseExpression 
                     * � �������� $state_operations �� 2, ������� ��������
                     */
                    case LT_OPERATOR5:
                        $buff = $this->parseIsOperator($token, $buff);
                        $state_operations += 1;
                    break;
                    /**
                     * ����������, ��������� ������ ����������
                     * �������� ���������� _parseExpression, 
                     * � �������� $state_operations �� 2, ������� ��������
                     */
                    case LT_EQUAL:
                        if ( LT_VARIABLE !== $last_token ) {
                            throw new LapaEngineException('Syntax: ����������� �������� ����� ������ ����������, ������ %s', $this->template_line); 
                        }
                        // ���� ���������� � �������, ��������� ������ �� �����
                        if ( ! $recursive ) {
                            $this->is_equal = true;
                        } 
                        $buff .= ' = ' . $this->_parseExpression(1, true);
                        $state_operations += 1;
                    break;
                    /* Operators ++ or -- */
                    case LT_OPERATOR2:
                        if ( $flag_inc ) { // ��������� �������� ++ --
                            throw new LapaEngineException('Syntax: ��������� ���������� "++" - "--" ����������, ����� %s, token#%s', $this->template_line, $this->offset);
                        }else if ($last_token != LT_VARIABLE) {
                            throw new LapaEngineException('Syntax:  ���������� �������� ������ ��� ���������� "++" - "--", ����� %s, token#%s', $this->template_line, $this->offset);
                        }
                        $buff .=  $token[1];
                        $flag_inc = 1; $state_operations += 1;
                    break;
                    default:
                        break 2;
                endswitch;
                $state_operations -= 1; $flag_minus = 0;
            /**
             * 0 - ����������, �������, �����, ������
             * 1 - ���������
             * 2 - ��������� ���������
             * 
             * ��������� ��� ��������� ����������� ����������, 
             * ���� ��������
             */ 
            else:
                throw new LapaEngineException('Syntax: ���������, ����� %s, token#%s', $this->template_line, $this->offset); 
    
            endif;
            $last_token = $token[0];
        endfor;
        
        /* ��������� �������, ����� ��������� ������������ ������������� */
        if (isset($token) && $token[0] == LT_MODIFICATOR) { // 
            $buff = $this->compileModificator($buff);
        }else {
            /* �� ������������ ������� ������, ����� ���  */
            $this->offset -= 1;
        }        
        return $buff;
    }
    
    public function parseParams($array = false, $set_array = false)
    {
        $result = ''; $state = 1; 
        
        for(++$this->offset; ;++$this->offset) {
            if ( $state ) { 
                $params = $this->_parseExpression(0);
                if ( '' === $params ) {
                    throw new LapaEngineException('Syntax: �������� ��������, ����� %s, token#%s', $this->template_line, $this->offset); 
                }
                $result[] = $params;
                $state = 0;
            }else {
                $token = $this->tokens[$this->offset];
                $state = 1;
                if ($token[0] == LT_PARAMETR_DELIM) {
                    // ���� ������� � �������, ����������
                    if ( !$array ) { 
                        $result[] = ' , ';
                    }
                    // ���� ������� � ���� ������� ����������, ����������� ������ �����������
                }else if ( !$array && $set_array && ($token[0] == LT_SET_ARRAY) ){
                    $result[] = '=>';
                }else {
                    break;
                }
            }
        }
        
        $this->offset -= 1;
         if ( !$array ) {
            $result = implode('', $result);
         }
         return $result;
    }
}

