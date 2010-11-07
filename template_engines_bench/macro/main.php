<?php
require_once(dirname(__FILE__).'/../data.inc');

set_include_path(
  dirname(__FILE__) . '/' . PATH_SEPARATOR .
  dirname(__FILE__) . '/../libs/');
                
require_once('limb/macro/common.inc.php');
 
$config = array(
  'cache_dir' => dirname(__FILE__ ) . '/cache/',
  'is_force_compile' => false,
  'is_force_scan' => false,
  'tpl_scan_dirs' => dirname(__FILE__). '/templates/'
);

$macro = new lmbMacroTemplate('page_news.phtml', $config);

$random_keys =  array_rand($_DATA['ADVERTS'],3);
$adverts = array();
foreach($random_keys as $i) {
  array_push($adverts, $_DATA['ADVERTS'][$i]);
}
$macro->set('adverts', $adverts);

$macro->set('sections', $_DATA['SECTIONS']);
$macro->set('users', $_DATA['STAT']);
$macro->set('poll', $_DATA['POLL']);
$macro->set('news', $_DATA['NEWS']);
 
echo $macro->render();