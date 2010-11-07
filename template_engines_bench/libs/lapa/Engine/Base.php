<?php
/**
 * Base
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
 * @package    Lapa
 * @subpackage LapaEngine
 * @author     Stepanov Sergey <StepanovSergey@tut.by>
 * @copyright  2007 Stepanov Sergey
 * @license    http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @version    v 0.5.7.3 2007/10/27
 */


require_once('Engine' . DIRECTORY_SEPARATOR . 'Exception.php'); 

class LapaEngineBase
{
    /**
     * $options
     *
     * @var array
     */
    protected $options = array(
                               'debugging'             => false,
                               'parse_text'            => false,
                               'left_delimiter'        => '{',
                               'right_delimiter'       => '}',
                               'plugins_dir'           => array(),
                               'functions'             => array('count', 'var_dump', 'isset', 'empty', 'print_r'),
                               'functions_modifier'    => array('count', 'str_repeat'),
                               'directives'            => array('break'=>true),
                               'allow_constants'       => false,
                               'debug_info'            => array(array('id' => 0, 'parent_id' => 0)), 
                               'debug_include_depth'   => 0,
                               'debug_parent_id'       => 0,
                               );
    
    public $version = '0.5.7';
        
    /**
     * set properties of object
     *
     * @return void 
     * @param string $var_name
     * @param mixed  $val
     */
    public function __set($var_name, $val)
    {
        switch ($var_name) {
            case 'right_delimiter': case 'left_delimiter': 
                $this->options[$var_name] = $val;
            break;
            case 'debugging':
                $this->options[$var_name] = (bool)$val;
            break;
            case 'directives':
                if (is_array($val)) {
                    $this->options['directives'][$val[0]] = $val[1];
                }else $this->notice_error('Wrong format of data for property "%s", in %s on line %s, method %s', $var_name, __FILE__, __LINE__, __METHOD__);
            break;
            case 'allow_constants': case 'parse_text':
                $this->options[$var_name] = (bool)$val;
            break;  
            default:
                 $this->warning_error('Properties a name "%s" do not exist, in %s on line %s, method %s', $var_name, __FILE__, __LINE__, __METHOD__);
        }
    }
    
    public function __get($var_name)
    {
        if (key_exists($var_name, $this->options)) {
            return $this->options[$var_name];
        }else {
            $this->warning_error('�������� "%s" �� ����������, ������ %s, method %s', $var_name, __LINE__, __METHOD__);
        }
        return NULL;
    }
    
    
    /**
     * ��� �������������
     * 
     * @param string $method
     * @param array  $params
     */
    public function __call($method, $params)
    {
        switch ($method) {
            /* For compatibility Smarty */
            case '_get_plugin_filepath':
                return call_user_func_array(array($this, 'getPluginFilePath'), $params);
            break;
            default:
                $this->warning_error('������ "%s" �� ����������,  %s, ������ %s, method %s', $var_name, __LINE__, __METHOD__);
        }
    } 
    
    /**
     * �������� ���������
     *
     * @param array & $options
     * @return void
     */
    public function __setOptions(& $options)
    {
        $this->options = & $options;
    }
    
    /**
    * ��������� ���� � ������� ������� ��� <br>
    * false � ������ �������
    *
    * @param string $type �������� 'compile' ��� 'function' ��� 'modifiler'
    * @param string $funct_��� ������� ��� ��������
    * @return string ���� �� �������, ��� false � ������ �������
    */
    public function getPluginFilePath($functionName, $functionPrefix) 
    {
        if ( count( (array) $this->options['plugins_dir']) == 0 ) {
            $this->options['plugins_dir'] = array(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR .'Plugins' . DIRECTORY_SEPARATOR);
        }
        
        foreach ( $this->options['plugins_dir'] as $dir ) {
            $function_name = "$dir$functionPrefix" . DIRECTORY_SEPARATOR . "$functionPrefix.$functionName.php";
            if ( file_exists($function_name) ) {
                 return $function_name;
            }
        }
        return false;
    }
    
    
    /**
     * �������� ������������� �� ����� ����������
     * 
     * @param $params
     */
    protected function loadFunction($params, $functionPrefix)
    {
        foreach ( (array) $params as $function_name => $function_info) {
            if ( function_exists($function_info['function_name']) ) {
                continue;
            }
            
            if ( file_exists($function_info['path']) ) {
                require_once($function_info['path']);
            }else {
                $path = $this->getPluginFilePath($function_name, $functionPrefix);
                if ( false === $path ) {
                    trigger_error('������� ' . $functionPrefix . ' _ ' . $function_name . ' �� �������', E_USER_ERROR);
                }
                require_once($path);
            }
        }        
    }
    
    /**
     * ��� ������ ��������������<br /> ������������ ���������� ������� sprintf
     * 
     * @param mixed $var ����� ���������� �������� 
     * @return void
     */
    public function notice_error()
    {
        $params = func_get_args();
        $this->trigger_error($params, E_USER_NOTICE);
    }
    
    /**
     * ��� ������ ��������������<br /> ������������ ���������� ������� sprintf
     * 
     * @param mixed $var ����� ���������� �������� 
     * @return void
     */
    public function warning_error()
    {
        $params = func_get_args();
        $this->trigger_error($params, E_USER_WARNING); 
    }
    
    /**
     * ��������������� �������
     * 
     * ��� ������ �������������� �������������� <br />
     * ������� notice_error() ��� warning_error()
     * 
     * @param array $params  
     * @param int   $const_error 
     * @return void
     */
    protected function trigger_error($params, $const_error)
    {
        $message = count($params) > 1 ? call_user_func_array('sprintf', $params) : implode('', $params);
        trigger_error($message, $const_error);
    }
}