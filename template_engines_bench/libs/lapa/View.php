<?php
/**
 * View
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
 * @subpackage LapaView
 * @author     Stepanov Sergey <StepanovSergey@tut.by>
 * @copyright  2007 Stepanov Sergey
 * @license    http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @version    v 0.5.7.3 2007/10/27
 */
 
 
ini_set('include_path', dirname(__FILE__) . ';' .ini_get('include_path'));

require_once('Engine' . DIRECTORY_SEPARATOR . 'Base.php');

class LapaView extends LapaEngineBase
{
    /**
     * $view = LapaView::Factory([$options])
     */
    protected static $view_obj;
    
    /**
     * Присвоенные переменные
     */
    protected $template_vars = array();
    
    /**
     * Конфигурационные переменные
     *
     * @var array
     */
    protected $config_vars   = array();
    
    /**
     * Хранит объект кеша
     *
     * @var object
     */
    protected $cache_object;
    
    /**
     * Содержит временный индефикатор ресурса
     *
     * @var mixed $template_compile_id
     */
    protected $template_compile_id;
    
    /**
     * Содержит временный индефикатор кеша
     *
     * @var mixed $template_cache_id
     */
    protected $template_cache_id;
    
    protected $template_count = 0;
    
    /**
     * Содержит уровень ошибок вызывающего приложения
     *
     * @var int $app_error_reporting
     */
    protected $app_error_reporting = 0;
    
    /**
     * Возращает класс LapaView 
     *
     *
     * @param array $options
     * @return object
     */
    public static function Factory($options = null)
    {
        if (!is_object(self::$view_obj)){
            self::$view_obj = new LapaView($options);
        }
        return self::$view_obj;
    }
    
    /**
     * Контруктор
     * 
     * @param array $options
     * @return void 
     */
    public function __construct($options = null)
    {
        /* утановки по умолчанию */
        
        /* проверять наличие изменений в базовом шаблоне */
        $this->options['compile_check']         = false;
        /* компилировать шаблоны каждый раз заново, 
           отменяет кеширование */
        $this->options['force_compile']         = false;
        /* кеширование 1 - включено, 2 - ручное кеширование, 3 кеширование только из шаблонов */
        $this->options['caching']               = false;
        /* время жизни кеша, в секундах */
        $this->options['cache_lifetime']        = 3600;
        /* уровень создания субдиректорий , по умолчанию 0,
           при указании  use_sub_dirs автоматически изменяет значение в true */
        $this->options['directory_level']       = 0;
        /* права доступа на созданные директории */
        $this->options['directory_umask']       = 0700;
        /* блокировать файлы при записи, чтении. 
           при указании false, выполняетя подмена файлов 
           с помощью unlink, rename */
        $this->options['file_locking']          = true;
        /* разрешить ипользование субдиректорий */
        $this->options['use_sub_dirs']          = false;
        /* индификатор ресурса по умолчанию */
        $this->options['compile_id']            = array('com');
        /* индификатор кеша по умолчанию */
        $this->options['cache_id']              = array('cache');
        /* путь до директории содержащей кеш, по умолчанию null, если
           не указана при первом вызове, установится на cache */
        $this->options['cache_dir']             = null;
        /* путь до директорий содержащей шаблоны, по умолчанию null, если
           не указана при первом вызове, установится на templates.
           можно применять несколько директорий, искать будет 
           по очереди во всех */
        $this->options['template_dir']          = array();
        /* путь до директории содержащей скомпелированные шаблоны, по умолчанию null, если
           не указана при первом вызове, установится на templates_c */
        $this->options['compile_dir']           = null;
        /* путь до директории содержащей конфигурационные файлы, по умолчанию null, если
           не указана при первом вызове, установится на configs */
        $this->options['config_dir']            = array();
        /* уровень ошибок при работе шаблонизатора */
        $this->options['error_reporting']       = E_ERROR | E_WARNING | E_PARSE | E_USER_WARNING | E_USER_NOTICE | E_USER_NOTICE;
        /* перехватывать исключения в случае ошибки */
        $this->options['processing_exception']  = 1;
        /* функция или объект которому передать исключение*/
        $this->options['exceptions_function']   = null;
        /* драйвер кеша, в данный момент только file */
        $this->options['cache_driver']          = 'file';
        /* тип реурса, в данный момент только file */
        $this->options['default_resource_type'] = 'file';
        
        /* Для использования тем или скинов *по умолчанию (базовый)* */
        $this->options['default_theme'] = null;
        /* Для использования тем или скинов */
        $this->options['theme'] = null;
        
        /* не выводить информацию отладчика используя методы display и fetch */
        $this->options['debug_output']           = true;
        /* свойства дублируется */
        $this->options['cache_setting']['directory_level']  = 0;
        $this->options['cache_setting']['directory_umask']  = 0700;
        $this->options['cache_setting']['file_locking']     = true;
        $this->options['cache_setting']['use_sub_dirs']     = false;
        $this->options['cache_setting']['driver']           = 'file';
        
        if (!is_null($options)) {
            foreach ($options as $k => $v) {
                $this->__set($k, $v);  
            }
        }
    }
    
