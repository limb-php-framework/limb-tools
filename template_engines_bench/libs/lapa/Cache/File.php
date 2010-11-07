<?php
/**
 * CacheFile
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
 * @package    LapaCache
 * @subpackage LapaCacheFile
 * @author     Stepanov Sergey <StepanovSergey@tut.by>
 * @copyright  2007 Stepanov Sergey
 * @license    http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @version    v 0.1.2 2007/09/30
 */

class LapaCacheFile extends LapaCache 
{
    /**
     * readCache
     * @param string $resource filename (file:template.tpl or template.tpl )
     * @param string $resourceHash
     * @param int    $cacheLifeTime
     * @param mixed  $cacheId
     * @param mixed  $cacheId2
     *
     */
    public function readCache($resource, $resourceHash, $cacheLifeTime, $cacheId = null, $cacheId2 = null)
    {
        $this->result = array();
        
        $cache_path = $this->getCachePath($resource, $cacheId, $cacheId2);
        
        $this->last_cache_path = $cache_path;
        
        $this->result[$resourceHash]['cache'] = false;
        
        if (is_readable($cache_path) ) {
            if ($this->caching == 1 && (time()> (filemtime($cache_path) + $cacheLifeTime)) ){
                    return false;
            }
            $result = false;
            if (function_exists('get_magic_quotes_runtime')) {$lv = get_magic_quotes_runtime(); set_magic_quotes_runtime(0);}
            
            if ($this->options['file_locking']) {
                if($fp = @fopen($cache_path, 'rb')) {
                    @flock($fp, LOCK_SH);
                    $length = @filesize($cache_path);
                    if ($length) {
                        $content = @fread($fp, $length);
                    } else {
                        $content = '';
                    }
                    @flock($fp, LOCK_UN);
                    @fclose($fp);
                    
                    $result = true;
                }
            }else {
                if ($content = file_get_contents($cache_path)) {
                    $result = true;
                }
            }
            if (function_exists('set_magic_quotes_runtime')) set_magic_quotes_runtime($lv);
            if (!$result) {
                return false;
            }
        }else return false;
        
        if (strlen($content) == 0) 
            return false;
        if (false === ($header_len = strpos($content, "\n")) )
            return false;
                
        $header_file = unserialize(substr($content, 0, $header_len));
        if (!is_array($header_file)) 
            return false;
        if (time() > $header_file['expires'] ) 
            return false;;
        
        $source['header'] = unserialize(substr($content, $header_len + 1, $header_file['header_len']));
        $source['data']   = substr($content, $header_len + $header_file['header_len'] + 1);
        
        if (strlen($source['data']) != $header_file['source_len']) 
            return false;   
        $this->result[$resourceHash]['result'] = & $source;
        $this->result[$resourceHash]['cache']  = true;
        
        return true;
    }
    
    /**
     * writeCache
     *
     * @param array  $source ($source = array('header'=>'header', 'data' =>'')
     * @param string $resource filename (file:template.tpl or template.tpl )
     * @param string $resourceHash
     * @param int    $cacheLifeTime
     * @param mixed  $cacheId
     * @param mixed  $cacheId2
     *
     */
    public function writeCache(& $source, $resource, $cacheLifeTime, $cacheId = null, $cacheId2 = null) 
    {
        $info_source               = serialize($source['header']);
        $header_file['expires']    = time() + $cacheLifeTime;
        $header_file['header_len'] = strlen($info_source);
        $header_file['source_len'] = strlen($source['data']);
        
        $path = $this->getCachePath($resource, $cacheId, $cacheId2);
        
        $this->last_cache_path = $path;
        
        if (function_exists('get_magic_quotes_runtime')) {$lv = get_magic_quotes_runtime(); set_magic_quotes_runtime(0);}
        $result = false; $try = true;
        do {
            if ($this->options['file_locking']) {
                if ($fp = @fopen($path, "wb")) {
                     @flock($fp, LOCK_EX);
                     @fwrite($fp, serialize($header_file) . "\n" . $info_source . $source['data']);
                     @flock($fp, LOCK_UN);
                     @fclose($fp);
                     $result = true;
                     break;
                }
            }else {
                if ($tmp = tempnam(dirname($path), 'le') ) {
                    $res = file_put_contents($tmp, serialize($header_file) . "\n" . $info_source . $source['data']);
                    @unlink($path);
                    if (@rename($tmp, $path)) {
                        $result = true; 
                        break;
                    }
                }
            }
            if (!$try || $this->options['directory_level'] == 0) break;
            
            if (@mkdir(dirname($path), $this->options['directory_umask'], true) != true) {
                break;
            }
            //@chmod($path, $this->options['directory_umask']);
            
            $try = false; 
        } while (true);
        if (function_exists('set_magic_quotes_runtime')) set_magic_quotes_runtime($lv);
        return $result;
    }
    
    public function clearCache($resource = null, $cacheId = null, $cacheId2 = null)
    {
        $clear_path = $this->clearCachePath($resource, $cacheId, $cacheId2);
        $files = glob($clear_path);
        foreach ((array) $files as $file) {
            @unlink($file);
        }
    }
    
    protected function clearCachePath($resource = null, $cacheId = null, $cacheId2 = null)
    {
        $ds    = $this->options['use_sub_dirs'] ? DIRECTORY_SEPARATOR : '^';
        $path = ''; $resource_name = '*';
        if (is_null($resource)) {
            for ($i = 0; $i < $this->options['directory_level']; ++$i) {
                $path .= '*' . $ds;
            }
        }else {
            $f_key = sprintf('%08X', crc32($resource));
            for ($i = 0; $i < $this->options['directory_level']; ++$i) {
                $path .= substr($f_key, $i, 2) . $ds;
            }
            $resource_name = '*' . urlencode(basename($resource));
        }
        if (is_null($cacheId)) {
            //$path .= '*';
        }else {
            foreach ((array) $cacheId as $id) {
                $path .= sprintf('%08X', crc32($id)) . '_';
            }
        }
        if (is_null($cacheId2)) {
            //$path .= '*';
        }else {
            foreach ((array) $cacheId2 as $id) {
                $path .= sprintf('%08X', crc32($id)) . '_';
            }
        }
        $path .= $resource_name . '.t.php';
        return $this->options['cache_dir'] . $path;
    }
    
    protected function getCachePath($resource, $cacheId, $cacheId2)
    {
        $ds    = $this->options['use_sub_dirs'] ? DIRECTORY_SEPARATOR : '^';
        $f_key = sprintf('%08X', crc32($resource));
        $path = '';
        for ($i = 0; $i < $this->options['directory_level']; ++$i) {
            $path .= substr($f_key, $i, 2) . $ds;
        }
        foreach ((array) $cacheId as $id) {
            $path .= sprintf('%08X', crc32($id)) . '_';
        }
        foreach ((array) $cacheId2 as $id) {
            $path .= sprintf('%08X', crc32($id)) . '_';
        }
        return $this->options['cache_dir'] . $path .= urlencode(basename($resource)) . '.t.php';
    } 

}