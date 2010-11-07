<?PHP
/**
 * Lexis
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
 
require_once('Exception.php'); 

define('LAPA_INLINE_HTML',   1);
define('LT_NEW_LINE',        2); //новая строка
// переменные
define('LT_VARIABLE',        3); // $var
define('LT_CLASS_STATIC',    4); // признак вызова статического метода
define('LT_VARIABLE_GLOBAL', 5); // глобальная переменная lapa

define('LT_BOOL',  6); // true, false (0, 1)
define('LT_VALUE', 7); // число, строка и т.д. 
 
// присвоение / вывод
define('LT_EQUAL',     8);  // = 
define('LT_CON_CAT',   9);  // .. обьединение '' .. '', аналог php . 
define('LT_SET_ARRAY', 10); // => присваивание элемента в массиве
   
// операторы сравнения
define('LT_OPERATOR',  11); // != <> !== // ==  ===  // <= // =<  // =< || * / % ^ && !
define('LT_OPERATOR2', 12); // ++ --
define('LT_OPERATOR3', 13); // !
define('LT_OPERATOR4', 14); // - смена знака, зависит от контекста
define('LT_OPERATOR5', 15); // is div even

define('LT_ARRAY',                16); 
define('LT_ARRAY_CAST',           17); 
define('LT_OBJECT_GET',           18);    
//define('LT_PAAMAYIM_NEKUDOTAYIM', 19); // ::
define('LT_OTHER',                29); // 
define('LT_FUNCTION',             21); //  
define('LT_STR_ARRAY',            22); //  нереализован, простой строковой массив
    
define('LT_OPEN_ARRAY_BRACKET',  23); // [
define('LT_CLOSE_ARRAY_BRACKET', 24); // ]

define('LT_OPEN_BRACKET',   25); // (
define('LT_CLOSE_BRACKET',  26); // (
define('LT_PARAMETR_DELIM', 27); // ,
define('LT_CALL',           28);   
define('LT_EXT_FUNCTION',   29); 
define('LT_GET',            30);    

define('LT_END',          35);
define('LT_END_FUNCTION', 38);

define('LT_INSERT_TEMPLATE',  53);
define('LT_INCLUDE_TEMPLATE', 54);
define('LT_DIRECTIVE',        55);

define('LT_MODIFICATOR',      56); // признак модификатора
define('LT_ARGUMENT',         57); // признак параметра модификатора 

define('LT_STOP_COMPILER',    58); // 
define('LT_END_LAPA_SCRIPT',  59); // служебный, признак окончания шаблона
define('LT_PARSER_DIRECTIVE', 60); // директивы парсера
define('LT_COMMENT',          61); // комментарий 
define('LT_END_COMMENT',      62); // конец комментария
define('LT_ERROR_AT',         63); // подавление ошибок

class LapaEngineLexisSmart
{
    protected $array_pattern_search = array(
            '!', 
            '[A-Za-z_]+[0-9A-Za-z_]*',     // любое допустимое слово (функции, переменные)
            '|[\s]',                // пробел 
            '|[\$]+',               // начало переменной
            '|\n',                  // перевод строки
            '|[\d]+',
            '|\.\=',                // .=
            '|\.\.|\.\=|\.',        // .. .= .
            '|\=\>',                // => 
            '|[\=\>]+',             // === == =
            '|[\|]+',               // || |
            '|\(',                  // (
            '|\#',                   // # признак конфигурационной переменной
            '|\)',                  // )
            '|[\*\/]+',             // * */ /
            '|[\:]+',               // : ::
            '|\[|\]',               // [ и ]
            '|[\%\^\,\?]+',         // % ^ , ?
            '|\+\=',                // +=
            '|[\+]+',               // ++ +
            '|[\-\>\=]+',           // -> -= -- -
            '|[\&]+',               // && &
            '|[\!\=]+',             // !== != !
            '|\<\>',                // <>
            '|[\<\=]+',             // << <= <
            '|[\>\=]+',             // >= >> >
            '|\\\\',
            '|"',                   // "
            "|'",                   // '
            '|\@',
            '|.',                   // кракозябы
            '!'
    );

    
       
    protected $known_tags = array(
        'lapa'            => array(LT_VARIABLE_GLOBAL),
        'call'            => array(LT_CALL), 
        '='               => array(LT_GET),
        'set'             => array(LT_CALL), 
        'get'             => array(LT_GET), 
        'directive'       => array(LT_DIRECTIVE),
        'insert_template' => array(LT_INSERT_TEMPLATE),  
        'include'         => array(LT_INCLUDE_TEMPLATE), 
        'comment'         => array(LT_COMMENT), 
    );
    
    protected $known_tags_operator = array(
        'lapa'            => array(LT_VARIABLE_GLOBAL),
        'and'             => array(LT_OPERATOR,  '&&'), 
        'or'              => array(LT_OPERATOR,  '||'),
        'xor'             => array(LT_OPERATOR,  'xor'),
        'eq'              => array(LT_OPERATOR,  '=='),
        'ne'              => array(LT_OPERATOR,  '!='),
        'neq'             => array(LT_OPERATOR,  '!='),
        'gt'              => array(LT_OPERATOR,  '>'),
        'lt'              => array(LT_OPERATOR,  '<'),
        'ge'              => array(LT_OPERATOR,  '>='),
        'gte'             => array(LT_OPERATOR,  '>='),
        'le'              => array(LT_OPERATOR,  '<='),
        'lte'             => array(LT_OPERATOR,  '<='),
        'not'             => array(LT_OPERATOR3, '!'),
        'array'           => array(LT_ARRAY_CAST     ),
        'on'              => array(LT_BOOL,     'true'),
        'true'            => array(LT_BOOL,     'true'),
        'yes'             => array(LT_BOOL,     'true'),
        'off'             => array(LT_BOOL,     'false'),
        'false'           => array(LT_BOOL,     'false'),
        'no'              => array(LT_BOOL,     'false'),
        '['               => array(LT_OPEN_ARRAY_BRACKET,  '['),
        ']'               => array(LT_CLOSE_ARRAY_BRACKET, ']'),
        '%'               => array(LT_OPERATOR,            '%'),
        '^'               => array(LT_OPERATOR,            '^'),
        ','               => array(LT_PARAMETR_DELIM,      ','),
        '?'               => array(LT_CALL                    ),   
      //'('               => array(LT_OPEN_BRACKET,        '('),
        ')'               => array(LT_CLOSE_BRACKET,       ')'), 
        '+'               => array(LT_OPERATOR,    '+'),
        '++'              => array(LT_OPERATOR2,   '++'),
        '+='              => array(LT_OPERATOR,    '+='), 
        '-'               => array(LT_OPERATOR4,   '-'),
        '--'              => array(LT_OPERATOR2,   '--'),
        '-='              => array(LT_OPERATOR,    '-='), 
        '->'              => array(LT_OBJECT_GET,  '->'),
        '&'               => array(LT_OPERATOR,    '&'),
        '&&'              => array(LT_OPERATOR,    '&&'),
        '='               => array(LT_EQUAL,       '='),
        '=>'              => array(LT_SET_ARRAY,   '=>'),
        '=='              => array(LT_OPERATOR,    '=='),
        '==='             => array(LT_OPERATOR,    '==='),
        '!'               => array(LT_OPERATOR3,   '!'),
        '!='              => array(LT_OPERATOR,    '!='),
        '!=='             => array(LT_OPERATOR,    '!=='),
        '<'               => array(LT_OPERATOR,    '<'),
        '<='              => array(LT_OPERATOR,    '<='),
        '<>'              => array(LT_OPERATOR,    '!='),
        '<<'              => array(LT_OPERATOR,    '<<'),
        '>'               => array(LT_OPERATOR,    '>'),
        '>='              => array(LT_OPERATOR,    '>='),
        '>>'              => array(LT_OPERATOR,    '>>'),
        '|'               => array(LT_MODIFICATOR, '|'),
        '||'              => array(LT_OPERATOR,    '||'),
        '.'               => array(LT_ARRAY,       '.'),
        '.='              => array(LT_OPERATOR,    '.='),
        ':'               => array(LT_ARGUMENT,    ':'),
        '@'               => array(LT_ERROR_AT,    '@')
    );
    
    protected $known_end_tags = array(
        'comment'        => array(LT_END_COMMENT),
        );
    
    protected $known_tags2 = array(
        '*'  => array(LT_OPERATOR,     '*'),
        '/'  => array(LT_OPERATOR,     '/'),
        '('  => array(LT_OPEN_BRACKET, '('),
        '..' => array(LT_CON_CAT,      '.'),
        
    );
    
    protected $known_is_operator = array(
        'notdivby'   => array(LT_OPERATOR5, 'notdivby', true),
        'divby'      => array(LT_OPERATOR5, 'divby', true),
        'noteven'    => array(LT_OPERATOR5, 'noteven', false),
        'even'       => array(LT_OPERATOR5, 'even', false),
        'notevenby'  => array(LT_OPERATOR5, 'notevenby', true),
        'evenby'     => array(LT_OPERATOR5, 'evenby', true),
        'notodd'     => array(LT_OPERATOR5, 'notodd', false),
        'odd'        => array(LT_OPERATOR5, 'odd', false),
        'notoddby'   => array(LT_OPERATOR5, 'notoddby', true),
        'oddby'      => array(LT_OPERATOR5, 'oddby', true),
    );
    
    protected $state_tpl = false;
    
    protected $pattern_search; 
    
    /**
     * Пропущенные блоки
     *
     * @var array
     */
    protected $literal_text = array();
    /**
     * Пропущенные блоки с php кодом 
     *
     * @var array
     */
    protected $php_text     = array();
    
    /**
     * Текущие настройки
     *
     * @var array
     */
    protected $options      = array();
    /**
     * текущая строка шаблона
     *
     * @var string
     */
    protected $template_line;
    
    /**
     * Имя шаблона, используется для формирования исключений
     *
     * @var string
     */
    protected $template_name;
    
    /**
     * В случае ошибки, содержит объект исключения
     *
     * Узнать об ошибке можно через метод isError<br />, получить 
     * объект можно вызвав метод lastError 
     * @var object
     */
    protected $last_error;
    /**
     * Копируем настройки
     *
     * @param array $options
     * @return void
     */
    public function __setOptions(& $options)
    {
        $this->options = & $options;
    }
    
    public function __construct()
    {
        $this->pattern_search = implode('', $this->array_pattern_search);
    }
    
    public function isError()
    {
        if (is_object($this->last_error)) {
            return true;
        }
        return false;
    }
    
    public function lastError()
    {
        if (is_object($this->last_error)) {
            return $this->last_error;
        }
        return false;
    }
    /**
    *
    * разбор шаблона на лексемы
    *
    * @param string $source - строка содержит шаблон
    * @param string $templateName - имя шаблона
    */
    public function & lex($template_string, $template_name = 'Unknown') {
                
        $this->template_name   = $template_name;
        
        $tokens[0] = $template_name;
        $tokens[1] = '';
        $lex_start = microtime(1); // служебный
        
        try {
            if ( strlen($template_string) ) {
                $tokens = & $this->splitTemplate($template_string, $tokens);
            }
        }catch (LapaEngineException $err) {
               $err->setTemplate($this->template_name, $this->template_line);
               $this->last_error = $err;
               // Only variable references should be returned by reference
               $r = false;
               return $r;
        }
        
        $tokens[1]['lex_time'] = microtime(1) - $lex_start;
        $tokens[1]['lex_line'] = $this->template_line;
        //print_r($tokens);
        return $tokens;
    }

    protected function prepareTemplate(& $template_string, & $arr_tpl) {
        
        $ldq = preg_quote($this->options['left_delimiter']);
        $rdq = preg_quote($this->options['right_delimiter']);
        
        $search['literal']  = "!{$ldq}\s*literal\s*{$rdq}(.*?){$ldq}\s*/literal\s*{$rdq}!si";
        $search['php']      = "!{$ldq}\s*php\s*{$rdq}(.*?){$ldq}\s*/php\s*{$rdq}!si";
        $search['tags']     = "!{$ldq}(.*?){$rdq}!s";
        
        $replace['literal'] = "!{$ldq}\s*literal\s*{$rdq}(.*?){$ldq}\s*/literal\s*{$rdq}!si";
        $replace['php']     = "!{$ldq}\s*php\s*{$rdq}(.*?){$ldq}\s*/php\s*{$rdq}!si";
        $replace['parse_text']     = '/(?<!\\\\|\\\\\$)([\$]{1,2}[a-zA-Z0-9_]+(\.|\w|\-\>)*)(?:[;]{1})/';
        //$replace['html_tags'] = '!(?:\<\s*)([\/]{0,1})(?:\s*tpl\s*:\s*)([^\/\>]*)(?:\/*\>)!si';
        //$replace['html_tags'] = '!(?:\<\s*)([\/]{0,1})(?:\s*tpl\s*\:\s*)([^\/\>]*)(?:\/*\>)!si';
        //$replace['html_tags'] = '!(?:\<\s*)([\/]{0,1})(?:\s*tpl\s*[\:]*\s*)(.*?)(?:\/*\s*\>)!si';
        $match        = array();
        
        // есть команды для парсера
        if ($template_string[0] == '!') {
            // в работе
        }
        
        //удалим все каретки и табы 
        $template_string = str_replace(array("\r", "\t"), '', $template_string);
        
        //$template_string = ' ' . $template_string;
        /* удалим блоки literal */
        $res = preg_match_all($search['literal'], $template_string, $match);
        if ($res) {
            $arr_tpl['literal'] = $match[1];
            $template_string = preg_replace($replace['literal'], $this->options['left_delimiter'] . "literal" . $this->options['right_delimiter'], $template_string);
        }
        
        /* удалим блоки php */
        $res = preg_match_all($search['php'], $template_string, $match);
        if ($res) {
            $arr_tpl['php'] = $match[1];
            $template_string = preg_replace($replace['php'], $this->options['left_delimiter'] . "php" . $this->options['right_delimiter'], $template_string);
        }
        if ( $this->options['parse_text'] ) {
            $template_string = preg_replace($replace['parse_text'], $this->options['left_delimiter'] . '$1' . $this->options['right_delimiter'], $template_string);
        }
        /* массив текста между тегами */
        $arr_tpl['text'] = preg_split("!{$ldq}.*?{$rdq}!s", $template_string);
                
        /* выделяем теги шаблонов */
        $res = preg_match_all($search['tags'], $template_string, $match);
        if ($res) {
          $arr_tpl['tags'] = $match[1];
        }
        //$this->debug_print($template_string);
        return true;
    }
    
    protected function & splitTemplate(& $template_string, $tokens)
    {
        $arr_tpl = array('literal'=>array(), 'php'=>array(), 'tags'=>array(), 'text'=>array()); 
        $this->prepareTemplate($template_string, $arr_tpl);
        
        unset($template_string);
        
        $text       = & $arr_tpl['text'];
        $tags       = & $arr_tpl['tags'];
        
        $count_tags = count($tags);
        
        if (count($arr_tpl['literal']) > 0) {
            $this->literal_text = $arr_tpl['literal'];
        }
        if (count($arr_tpl['php']) > 0) {
            $this->php_text     = $arr_tpl['php'];
        }
        unset($arr_tpl);
        
        $this->template_line = 1;
        
        for ($i = 0; $count_tags > $i; ++$i) {
            $tmp_text = & $text[$i];
            $this->template_line += substr_count($tmp_text, "\n");
            $tokens[] = array(LAPA_INLINE_HTML, $tmp_text, $this->template_line);
                        
            $tokens[]  = array(LT_NEW_LINE, $this->template_line);
            $this->splitBlock($tags[$i], $tokens);
        }
        
        $this->template_line += substr_count($text[$i], "\n");
        if ($text[$i] != '') {
            $tokens[] = array(LAPA_INLINE_HTML, $text[$i], $this->template_line);
        }
        // почистим 
        $this->literal_text = array();
        $this->php_text     = array();
        
        return $tokens;
    }
    
    
    protected function splitBlock($tpl, & $tokens)
    {
        $new_line = true; $match = null; 
        
        $res_count = preg_match_all($this->pattern_search, $tpl, $match);
        //echo '<pre>';
        //print_r($this->pattern_search);
        if ( false === $res_count ) {
            throw new LapaEngineException('Синтаксическая ошибка в строке %s', $this->template_line);
        }else if ( 0 == $res_count ) {
            return ;
        }
        
        if (!isset($match[0])) {
            throw new LapaEngineException('Синтаксическая ошибка в строке %s', $this->template_line);
        }else {
            $tags = & $match[0];
        }
        
        $count_tags = count($tags);
        for ( $index = 0; $count_tags > $index; ++$index ) {
            
            if (' ' == $tags[$index]) {
                continue;
            }
            
            $tag_index = strtolower($tags[$index]);
            
            if ( "\n" == $tag_index){
                $tokens[] = array(LT_NEW_LINE, $this->template_line += 1);
                $new_line = true; 
                continue;           
            }
            
            if ($new_line ) {
                if ( key_exists($tag_index, $this->known_tags) ) {
                    $tokens[] = & $this->known_tags[$tag_index];
                    $new_line = false;
                    continue;
                }
            }
            
            $new_line = false;
            if ( key_exists($tag_index, $this->known_tags_operator) ) {
               //var_dump($tag_index);
               $tokens[] = & $this->known_tags_operator[$tag_index];
                continue;
            }
            
            switch ($tag_index) {
                case '$':
                    if ( $count_tags == ($index += 1) ) {
                        throw new LapaEngineException('Ошибка определения переменной в строке %s', $this->template_line);
                    }    
                    $var_name = & $tags[$index];
                                  
                    if ( !preg_match('!^[A-Za-z_]+[0-9]*$!', $var_name) ) {
                        throw new LapaEngineException('Ошибка определения переменной в строке %s', $this->template_line);
                    }
                    if ( strtolower($var_name) == 'lapa' ) {
                        $tokens[] = & $this->known_tags['lapa'];
                    }else {
                        $tokens[] = array(LT_VARIABLE, "\$_var['$var_name']");
                    }
                    unset($var_name);
                break;
                case '$$':
                    if ( $count_tags == ($index += 1) ) {
                        throw new LapaEngineException('Ошибка определения переменной в строке %s', $this->template_line);
                    }  
                    $var_name = & $tags[$index];
                                  
                    if ( !preg_match('!^[A-Za-z_]+[0-9]*$!', $var_name) ) {
                        throw new LapaEngineException('Ошибка определения переменной в строке %s', $this->template_line);
                    }
                    $tokens[] = array(LT_VARIABLE, "\$_var_local['$var_name']");
                    unset($var_name);
                break;
                case '//':
                    for ( ++$index; $count_tags > $index; ++$index ) {
                        if ( "\n" == $tags[$index] ) {
                            $tokens[] = array(LT_NEW_LINE, $this->template_line +=1);
                            $new_line = true;
                            break;
                        }
                    }
                break;
                case '/*':
                    for ( ++$index; $count_tags > $index; ++$index ) {
                        if ( "*/" == $tags[$index] ) {
                            break;
                        }else if ("\n" == $tags[$index] ) {
                            $tokens[] = array(LT_NEW_LINE, $this->template_line +=1);
                        }
                    }
                break;
                case '*': // комментарий, если начинается с новой строки
                    $token = end($tokens);
                    if ( 0 == $index || LT_NEW_LINE == $token[0] ) {
                        for ( ++$index; $count_tags > $index; ++$index ) {
                            if ( '*' == $tags[$index] ) {
                                break;
                            }else if ( "\n" == $tags[$index] ) {
                                $tokens[] = array(LT_NEW_LINE, $this->template_line +=1);
                                $new_line = true;
                                break;
                            }
                        }
                    }else {
                        $tokens[] = & $this->known_tags2['*'];
                    }
                break;
                case '..': // пропустим все пробелы и возможно все переводы строки
                    for ( ++$index; $count_tags > $index; ++$index ) {
                        if ( ' ' == $tags[$index] ) {
                            continue;
                        }else if ( "\n" == $tags[$index] ) {
                            $tokens[] = array(LT_NEW_LINE, $this->template_line +=1);
                        }else {
                            --$index;
                            break;
                        }        
                    }
                    $tokens[] = & $this->known_tags2['..'];
                break;
                case '#':
                    if ( $count_tags == ($index += 1) ) {
                        throw new LapaEngineException('Ошибка определения конфигурационной переменной в строке %s', $this->template_line);
                    }
                    $var_name = $tags[$index];
                    
                    if ( !preg_match('!^[A-Za-z_]+[0-9]*$!', $var_name) ) {
                        throw new LapaEngineException('Ошибка определения конфигурационной переменной в строке %s', $this->template_line);
                    }
                    $tokens[] = & $this->known_tags['lapa'];
                    $tokens[] = & $this->known_tags['.'];
                    $tokens[] = array(LT_OTHER, 'config');
                    $tokens[] = & $this->known_tags['.'];
                    $tokens[] = array(LT_OTHER, $var_name);
                    // для совместимости
                    if ( $count_tags > ($index += 1) ) {
                        if ($tags[$index] != '#') {
                            $index -= 1;
                        }
                    }
                    unset($var_name);
                break;
                case '/':
                    $token = end($tokens);
                    // если первый, или после \n считаем закрывающим блоком
                    if ( 0 == $index || LT_NEW_LINE == $token[0] ) {
                        if ( $count_tags == ($index += 1) ) {
                            throw new LapaEngineException('Ошибка закрытия условного блока в строке %s', $this->template_line);
                        }
                        $tag = & $tags[$index];
                        $tag_index = strtolower($tag);
                        if ( !preg_match('!^[A-Za-z_]+[0-9]*$!', $tag) ) {
                            throw new LapaEngineException('Ошибка определения блока в строке %s', $this->template_line);
                        }
                        if (key_exists($tag_index, $this->known_end_tags) ) {
                            // известный тег
                            $tokens[] = & $this->known_end_tags[$tag_index];
                        }else {
                            $tokens[] = array(LT_END);
                            $tokens[] = array(LT_END_FUNCTION, $tag);
                        }
                        unset($tag);
                    }else {
                        $tokens[] = & $this->known_tags2['/'];
                    }
                break;
                case '(':
                    $token = end($tokens);
                    // работает только для функций
                    if ( LT_OTHER == $token[0] ) {
                        array_pop($tokens);
                        $tokens[] = array(LT_FUNCTION, $token[1]);
                    }else {
                        
                    }
                    $tokens[] = & $this->known_tags2['('];
                break;
                case '\'': 
                    $try_s = 0; $buff = '\''; $res = false;
                    for ( ++$index; $count_tags > $index; ++$index ) {
                        
                        if ('\'' == $tags[$index]) {
                            if ( !$try_s ) {
                                $res = true;
                                break;
                            }
                        }else if ("\n" == $tags[$index]) {
                            $this->template_line += 1;
                        }else if ("\\" ==  $tags[$index] ) {
                            $try_s = (1 == $try_s) ? 0: ++$try_s;
                            $buff .= $tags[$index];
                            continue;
                        }
                        $try_s = 0;
                        $buff .= $tags[$index];
                    }
                    
                    if ( !$res ) { 
                        throw new LapaEngineException('Незакрытые кавычки в строке %s', $this->template_line);
                    }
                    $tokens[] = array(LT_VALUE, $buff . '\'');
                    
                    unset($buff);
                break;
                
                /**
                 * собираем строку, в будущем реализуем возможность распознавать управляющие конструкции 
                 */
                case '"': 
                    $try_s = 0; $buff = '';$res = false;
                    for ( ++$index; $count_tags > $index; ++$index ) {
                        if ('"' == $tags[$index]) {
                            if ( 0 == $try_s ) {
                                $res = true;
                                break;
                            }else {
                                $buff = substr($buff, 0, strlen($buff) - 1);
                            }
                        }else if ("\n" == $tags[$index]) {
                            $this->template_line += 1;
                        }else if ('\\' ==  $tags[$index] ) {
                            $try_s = (1 == $try_s) ? 0 : 1;
                            $buff .= $tags[$index];
                            continue;
                        }else if ('\'' ==  $tags[$index]) {
                            $buff .= '\\';
                        }
                        $try_s = 0;
                        $buff .= $tags[$index];
                    }
                    if ( !$res ) { 
                        throw new LapaEngineException('Незакрытые кавычки в строке %s', $this->template_line);
                    }
                    $buff = '\'' . $buff . '\'';
                    $tokens[] = array(LT_VALUE, $buff);
                    unset($buff);
                break;
                /**
                 * MyClass::Title
                 */
                case '::':
                    $token = & $tokens[count($tokens) - 1];
                    if ( LT_OTHER == $token[0] ) {
                        $token[0] = LT_CLASS_STATIC;
                    }else {
                        throw new LapaEngineException('Недопустимый символ :: в строке %s', $this->template_line);
                    }
                    unset($token);
                break;
                /**
                 * is [not, !] div by, is [not, !] even [by], is [not, !] odd [by]
                 */
                case 'is':
                    $is_operator = '';
                    for ( ++$index; $count_tags > $index; ++$index ) {
                        
                        if (' ' == $tags[$index]) {
                            continue;
                        }
                        $tag_index = strtolower($tags[$index]);
                        switch ($tag_index) {
                            case '!':
                                $is_operator .= 'not';
                            break;
                            case 'not': case 'div': case 'by': case 'even': case 'odd': 
                                $is_operator .= $tag_index;
                            break;
                            default:
                                --$index;
                            break 2;
                        }
                    }
                    
                    if ( key_exists($is_operator, $this->known_is_operator) ) {
                        $tokens[] = & $this->known_is_operator[$is_operator];
                    }else {
                        throw new LapaEngineException('Синтаксическая ошибка оператора "IS" в строке %s', $this->template_line);
                    }
                    unset($is_operator); 
                break;
                case 'literal':
                    $literal_text = array_shift($this->literal_text);
                    $this->template_line += substr_count($literal_text, "\n");
                    $tokens[] = array(LAPA_INLINE_HTML, $literal_text, $this->template_line);
                    $tokens[] = array(LT_NEW_LINE, $this->template_line);
                    $new_line = true;
                    unset($literal_text);
                break;
                case 'php':
                    $php_text = array_shift($this->php_text);
                    $this->template_line += substr_count($php_text, "\n");
                    $tokens[] = array(LAPA_INLINE_HTML, '<?php ' . $php_text .'?>', $this->template_line);
                    $tokens[] = array(LT_NEW_LINE, $this->template_line);
                    $new_line = true;
                    unset($php_text);
                break;
                case 'ldelim':
                    $tokens[] = array(LT_VALUE, '\'' . $this->options['left_delimiter'] . '\'');
                break;
                case 'rdelim':
                    $tokens[] = array(LT_VALUE, '\'' . $this->options['right_delimiter'] . '\'');
                break;
                /**
                 * [not, !] div by, [not, !] even [by], [not, !] odd [by]
                 */
                case 'div': case 'even': case 'odd':
                    
                    $is_operator = ''; 
                    /* 
                     * Проверка последнего значения, если not, удалим
                     * {$i not even by 2}
                     */
                    $token = end($tokens);
                    if ( LT_OPERATOR3 == $token[0] && '!' == $token[1] ) {
                        array_pop($tokens); // удалим последний элемент
                        $is_operator = 'not';
                    }
                    $is_operator .= $tag_index;
                    for ( ++$index; $count_tags > $index; ++$index ) {
                        $tag_index_tmp = strtolower($tags[$index]);
                        if (' ' == $tag_index_tmp) {
                            continue;
                        }else if ('by' == $tag_index_tmp ) {
                            $is_operator .= $tag_index_tmp;
                            break;
                        }else{
                            --$index;
                            break;
                        }
                    }
                    if ( key_exists($is_operator, $this->known_is_operator) ) {
                        $tokens[] = & $this->known_is_operator[$is_operator];
                    }else {
                        throw new LapaEngineException('Синтаксическая ошибка оператора "%s" в строке %s', $tag_index, $this->template_line);
                    }
                    unset($is_operator); unset($tag_index_tmp);
                break;
                default:
                    $other_tag = & $tags[$index];
                    
                    if ( preg_match('!^[A-Za-z_]+[0-9A-Za-z_]*$!', $other_tag) ) {
                        $tokens[] = array(LT_OTHER, $other_tag);
                    }else if ( preg_match('!^[\d]+$!', $other_tag) ) {
                        $tokens[] = array(LT_VALUE, $other_tag);
                    }else {
                        throw new LapaEngineException('Недопустимая конструкция "%s" в строке %s', $other_tag, $this->template_line);
                    }
                    unset($other_tag);
                    break;
            }
        }
    }
}