    /**
     * Приcваивает свойства объекта
     *
     * @param string $var_name
     * @param mixed  $val
     * @return void 
     */
    public function __set($var_name, $val) {
        switch ($var_name) {
            /**
             * Установка темы по умолчанию,
             * и текущей темы 
             * 
             * @var string default_theme
             * @var string theme
             */
            case 'default_theme': case 'theme':
                $var_name = trim( (string) $var_name, '\/');
                $this->options[$var_name] = $var_name . DIRECTORY_SEPARATOR;
            break; 
            
            /**
             * Если true, постоянная компиляция, 
             * неявно отменяет кеширование
             * 
             * @var bool force_compile
             */           
            case 'force_compile':
                if ( (bool)$val ) {
                    $this->options['compile_check'] = false;
                }
                $this->options[$var_name] = (bool)$val;
            break;
            
            case 'compile_check':
                if ( !$this->options['force_compile'] ) {
                    $this->options[$var_name] = (bool)$val;
                }
            break; 
            case 'caching':
                $this->options[$var_name] = (int)$val;
                if (is_object($this->cache_object)) {
                    $this->cache_object->caching = (int)$val;
                }
            break;
            case 'cache_lifetime': 
                if ((int) $val == -1) {
                    $this->options[$var_name] = 2592000;
                }else {
                    $this->options[$var_name] = (int) $val;
                }
            break;
            case 'processing_exception':
                $this->options[$var_name] = (int) $val;
            break;
            case 'compile_id':  case 'cache_id': case 'default_resource_type':
            case 'error_reporting':
                $this->options[$var_name] = $val;
            break; 
            case 'plugins_dir': case 'template_dir': case 'config_dir':
                if (is_array($val)) {
                    foreach ($val as $path) {
                        $val = rtrim($val, '\/ ');
                        if ( file_exists($val) ){ 
                            if (!in_array($val . DIRECTORY_SEPARATOR, $this->options[$var_name])) {
                                $this->options[$var_name][] = $val . DIRECTORY_SEPARATOR;
                            }
                        }else {
                            require_once('ViewException.php');
                            LapaViewException('Указаная папка "%s" для "%s" не найдена', $val, $var_name);
                        }
                    }
                }else if(is_string($val)){
                        $val = rtrim($val, '\/ ');
                        if ( file_exists($val) ){ 
                            if (!in_array($val . DIRECTORY_SEPARATOR, $this->options[$var_name])) {
                                $this->options[$var_name][] = $val . DIRECTORY_SEPARATOR;
                            }
                        }else {
                            require_once('ViewException.php');
                            LapaViewException('Указаная папка "%s" для "%s" не найдена', $val, $var_name);
                        }
                }else {
                    $this->warning_error('Неправильный формат данных для свойства "%s", в %s, строка %s, метод %s', $var_name, __FILE__, __LINE__, __METHOD__);
                }
            break;
            
            case 'compile_dir': 
                if(is_string($val)){
                    $val = rtrim($val, '\/ ');
                    if ( file_exists($val) ) {
                        $this->options[$var_name] = $val. DIRECTORY_SEPARATOR;
                    }else {
                        require_once('ViewException.php');
                        throw new LapaViewException('Указаная папка "%s" для скомпелированных файлов не найдена', $val);
                    }
                }else {
                    $this->warning_error('Неправильный формат данных для свойства "%s", в %s, строка %s, метод %s', $var_name, __FILE__, __LINE__, __METHOD__);
                }
            break;
            case 'directory_umask':
                $this->options[$var_name] = (string) $val[0] == '0' ? $val: 0700;
                $this->options['cache_setting'][$var_name] = $this->options[$var_name];
            break;
            case 'file_locking':
                $this->options[$var_name] = (bool) $val;
                $this->options['cache_setting'][$var_name] = (bool) $val;
            break;
            case 'directory_level':
                $this->options['use_sub_dirs'] = $val > 0 ? true : false;
                $this->options['cache_setting']['use_sub_dirs'] = $this->options['use_sub_dirs'];
                $this->options[$var_name] = (int) $val;
                $this->options['cache_setting'][$var_name] = (int) $val;
            break;
            case 'use_sub_dirs':
                $this->options['directory_level'] = (bool)$val ? 3 : 0;
                $this->options['cache_setting']['directory_level'] = $this->options['directory_level'];
                $this->options[$var_name] = (bool) $val;
                $this->options['cache_setting'][$var_name] = (bool) $val;
                break;
            case 'cache_dir':
                $val = rtrim($val, '\/ ');
                if ( file_exists($val) ) {
                    $this->options['cache_setting'][$var_name] = $val . DIRECTORY_SEPARATOR;
                    $this->options[$var_name] = $val . DIRECTORY_SEPARATOR;
                }else{
                    require_once('ViewException.php');
                    throw new LapaViewException('Указаная папка для кеширования "%s" не надена', $val);
                }
            break;
            case 'cache_driver':  
                $this->options['cache_setting']['driver'] = $val;
            break;
            case 'exceptions_function':
                $this->options[$var_name] = $val;
            break;
            case 'debug_output':
                $this->options[$var_name] = (bool) $val;
            break;
            default:
                parent::__set($var_name, $val);
        }        
    }
    /** 
     * Проверяет наличие кешированных страниц
     * 
     * альтернатива isCached
     *
     * @papam string $resource
     * @param mixed  $cacheId
     * @param mixed  $compileId
     * 
     * @return bool
     */
    public function is_cached($resource, $cacheId = null, $compileId = null)
    {
        return $this->isCached($resource, $cacheId, $compileId);
    }
    /** 
     * Проверяет наличие кешированных страниц
     * 
     * @papam string $resource
     * @param mixed  $cacheId
     * @param mixed  $compileId
     * @param bool   $returnHash
     * 
     * @return bool при указании параметра $returnHash, возращает в случае упеха индификатор
     *  полученных данных
     */    
    public function isCached($resource, $cacheId = null, $compileId = null)
    {
        /* если кеширование выключено или включен режим 'force_compile', вернем false */
        if ( ( $this->options['force_compile'] ) || (0 == $this->options['caching']) ) {
            return false;
        }
        $params = $this->parse_resource_name($resource);
        
        return $this->_isCache($params, $cacheId = null, $compileId = null);
    }
    
