<?php
function quicky_compiler_include($params,&$compiler)
{
 static $nesting = array();
 static $tmp = 0;
 if (!isset($params['file'])) {return $compiler->_syntax_error('assign: missing \'file\' parameter in include function');}
 if (isset($params['assign']) and preg_match('~^\w+$~',$params['assign'])) {$params['assign'] = $compiler->_var_token('$'.$params['assign']);}
 $assign = '';
 $noinline = isset($params['noinline']);
 unset($params['noinline']);
 foreach ($params as $k => $v)
 {
  if ($k != 'file' and $k != 'assign')
  {
   $assign .= '$this->assign('.var_export($k,TRUE).','.$v.');'."\n";
  }
 }
 $path = $params['file'];
 $dir = dirname($compiler->template_from);
 if ($dir === '') {$dir = '.';}
 $path = trim(preg_replace('~(?:^|\'?\.)\$dir(?:$|\.\'?)?~',$dir,$path),'\'');
 if (strpos($path,'$') === FALSE && isset($compiler->prefs['inline_includes']) && $compiler->prefs['inline_includes'] && !$noinline)
 {
  if ($path === '' or is_null($path)) {return $compiler->_syntax_error('Empty include-path given');}
  if (is_dir($compiler->parent->_get_template_path($path))) {return $compiler->_syntax_error('Path is directory');}
  $nesting[] = $path;
  $repeats = sizeof(array_intersect($nesting,array($path)));
  if ($repeats > $compiler->parent->max_recursion_depth) {return $compiler->_syntax_error('Max recursion depth of inline-includes exceed ('.$path.')');}
  $return = '';
  if (isset($params['assign']))
  {
   if (is_int(array_search('ob',$params))) {$return .= '<?php $old_inc_ob'.$tmp++.' = ob_get_clean(); ob_start(); ?>';}
   else {$return .= '<?php $old_inc_write_out_to'.$tmp++.' = $this->_write_out_to; $this->_write_out_to = '.$params['assign'].'; ?>';}
  }
  $nesting_old = $nesting;
  $compiler->parent->_compile($path,'',$compiler->compiler_name);
  $nesting = $nesting_old;
  if ($assign !== '') {$assign = '<?php '.$assign.' ?>';}
  $return .= $assign.file_get_contents($compiler->parent->_get_compile_path($path,''));
  if (isset($params['assign']))
  {
   if (is_int(array_search('ob',$params))) {$return .= '<?php '.$params['assign'].' = ob_get_clean(); echo $old_ob'.$tmp.'; ?>';}
   else {$return .= '<?php  $this->_write_out_to = $old_inc_write_out_to'.$tmp++.'; ?>';}
  }  
  return $return;
 }
 if (isset($params['assign'])) {return '<?php '.$params['assign'].' = $this->fetch('.$params['file'].'); ?>';}
 return '<?php '.$assign.' $this->display('.$params['file'].'); ?>';
}
