<?php 
/**
 * config_load
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

function lapa_function_config_load($params, $parser) 
{    
    if(!isset($params['file']))
        throw new LapaEngineException('The configuration file is not set');
    $config_file = $params['file'];
    if (empty($parser->config_dir)) {
        $parser->config_dir = array('configs');
    }
    $dirs = $parser->config_dir;
    $config_path = false;
    foreach ($dirs as $dir) {
        if (file_exists($dir . $config_file))
            $config_path = $dir . $config_file;
    }
    
    if($config_path && is_readable($config_path) ) {
        $arr_ini = parse_ini_file($config_path, true);
    }else {
        throw new LapaEngineException('The requested file of a configuration is not accessible to reading');
    }
    $section = isset($params['section']) ? $params['section']: null;
    $parser->setConfigVariable(& $arr_ini, $section);
}
   
?>