    protected function _isCached(& $params, $cacheId = null, $compileId = null, $returnHash = false)
    {
        if ( $this->options['force_compile'] ) {
            return false;
        }
        
        /* заполним индификаторы, вернем хеш */
        $resource_hash = $this->setResourceId($params['resource_name'], $cacheId, $compileId, true);
        
        $cache = $this->getCacheObject();
        
        /* проверим на существование уже затребованного кеша*/
        if (key_exists($resource_hash, $cache->result) && $cache->result[$resource_hash]['cache']) {
            if ($returnHash) {
                return $resource_hash;
            }
            return true;
        }
        
        if ($this->options['compile_check']) {
            $compile_path = $this->getCompilePath($params, $compileId);
            if ($this->_isCompiledFile($params, $compile_path) != true) {
                return false;
            }
        }
        
        /* проверим существование кеша */
        if ( $this->readCache($params['resource_name'], $resource_hash, $cacheId, $compileId) ) {
            if ($returnHash) {
                return $resource_hash;
            }else {
                return true;
            }
        }
        
        return false;        
    }
    
    /*
     * Выводит результат выполнения шаблона
     *
     * @param string $resource
     * @param mixed  $cache_id
     * @param mixed  $compile_id
     */
    public function display($resource, $cacheId = null, $compileId = null)
    {
        $this->fetch($resource, $cacheId, $compileId, true);
    }
    
