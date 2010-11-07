<?php
/**
 * Cache
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
 * @subpackage LapaCache
 * @author     Stepanov Sergey <StepanovSergey@tut.by>
 * @copyright  2007 Stepanov Sergey
 * @license    http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @version    v 0.1.2 2007/09/30
 */
abstract class LapaCache 
{    
    protected $options;
    
    public $result = array();
    
    public $caching;
    
    public $last_cache_path;
    
    static public function getObject($driver)
    {
        $class = 'Cache' . $driver;
        $try = 0; $result = true;
        do {
            if (class_exists($class)) {
                $cache_object = new $class();
                break;
            }
            if ($try > 0) {
                $result = false;
                break;
            }
            
            $class = 'LapaCache' . ucfirst($driver);
            $include_path  = dirname(__FILE__) . DIRECTORY_SEPARATOR . ucfirst($driver) . '.php';
            if (file_exists($include_path) ) {
                require_once($include_path);
            }
            ++$try;
        } while(true);
        
        if (!$result) {
            // error? исключение
        }
        return $cache_object;
    }
        
    public function _setOptions(& $options) {
        $this->options = & $options;
    }
    
    abstract public function readCache($resource, $resourceHash, $cacheLifeTime, $cacheId = null, $cacheId2 = null);
    
    abstract public function writeCache(& $paramsSource, $resource, $cacheLifeTime, $cacheId = null, $cacheId2 = null);
     
    abstract public function clearCache($resource = null, $cacheId = null, $cacheId2 = null);
    //abstract public function clearCache($resource, $cacheId = null, $cacheId2 = null);
    
    
    
    
    
}