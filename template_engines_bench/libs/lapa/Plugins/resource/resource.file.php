<?php
/**
 * Plugins Resource File
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
 * @package    Plugins
 * @subpackage Resource
 * @author     Stepanov Sergey <StepanovSergey@tut.by>
 * @copyright  2007 Stepanov Sergey
 * @license    http://www.opensource.org/licenses/lgpl-license.php LGPL
 */
 
 
/**
 * $resourceInfo['resource_path'] = 'полный путь ресурса';
 * $resourceInfo['resource_time'] = 'последнее изменение'; 
 */
 
 
/**
 * ¬озращает содержимое ресурса
 * 
 * 
 * @param string $resourceInfo
 * @param string $source
 * @param object $object
 * 
 * @return bool
 */
function lapa_resource_file_source(& $resourceInfo, & $source, $object)
{
    if ( !lapa_resource_file_path($resourceInfo, $object) ) {
        return false;
    }
    
    if (function_exists('get_magic_quotes_runtime')) {
        $lv = get_magic_quotes_runtime(); set_magic_quotes_runtime(0);
    }
    
    $source = file_get_contents($resourceInfo['resource_path']);
    
    if ( isset($lv) ) {
        set_magic_quotes_runtime($lv);
    }
    
    return true;
}

/**
 * ¬озращает врем€ последнего изменени€ ресурса
 * 
 * 
 * @param string $resourceInfo
 * @param object $object
 * 
 * @return bool
 */
function lapa_resource_file_timestamp(& $resourceInfo, $object)
{
    if ( !lapa_resource_file_path($resourceInfo, $object) ) {
        return false;
    }
    $file_time = @filemtime($resourceInfo['resource_path']);
    
    if ( $file_time ) {
        $resourceInfo['resource_time'] = $file_time;
        return true;
    }else {
        return false;
    }
}

/**
 * ¬озращает путь до ресурса
 * 
 * 
 * @param string $resourceInfo
 * @param object $object
 * 
 * @return bool
 */
 
function lapa_resource_file_path(& $resourceInfo, $object)
{
    $result = false;
    do {
        if ( 0 == strlen($resourceInfo['resource_name'])) {
            break;
        }
        /* если ресурс уже находили ранее */
        if ( key_exists('resource_path', $resourceInfo) ) {
            if ( !file_exists($resourceInfo['resource_path']) ) {
                break;
            }else {
                $result = true;
                break;
            }
        }
        /**
         * ѕровер€ем путь (либо /, \, с:)
         */
        if ( preg_match('/^([\/\\\\]|[a-zA-Z]:[\/\\\\])/', $resourceInfo['resource_name'] )) {
            if ( !file_exists($resourceInfo['resource_name']) ) {
                break;
            }
                
            $resourceInfo['resource_path'] = $resourceInfo['resource_name'];
            $result = true;
        }else {
            $tpl_dirs = $object->template_dir;
            if ( 0 == count($tpl_dirs) ) {
                $object->template_dir = 'templates';
                $tpl_dirs = $object->template_dir;
            }
            /**
             * ѕроверим темы
             */
            if ( !is_null($object->theme) ) {
                $theme = $object->theme;
                foreach ( (array) $tpl_dirs as $dir) {
                    if ( file_exists($dir . $theme . $resourceInfo['resource_name']) ) {
                        $resourceInfo['resource_path'] = $dir . $theme . $resourceInfo['resource_name'];
                        $result = true;
                        break;
                    }
                }
                /**
                 * ≈сли темы не найдены, проверим темы по умолчанию
                 */
                if ( !is_null($object->default_theme) ) {
                    $theme = $object->default_theme;
                    foreach ( (array) $tpl_dirs as $dir) {
                        if ( file_exists($dir . $theme . $resourceInfo['resource_name']) ) {
                            $resourceInfo['resource_path'] = $dir . $theme . $resourceInfo['resource_name'];
                            $result = true;
                            break;
                        }
                    }
                }
            }
            
            
            /**
             * ≈сли в темах ничего не найдено, ищем по умолчанию
             */
            foreach ( (array) $tpl_dirs as $dir) {
                if ( file_exists($dir . $resourceInfo['resource_name']) ) {
                    $resourceInfo['resource_path'] = $dir . $resourceInfo['resource_name'];
                    $result = true;
                    break;
                }
            }
        }
    } while (false);

    return $result;
}