    /*
     * Возращает результат выполнения шаблона
     *
     * @param string $resource
     * @param mixed  $cache_id
     * @param mixed  $compile_id
     * @param bool   $print
     * @return string or void
     */
    public function fetch($resource, $cacheId = null, $compileId = null, $print = false)
    {   
        $debug = $this->options['debugging'];
        /* если включен режим отладки, уровень отчетов об ошибке не изменяем */
        if ( $debug ) {
            $this->options['debug_info'][] = array(
                'id' => count($this->options['debug_info']), 
                'parent_id' => count($this->options['debug_info']) - 1, 
                'type' => 'template', 
                'template' => $resource, 
                'depth' => 0);
                 
            $template_self_index = count($this->options['debug_info']) - 1;
            $this->options['debug_parent_id'] = $template_self_index;
            $debug_time_all_start =  microtime(true);
        }else {
            $this->app_error_reporting = error_reporting($this->options['error_reporting']);
        }
        
        $params = $this->parse_resource_name($resource);
        
        do {
            if ( !$this->options['force_compile'] ) {
                /* если кеширование выключено или включен режим 'force_compile', вернем false */
                if ( 0 != $this->options['caching'] ) {       
                    /* проверим кеш */
                    if ($resource_hash = $this->_isCached($params, $cacheId, $compileId, true) ) {
                        $result = $this->cache_object->result[$resource_hash]['result']['data'];
                        $header = $this->cache_object->result[$resource_hash]['result']['header'];
                        if ( $debug ){
                            $this->options['debug_info'][$template_self_index]['cache'] = true;
                            $this->options['debug_info'][$template_self_index]['header'] = $header;
                            $this->options['debug_info'][$template_self_index]['debug_cache_read_time'] = microtime(true) - $debug_time_all_start;
                            $this->options['debug_info'][$template_self_index]['debug_cache_path']       = $cache->last_cache_path;
                            $this->options['debug_info'][$template_self_index]['debug_cache_try'] = true;
                        }
                        break;             
                    }else {
                        if ( $debug ){
                            $this->options['debug_info'][$template_self_index]['cache'] = false;
                            $this->options['debug_info'][$template_self_index]['debug_cache_read_time'] = microtime(true) - $debug_time_all_start;
                            $this->options['debug_info'][$template_self_index]['debug_cache_try'] = false;
                        }
                    }    
                }
            }
            $this->setResourceId($resource, $cacheId, $compileId);
            /* присвоим временные индификаторы для файлов включения */
        
            $this->template_cache_id   = $cacheId;
            $this->template_compile_id = $compileId;

            /* попытка обработать шаблон */       
            try{
                $compile_path = $this->getCompilePath($params, $compileId);
            
                if ( $this->_isCompiledFile($params, $compile_path) != true) {
                    if ( $debug ) {
                        $debug_time_tmp = microtime(true);
                    }
                    /* шаблон на компиляцию */
                    if ( !$this->compileResource($params, $compile_path) ) {
                        
                    }
                    
                    if ( $debug ) {
                        $this->options['debug_info'][$template_self_index]['debug_compile_time'] = microtime(true) - $debug_time_tmp;
                    }
                }
                if ( $debug ) {
                    $debug_time_tmp = microtime(true);
                }
                
                /* выполнить */
                $result = $this->execCompileFile($compile_path);
                
                if ( $debug ) {
                    $this->options['debug_info'][$template_self_index]['debug_exes_time'] = microtime(true) - $debug_time_tmp;
                    $this->options['debug_info'][$template_self_index]['debug_compile_path'] = $compile_path;
                    unset($debug_time_tmp);
                }
        
                /* запишем полученный результат в кеш */
                if ($this->options['force_compile'] != true && 
                            $this->options['caching'] && 
                            $this->options['cache_lifetime'] > 0) {
                                
                    $source['header'] = array('cache_setting' =>array('create_time' => time(), 
                            'exp_time' => time() + $this->options['cache_lifetime'], 
                            'type' => $this->options['caching'] ), 'header_test');
                            
                    $source['data']   = & $result; 
                    
                    if ( $debug ) {
                        $debug_time_tmp = microtime(true);
                    }
                    $this->writeCache($source, $resource, $this->options['cache_lifetime'], $cacheId, $compileId);
                    if ( $debug ) {
                        $this->options['debug_info'][$template_self_index]['debug_cache_write_time'] = $compile_path;
                        $this->options['debug_info'][$template_self_index]['debug_cache_path']       = $this->getCacheObject()->last_cache_path;
                        unset($debug_time_tmp);
                    }
                }
                
                /* ошибка */
            }catch (LapaEngineException $err) {
                /* вернем старый уровень ошибок */
                if ( !$debug ) {
                    error_reporting($this->app_error_reporting);
                }
            
                /* если включена обработка исключений и 
                   нет назначеной пользователем функции для обработки */
                if ( (1 == $this->options['processing_exception']) && 
                                        is_null($this->options['exceptions_function']) ) {
                    /* возбуждаем исключение дальше */
                    throw $err;
                }else {
                    /* нет назначеной пользователем функции для обработки исключения */
                    if ( is_null($this->options['exceptions_function']) ) {
                        throw $err;
                    /* передаем исключение в функцию обработки */    
                    }else {
                        call_user_func_array($this->options['exceptions_function'], $err);
                    }
                }
                return ;
            }
            /* если включен режим отладки, уровень отчетов об ошибке не изменяем */
            if ( !$debug ) {
            /* вернем старый уровень ошибок */
                error_reporting($this->app_error_reporting);
            }
            
        } while ( false );
        if ( $debug ) {
            $this->options['debug_info'][$template_self_index]['debug_all_time'] = microtime(true) - $debug_time_all_start;
        }
        
        if ( $print ) {
            echo $result;
            /* выведем информацию для отладки */
            if ( $debug && $this->options['debug_output'] ) {
                $this->_fetch_debug();
            }
        }else { 
            return $result;
        }
        
    }
    /**
     * Принудительно выводит информацию для отладки
     *
     * При выключенном параметре debug_output вывод <br />отладочной информации 
     * при использовании метода display не происходит.
     * Использовать при включенном параметре debugging. Иначе вернет false
     *
     * @return string
     */
    public function fetchDebug()
    {
        if ( $this->options['debugging'] ) {
            ob_start();
            $this->_fetch_debug();
            $result = ob_get_contents(); ob_get_clean();
            return $result;
        }else {
            return false;
        }
    }
    

