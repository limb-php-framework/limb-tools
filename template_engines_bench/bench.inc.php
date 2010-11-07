<?php
function get_bench_root_url()
{
  $uri = $_SERVER['REQUEST_URI'];  
  $index_pos = strpos($uri, 'index.php');
  return "http://".$_SERVER['HTTP_HOST'] . substr($uri, 0, strpos($uri, 'index.php'));
}

function get_engine_dir($engine_name)
{
  return BENCH_ENGINES_DIR.'/'.$engine_name;
}

function get_engine_bench_url($engine_name)
{
  return get_bench_root_url().$engine_name.'/main.php';
}

function bench_one($engine_name, $iterations, $concurency)
{
  $url = get_engine_bench_url($engine_name);
  $ab_str = BENCH_PATH_TO_AB." -dS -n ".$iterations." -c ".$concurency.' ';
  $sys_str = $ab_str . $url. ' | grep "per second"';
  $result_str = exec($sys_str);
  $result = explode(':',$result_str);
  return (int) $result[1];
}

function bench_engines($engines, $iterations, $concurency)
{
  $results = array();
  for($i = 0, $cnt = count($engines); $i < $cnt; $i++)
  {
    $result = bench_one($engines[$i], $iterations, $concurency);
    $results[$engines[$i]] = $result;
  }
  arsort($results);
  log_results($results, $iterations, $concurency);
  return $results;
}

function is_engine($file)
{
  return is_dir(BENCH_ENGINES_DIR . '/'.$file)
    && 'libs' != $file
    && '.' != $file
    && '..' != $file
    && ('.' != substr($file, 0, 1));
}

function log_results($engines, $iterations, $concurency)
{
  $fp = fopen(BENCH_LOG_PATH, 'a');
  fwrite($fp, json_encode(array('engines' => $engines, 'iterations' => $iterations, 'concurency' => $concurency))."\n");
  fclose($fp);
}

function get_engines()
{
  $engines = array();
  foreach(scandir(BENCH_ENGINES_DIR) as $file)
    if(is_engine($file)) $engines[] = $file;
  return $engines;
}

function get_engine_version($engine)
{
  $file = get_engine_dir($engine).'/VERSION';
  if(!file_exists($file))
    return '--';

  return file_get_contents($file);
}

function get_engine_description($engine)
{
  $file = get_engine_dir($engine).'/DESCRIPTION';
  if(!file_exists($file))
    return '--';

  return file_get_contents($file);
}

function get_engine_home_link($engine)
{
  $file = get_engine_dir($engine).'/HOME_URL';
  if(!file_exists($file))
    return;

  $url = file_get_contents($file);
  return '<a href="http://'.$url.'">'.$url.'</a>';
}
?>