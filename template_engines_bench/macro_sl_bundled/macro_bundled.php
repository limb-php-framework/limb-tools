<?php

interface lmbMacroTemplateLocatorInterface {
  function __construct($config);
  function locateSourceTemplate($file_name);
  function locateCompiledTemplate($file_name);
}

/**
 * class lmbMacroSimpleTemplateLocator.
 *
 * @package macro
 * @version $Id$
 */
class lmbMacroTemplateLocatorSimple implements lmbMacroTemplateLocatorInterface
{
  protected $config;

  function __construct($config)
  {
    $this->config = $config;
  }

  function locateSourceTemplate($file_name)
  {                
    $file_path = $this->config['tpl_scan_dirs'].'/'.$file_name;
    if(!file_exists($file_path))
      throw new lmbMacroException('template file not found', array('template' => $file_path));
    return $file_path;
  }

  function locateCompiledTemplate($file_name)
  {
    return $this->config['cache_dir'] . '/' . md5($file_name) . '.php';
  }
}

/**
 * class lmbMacroTemplate.
 *
 * @package macro
 * @version $Id$
 */
class lmbMacroTemplate
{
  protected $file;
  protected $compiled_file;
  protected $executor;
  protected $vars = array();
  protected $child_executor;
  public $config;

  function __construct($file, $config = array(), lmbMacroTemplateLocatorInterface $locator = null)
  {
    $this->file = $file;
    $this->config = $config;        
    $this->_applyConfig($config);
    $this->locator = $locator ? $locator : new lmbMacroTemplateLocator($this->config);
  }
  
  protected function _applyConfig($config)
  {
    $this->config['cache_dir'] = (isset($config['cache_dir'])) ? $config['cache_dir'] : LIMB_VAR_DIR . '/compiled';
    $this->config['is_force_compile'] = (isset($config['is_force_compile'])) ? $config['is_force_compile'] : true;
    $this->config['is_force_scan'] = (isset($config['is_force_scan'])) ? $config['is_force_scan'] : false;
    $this->config['tpl_scan_dirs'] = (isset($config['tpl_scan_dirs'])) ? $config['tpl_scan_dirs'] : array('templates');
    $this->config['tags_scan_dirs'] = (isset($config['tags_scan_dirs'])) ? $config['tags_scan_dirs'] : array('limb/macro/src/tags');
    $this->config['filters_scan_dirs'] = (isset($config['filters_scan_dirs'])) ? $config['filters_scan_dirs'] : array('limb/macro/src/filters');    
  }

  function setVars($vars)
  {
    $this->vars = $vars;
  }

  function set($name, $value)
  {
    $this->vars[$name] = $value;
  }

  function setChildExecutor($executor)
  {
    $this->child_executor = $executor;
  }

  function render($vars = array())
  {     
    if(!$this->executor)
    {
      $this->compiled_file = $this->locator->locateCompiledTemplate($this->file);

      if($this->config['is_force_compile'] || !file_exists($this->compiled_file))
      {
        $macro_executor_class = 'MacroTemplateExecutor' . uniqid();//think about evaling this instance

        $compiler = $this->_createCompiler();
        $compiler->compile($this->file, $this->compiled_file, $macro_executor_class, 'render');
        //appending macro executor class
        file_put_contents($this->compiled_file, file_get_contents($this->compiled_file) .
                                          "\n\$macro_executor_class='$macro_executor_class';");
      }

      include($this->compiled_file);
      $this->executor = new $macro_executor_class($this->config);
    }

    $this->executor->setVars($this->vars);

    //in case of dynamic wrapping we need to ask parent for all unknown variables
    if($this->child_executor)
      $this->child_executor->setContext($this->executor);

    ob_start();
    $this->executor->render($vars);
    $out = ob_get_contents();
    ob_end_clean();
    return $out;
  }

  protected function _createCompiler()
  {
    require_once('limb/macro/common.inc.php');
    lmb_require('limb/macro/src/compiler/*.interface.php');
    lmb_require('limb/macro/src/compiler/*.class.php');

    $tag_dictionary = lmbMacroTagDictionary :: instance();
    $filter_dictionary = lmbMacroFilterDictionary :: instance();
    $tag_dictionary->load($this->config);
    $filter_dictionary->load($this->config);

    return new lmbMacroCompiler($tag_dictionary, $this->locator, $filter_dictionary);
  }
}
?>