    /*
     * Аналог fetch, но для файлов подключаемых из шаблона
     *
     * @param string $resource
     * @param mixed  $cache_id
     * @param mixed  $compile_id
     * @param int    $cache_lifetime
     * @param array  $params_vars
     * @return string 
     */
    protected function _fetch($resource, $cacheId, $compileId, $paramsVars = null, $cacheLifeTime = null)
    {
        $debug = $this->options['debugging'];
        if ( $debug ) {
            $last_parent_id = $this->options['debug_parent_id'];
            $this->options['debug_info'][] = array(
                'id' => count($this->options['debug_info']), 'parent_id' => $last_parent_id, 
                'type' => 'include', 'template' => $resource, 'depth' => $this->options['debug_include_depth'] += 1);
            $template_self_index = count($this->options['debug_info']) - 1;
            
            $debug_time_all_start =  microtime(true);
        }
        
        $params = $this->parse_resource_name($resource);
        
        
        $compile_path = $this->getCompilePath($params, $compileId);
        
        if ($this->options['force_compile'] || $this->_isCompiledFile($params, $compile_path) != true) {
            if ( $debug ) {
                $debug_time_tmp = microtime(true);
            }    
            $this->compileResource($params, $compile_path);
            if ( $debug ) {
                $this->options['debug_info'][$template_self_index]['debug_compile_time'] = microtime(true) - $debug_time_tmp;
            }
        }
        
        if ( $debug ) {
            $debug_time_tmp = microtime(true);
        }
        
        $result = $this->execCompileFile($compile_path, $paramsVars);
        if ( $debug ) {
            $this->options['debug_info'][$template_self_index]['debug_exes_time'] = microtime(true) - $debug_time_tmp;
             unset($debug_time_tmp);
        }
        
        if ($this->options['force_compile'] != true && 
            $this->options['caching'] && $this->options['cache_lifetime'] > 0) {
            $source['header'] = 'header_test';
            $source['data']   = & $result; 
            $this->writeCache($source, $resource, $this->options['cache_lifetime'], $cacheId, $compileId);
        }
        
        if ( $debug ) {
            $this->options['debug_info'][$template_self_index]['debug_all_time'] = microtime(true) - $debug_time_all_start;
            $this->options['debug_include_depth'] -= 1;
            $this->options['debug_parent_id'] = $last_parent_id;
        }
        return $result;
    }
    
    protected function _fetch_debug()
    {
        $params = $this->parse_resource_name('file:'. dirname(__FILE__) . '/debug.tpl.php');
        $compile_path = $this->getCompilePath($params, null);
        
        //if ( false == $this->isCompiledFile($params, $compile_path) ) {
        
            $options['left_delimiter']  = $this->options['left_delimiter'];
            $options['right_delimiter'] = $this->options['right_delimiter'];
            $options['parse_text']      = $this->options['parse_text'];
            
            //$this->options['template_dir'][] = './'; 
            $this->options['parse_text'] = true;
            $this->options['left_delimiter']  = '{';
            $this->options['right_delimiter'] = '}';
            $this->options['debugging'] = false;
            if ( ! $this->_isCompiledFile($params, $compile_path) ){
                $this->compileResource($params, $compile_path);
            }
            $this->options['left_delimiter']  = $options['left_delimiter'];
            $this->options['right_delimiter'] = $options['right_delimiter'];
            $this->options['debugging'] = true;
            $this->options['parse_text'] = $options['parse_text'];
        //}
        
        $_var = & $this->template_vars;
        $_var_local = array('debug_info' => & $this->options['debug_info']);
        
        if (is_readable($compile_path)) {
            include($compile_path);
        }
        
    }
    /**
     * Выполняет и возращает результат выполнения шаблона
     *
     *@return string
     *@param string $compile_path full path to the script
     *@param array  $params_vars - list of local variables  
     */
    protected function execCompileFile($compilePath, $paramsVars = null)
    {
        $_var = & $this->template_vars;
        
        if ( is_array($paramsVars) && count(($paramsVars) ) > 0) {
            $_var_local = & $paramsVars;
        }else {
            $_var_local = array();
        }
        if ( $this->options['debugging'] ) {
            $this->options['debug_info'][$this->options['debug_parent_id']]['debug_local_var'] = & $_var_local;
            $this->options['debug_info'][$this->options['debug_parent_id']]['debug_var'] = $_var;
        }
        if ( is_readable($compilePath) ) {
            ob_start();
            require($compilePath) ;
            $res = ob_get_contents(); ob_get_clean();
        }else {
            require_once('ViewException.php');
            throw new LapaViewException('Скомпелированный шаблон "%s" не найден', $compilePath);
        }
        
        return $res;
    }
    
