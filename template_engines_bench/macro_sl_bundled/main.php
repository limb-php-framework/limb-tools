<?php
require_once(dirname(__FILE__).'/../data.inc');

set_include_path(
  dirname(__FILE__) . '/' . PATH_SEPARATOR .
  dirname(__FILE__) . '/../libs/');
 
require_once('macro_bundled.php');

$config = array(
  'cache_dir' => dirname(__FILE__ ) . '/cache/',
  'is_force_compile' => false,
  'is_force_scan' => false,
  'tpl_scan_dirs' => dirname(__FILE__). '/templates/'
); 

$macro = new lmbMacroTemplate('page_news.phtml', $config, new lmbMacroTemplateLocatorSimple($config));

$random_keys =  array_rand($_ADVERTS,3);

$adverts = array();
foreach($random_keys as $i) {
  array_push($adverts, $_ADVERTS[$i]);
}
$macro->set('adverts', $adverts);

$macro->set('sections', $_SECTIONS);

$macro->set('users', $_STAT);

$macro->set('poll', $_POLL);

$macro->set('news', $_NEWS);

echo $macro->render();