    public function assign($name, $value = null)
    {
        if (is_array($name)) {
            foreach ($name as $key => $val) {
                if ($key != '') {
                    $this->template_vars[$key] = $val;
                }
            }
        } else {
            if ($name != '') {
                $this->template_vars[$name] = $value;
            }
        }
    }
    
    public function assign_by_ref($name, $value)
    {
        if ($name != '') {
            $this->template_vars[$name] = & $value;
        }
    }
    
    function append($name, $value = null, $merge = false)
    {
        if (is_array($name)) {
            foreach ($name as $_key => $_val) {
                if ($_key != '') {
                    if ( key_exists($_key, $this->template_vars) && !is_array($this->template_vars[$_key]) ) {
                        settype($this->template_vars[$_key], 'array');
                    }
                    if ( $merge && is_array($_val) ) {
                        foreach($_val as $_mkey => $_mval) {
                            $this->template_vars[$_key][$_mkey] = $_mval;
                        }
                    } else {
                        $this->template_vars[$_key][] = $_val;
                    }
                }
            }
        } else {
            if ( $name != '' && isset($value) ) {
                if(key_exists($name, $this->template_vars) && !is_array($this->template_vars[$name])) {
                    settype($this->template_vars[$name], 'array');
                }
                if($merge && is_array($value)) {
                    foreach($value as $_mkey => $_mval)  {
                        $this->template_vars[$name][$_mkey] = $_mval;
                    }
                } else {
                    $this->template_vars[$name][] = $value;
                }
            }
        }
    }

    function append_by_ref($name, &$value, $merge = false)
    {
        if ($name != '' && isset($value)) {
            if(key_exists($name, $this->template_vars) && !is_array($this->template_vars[$name])) {
             settype($this->template_vars[$name], 'array');
            }
            if ($merge && is_array($value)) {
                foreach($value as $_key => $_val) {
                    $this->template_vars[$name][$_key] = &$value[$_key];
                }
            } else {
                $this->template_vars[$name][] = &$value;
            }
        }
    }
    
    public function clear_vars($name = '')
    {
         if ($name != '') {
            if (key_exists($name, $this->template_vars)) {
                unset($this->template_vars[$name]);
            }
         }else {
            $this->template_vars = array();
         }
    }
    
    
    
    /**
     * Проверяет наличие скомпелированного файла
     * 
     * @param array  $params
     * @param string $compile_path
     * @return bool
     */
    protected function _isCompiledFile(& $resourceInfo, $compilePath)
    {   
        $result = false;
        do {
            if ( $this->options['force_compile'] ) 
                break;
            if ( !file_exists($compilePath) ) 
                break;
            if ( !$this->options['compile_check'] ) {
                $result = true;
                break;
            }

            if ( !$this->_getResourceTimestamp($resourceInfo) ) {
                break;
            }
            $compile_timestamp  = filemtime($compilePath);
            
            if ( key_exists('resource_time', $resourceInfo) && ($compile_timestamp > $resourceInfo['resource_time']) ) {
                $result = true;
                break;
            }
        } while (false);
        
        return $result;
    }
        
    /**
     * Получаем информацию о ресурсе
     *
     * @param string $resource
     * @return array
     */
    protected function parse_resource_name($resource)
    {
        $arr_resource = explode(':', $resource, 2);
        if (count($arr_resource) == 2) {
            if (strlen($arr_resource[0]) == 1) {
                $resourceInfo['resource_type'] = $this->options['default_resource_type'];
                $resourceInfo['resource_name'] = $resource;
            }else {
                $resourceInfo['resource_type'] = strtolower($arr_resource[0]);
                $resourceInfo['resource_name'] = $arr_resource[1];
            }
        }else {
            $resourceInfo['resource_type'] = $this->options['default_resource_type'];
            $resourceInfo['resource_name'] = $resource;
        }
        
        return $resourceInfo;
    }
    
      
    /**
     * Получаем путь к файлу
     *
     * @param string $resource
     * @param array  $compile_id
     * @return string 
     */
    protected function getCompilePath(& $resourceInfo, $compileId)
    {
        /* определим разделитель */
        $ds    = $this->options['use_sub_dirs'] ? DIRECTORY_SEPARATOR : '^';
        // file: file.tpl.php
        $f_key = sprintf('%08X', crc32($resourceInfo['resource_type'] . ':' . $resourceInfo['resource_name']));

        /* проверим ключ $compileId */
        if ( empty($compileId) ) {
            $compileId = empty($this->options['compile_id']) ? null : $this->options['compile_id'];
        }
        
        $path  = '';
        $level = $this->options['use_sub_dirs'] ? $this->options['directory_level']: 3;
        
        for ( $i = 0; $i < $level; ++$i ) {
            $path .= substr($f_key, $i, 2) . $ds;
        }

        foreach ( (array)$compileId as $id ) {
            $path .= sprintf('%08X', crc32($id)) . '_';
        }
        
        if ( is_null($this->compile_dir) ) {
            $this->compile_dir = 'templates_c';
        }
        
        $resourceInfo['compile_path'] = $this->compile_dir . $path .= urlencode( basename( $resourceInfo['resource_name']) );
        return $resourceInfo['compile_path'];
    } 
        
    
    
    protected function compileResource(& $resourceInfo, $compile_path)
    {
        $source = '';
        if ( !$this->_getResourceSource($resourceInfo, $source) ) {
            require_once('ViewException.php');
            throw new LapaViewException('Шаблон "%s" не найден ', $resourceInfo['resource_name']);
        }
        
        if ( !class_exists('LapaEngineParser') ) {
            require_once('Engine' . DIRECTORY_SEPARATOR . 'Parser.php');
        }
        
        $parser = new LapaEngineParser();
        $parser->__setOptions($this->options);
        $parser->setTemplateObject($this);
        
        $compile_source = $parser->parse($resourceInfo['resource_name'], $source);
        unset($parser);
        
        return $this->writeFile($compile_source, $compile_path);
    }
    
    /**
     * Подключаем файл во время выполнения
     *
     * @param string $resource имя ресура
     * @param array  $params атрибуты (не используется)
     * @param array  $params_vars список переданных переменных
     */
    public function _includeFile($resource, $params, $params_vars) {
        
        $res = $this->_fetch($resource, $this->template_cache_id, $this->template_compile_id, 0, $params_vars);
        echo $res;
    }
    
    public function _getProcessResource($resource, & $source) 
    {
        $resourceInfo = $this->parse_resource_name($resource);
        $source = '';
        
        if ( $this->_getResourceSource($resourceInfo, $source) ) {
            return true;
        }else {
            require_once('ViewException.php');
            throw new LapaViewException('Шаблон "%s" не найден, тип %s', $resourceInfo['resource_name'], $resourceInfo['resource_type']);
        }
    }
    
    public function setConfigVariable(& $arr, $setion = null)
    {
        $this->config_vars = array_merge($this->config_vars, $arr);
    }
    
    public function getConfigVariable($params)
    {
        if (count($params) == 1) {
            if (key_exists($params[0], $this->config_vars)) 
                return $this->config_vars[$params[0]];
        }else {
            if (key_exists($params[0], $this->config_vars)) {
                if (key_exists($params[1], $this->config_vars[$params[0]])) {
                    return $this->config_vars[$params[0]][$params[1]];
                } 
                $this->notice_error('The configuration variable %s.%s is not declared in a file of a configuration', $params[0],  $params[1]);
            }else $this->notice_error('The configuration variable %s is not declared in a file of a configuration',  $params[0]);
        }
        return '""';
    }
    
    /**
     * Проверяет и присваивает ключи ресурса при необходимости
     *
     * При указании параметра $createHash = true, создает уникальный индификатор ресурса<br />
     * на основе переданных данных
     *
     * @param string $resource
     * @param mixed  $cacheId
     * @param mixed  $compileId
     * @param bool   $toCreateHash
     * @return void Если параметр $createHash установлен в true, вернет полученный индификатор
     */
    protected function setResourceId($resource, & $cacheId, & $compileId, $createHash = false)
    {
        if ($this->options['caching']) {
            if (empty($cacheId) ) {
                $cacheId = empty($this->options['cache_id']) ?  null :$this->options['cache_id'];
            }else{
                if ( !is_array($cacheId) ) {
                    $cacheId = array($cacheId);
                }
            }
        }
        
        if (empty($compileId)) {
            $compileId = empty($this->options['compile_id']) ? null : $this->options['compile_id'];
        }else {
            if ( !is_array($compileId) ) {
                $compileId = array($compileId);
            }
        }
        /**
         * Если используются темы, добавим ключ темы
         */
        if ( !is_null($this->theme) ) {
            $compileId[] = $this->theme;
        }
        
        
        /* если параметр $createHash установлен в true */
        if ($createHash) {
            $hash = sprintf('%08X', crc32($resource));
            if ($this->options['caching']) {
                $hash .= sprintf('%08X', crc32(implode('', $cacheId) . implode('', $compileId)));
            }else {
                $hash .= sprintf('%08X', crc32(implode('', $compileId)));
            }
            return $hash;
        }
        return ;
    }
    
    public function clearCache($resource = null, $cacheId = null, $compileId = null)
    {
        $cache = $this->getCacheObject();
        $cache->clearCache($resource, $cacheId, $compileId);
    }
    
    protected function readCache($resource, $resourceHash, $cacheId, $compileId)
    {
        $cache = $this->getCacheObject();
        return $cache->readCache($resource, $resourceHash, $this->options['cache_lifetime'], $cacheId, $compileId);
    }
    
    protected function writeCache(& $source, $resource, $cacheLifeTime, $cacheId, $compileId)
    {
        $cache = $this->getCacheObject();
        return $cache->writeCache($source, $resource, $cacheLifeTime, $cacheId, $compileId);
    }
        
    protected function getCacheObject()
    {
        if ( !is_object($this->cache_object) ) {
            require_once('Cache' . DIRECTORY_SEPARATOR . 'Cache.php');
            /* если путь к кешу не установлен, попробуем установить по умолчанию */
            if ( is_null($this->options['cache_dir']) ) {
                $this->cache_dir = 'cache';
            }
            $this->cache_object = LapaCache::getObject($this->options['cache_setting']['driver']);
            $this->cache_object->_setOptions($this->options['cache_setting']);
            $this->cache_object->caching = $this->options['caching'];
        }
        return $this->cache_object;
    }
    
    protected function _includePluginResource($resourceType)
    {
        $resource_path = $this->getPluginFilePath($resourceType, 'resource');
        if ( false === $resource_path ) {
           require_once('ViewException.php');
           throw new LapaViewException('Файл ресурсов "%s" не найден', $resourceType);
        }
        require_once($resource_path);   
    }
    
    /**
     * Получаем последнее время изменения ресурса
     *
     * @param array $resourceInfo
     *
     */
    protected function _getResourceTimestamp(& $resourceInfo)
    {
        $function_name = 'lapa_resource_' . $resourceInfo['resource_type'] . '_' . 'timestamp';
        if ( !function_exists($function_name) ) {
            $this->_includePluginResource($resourceInfo['resource_type']);
        }
        return $function_name($resourceInfo, $this);
    }
    
    /**
     * Вызывает функцию получения шаблона
     * 
     *
     * @param  
     *
     */
    protected function _getResourceSource(& $resourceInfo, & $source)
    {
        $function_name = 'lapa_resource_' . $resourceInfo['resource_type'] . '_' . 'source';
        if ( !function_exists($function_name) ) {
            $this->_includePluginResource($resourceInfo['resource_type']);
        }
        return $function_name($resourceInfo, $source, $this);
    }
    
    protected function writeFile(& $source, $compilePath)
    {
        /* шестерка на носу */
        if ( function_exists('get_magic_quotes_runtime') ) {$lv = get_magic_quotes_runtime(); set_magic_quotes_runtime(0);}
        
        $result = false; $try = true;
        
        $dir = dirname($compilePath);
        do {
            if ( file_exists($dir) && ($tmp = tempnam($dir, 'le')) ) {
                $res = file_put_contents($tmp, $source);
                if ( !@rename($tmp, $compilePath) ) {
                    @unlink($compilePath);
                    if ( @rename($tmp, $compilePath) ) {
                        $result = true;
                        break;
                    }
                }else {
                    $result = true;
                    break;
                }
            }
            if (!$try || ($this->options['directory_level'] == 0) ) {
                break;
            }
            if (@mkdir($dir, $this->options['directory_umask'], true) != true) {
                break;
            }
            //@chmod($path, $this->options['directory_umask']);
            $try = false; 
        } while (true);
        if ( function_exists('set_magic_quotes_runtime') ) set_magic_quotes_runtime($lv);
        return $result;
    }
    
}
