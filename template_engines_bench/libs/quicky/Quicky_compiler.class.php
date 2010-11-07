<?php
/**************************************************************************/
/* Quicky: smart and fast templates
/* ver. 0.4
/* ===========================
/*
/* Copyright (c)oded 2007 by WP
/* http://quicky.keeperweb.com
/*
/* Quicky_compiler.class.php: Template compiler
/**************************************************************************/
class Quicky_compiler
{
 public $precompiled_vars = array();
 public $prefilters = array();
 public $postfilters = array();
 public $compiler_name = 'Quicky';
 public $compiler_version = '0.3';
 public $load_plugins = array();
 public $seq = array();
 public $seq_id = 0;
 public $_alt_tag = FALSE;
 public $prefs = array();
 public $template_defined_functions = array();
 public $_cast_undefined_token_to_strings = TRUE;
 public $allowed_php_tokens = array('array','date','strtotime','isset','empty','is_empty','count', 'sizeof',
							'is_array','is_int','is_float','is_long','is_numeric','is_object',
							'is_scalar','is_string','gettype','is_real',
							'abs','acos','acosh','asin','asinh','atan2','atan','atanh','base_','bindec',
							'ceil','cos','cosh','decbin','dechex','decoct','deg2rad','exp','expm1','floor',
							'fmod','getrandmax','hexdec','hypot','is_finite','is_infinite','is_nan','lcg_','log10','log1p',
							'log','max','min','mt_getrandmax','mt_rand','mt_srand','octdec','pi','pow','rad2deg','rand',
							'round','sin','sinh','sqrt','srand','tan','tanh',
							'constant','strlen','time','var_dump','var_export',
							'gmp_*','ctype_*','array_*','addcslashes','addslashes','bin2hex','chop','chr',
							'chunk_split','convert_cyr_string','convert_uudecode','convert_uuencode','count_chars',
							'crc32','crypt','echo','explode','fprintf','get_html_translation_table','hebrev','hebrevc',
							'html_entity_decode','htmlentities','htmlspecialchars_decode','htmlspecialchars','implode',
							'join','levenshtein','localeconv','ltrim','md5_file','md5','metaphone','money_format',
							'nl_langinfo','nl2br','number_format','ord','parse_str','print','printf',
							'quoted_printable_decode','quotemeta','rtrim','sha1_file','sha1','similar_text',
							'soundex','sprintf','sscanf','str_ireplace','str_pad','str_repeat','str_replace',
							'str_rot13','str_shuffle','str_split','str_word_count','strcasecmp','strchr',
							'strcmp','strcoll','strcspn','strip_tags','stripcslashes','stripos','stripslashes',
							'stristr','strlen','strnatcasecmp','strnatcmp','strncasecmp','strncmp','strpbrk',
							'strpos','strrchr','strrev','strripos','strrpos','strspn','strstr','strtok',
							'strtolower','strtoupper','strtr','substr_compare','substr_count','substr_replace',
							'substr','trim','ucfirst','ucwords','vfprintf','vprintf','vsprintf','wordwrap','and','or','xor',
							'json_encode','lang_om_number','intval','floatval','strval','setcookie','in_array',
							'long2ip','ip2long');
 public $allowed_php_constants = array();
 public $syntax_error = NULL;
 public $template_from;
 public $blocks = array();
 public $left_delimiter = '{';
 public $right_delimiter = '}';
 public $magic_constants = array('tplpath','tplpathdir','ldelim','rdelim');
 public $block_props = array();
 public $_write_out_to = '';
 public $_halt = FALSE;
 public $_tag_stacks = array();
 public $_tag_stack_n = 0;
 public $_no_magic = FALSE;
 public $no_optimize = FALSE;
 public $_tmp = array();
 public $_cpl_vars = array();
 public $_cpl_config = array();
 function Quicky_compiler() {}
 static function escape_string($s)
 {
  static $escape = array(
	'\\' => '\\\\',
	'\'' => '\\\''
  );
  return strtr($s,$escape);
 }
 function push_block_props($props,$blocktype,$name)
 {
  for ($i = 0; $i < sizeof($props); $i++) {$this->block_props[$props[$i]] = array($name,$blocktype);}
  return TRUE;
 }
 function _syntax_error($error) {$this->syntax_error = $error; return UNIQUE_HASH.'~~error here~~';}
 function _write_seq($m)
 {
  if (!isset($this->seq[$this->seq_id])) {$this->seq[$this->seq_id] = array();}
  $this->seq[$this->seq_id][] = $m[1]; 
  return $this->seq_hash;
 }
 function _read_seq($reset = FALSE)
 {
  static $i = array();
  if ($reset === TRUE or !isset($i[$this->seq_id])) {$i[$this->seq_id] = 0; return;}
  return $this->seq[$this->seq_id][$i[$this->seq_id]++];
 }
 function register_block($block)
 {
  if (!in_array($block,$this->blocks)) {$this->blocks[] = $block;}
  return TRUE;
 }
 function unregister_block($block)
 {
  if ($k = array_search($block,$this->blocks)) {unset($this->blocks[$k]); return TRUE;}
  else {return FALSE;}
 }
 function _block_lang_callback($m)
 {
  $name = $m[2];
  $tag = $m[3];
  preg_match_all('~\{(\w+)\}(.*?)(?=\{\w+\}|\z)~s',$tag,$matches,PREG_SET_ORDER);
  $variants = array();
  foreach ($matches as $m) {$variants[strtolower($m[1])] = trim($m[2]);}
  $reqlang = $this->parent->lang;
  if (isset($variants[$reqlang])) {return $variants[$reqlang];}
  return isset($variants['default'])?$variants['default']:'Warning! Can\'t find phrase '.($name !== ''?'('.htmlspecialchars($name).')':'').' for this language.';
 }
 function _form_detect_field($m)
 {
  $tag = strtolower($m[1] !== ''?$m[1]:$m[3]);
  $params = $this->_parse_params($m[1] !== ''?$m[2]:$m[4],TRUE);
  if ($tag == 'option')
  {
   $params['text'] = $m[5];
   if (!isset($params['value'])) {$params['value'] = $params['text'];}
  }
  elseif ($tag == 'textarea')
  {
   $params['value'] = $m[5];
  }
  $p = '';
  if (isset($params['name']) and !isset($params['join'])) {$params['join'] = $params['name'];}
  foreach ($params as $k => $v) {$p .= $k.'=\''.$this->escape_string($this->_dequote($v)).'\' ';}
  $p = substr($p,0,-1);

  if ($tag == 'input' or $tag == 'textarea')
  {
   $return = $this->left_delimiter.$tag.' '.$p.$this->right_delimiter;
  }
  elseif ($tag == 'option')
  {
   $return = $this->left_delimiter.'option '.$p.$this->right_delimiter;
  }
  elseif ($tag == 'select')
  {
   $body = preg_replace_callback('~<(option)(\s+.*?)?>(.*?)</\3>~si',array($this,'_form_detect_field'),$m[2]);
   $return = $this->left_delimiter.$tag.' '.$p.$this->right_delimiter.$m[5].$this->left_delimiter.'/'.$tag.$this->right_delimiter;

  }
  return $return;
 }
 function _form_detect($m)
 {
  $params = $this->_parse_params($m[1],TRUE);
  $form_name = '';
  $p = '';
  $params['auto_object'] = 1;
  foreach ($params as $k => $v)
  {
   if (strtolower($this->_dequote($k)) == 'name') {$form_name = $this->_dequote($v);}
   $p .= $k.'=\''.$this->escape_string($this->_dequote($v)).'\' ';
  }
  if (!$this->parent->_auto_detect_forms and !in_array($form_name,$this->parent->_detect_forms)) {return $m[0];}
  $p = substr($p,0,-1);
  $body = preg_replace_callback('~<(input)(\s+.*?)?\s*/?\s*>|<(textarea|select)(\s+.*?)?>(.*?)</\3>~si',array($this,'_form_detect_field'),$m[2]);
  $return = '{form '.$p.'}'.$body.'{/form}';
  return $return;
 }
 function _compile_source_string($template,$from)
 {
  $old_load_plugins = $this->load_plugins;
  $this->load_plugins = array();
  $this->template_from = $from;
  //$template = preg_replace('~\r\n|\r(?!\n)~',"\n",$template);
  $template = preg_replace('~^/.*?/\r?\n~','',$template);

  $ldelim = preg_quote($this->left_delimiter,'~');
  $rdelim = preg_quote($this->right_delimiter,'~');
  $template = preg_replace_callback('~([\'"]).*?\1|('.$ldelim.'\*.*?\*'.$rdelim.')~s',create_function('$m','if (!isset($m[2])) return $m[0]; return \'\';'),$template);

  $a = array_values($this->prefilters);
  for ($i = 0,$s = sizeof($a); $i < $s; $i++) {$template = call_user_func($a[$i],$template,$this);}
  $source = $template;

  if ($this->parent->lang !== '')
  {
   $source = preg_replace_callback('~'.$ldelim.'_\s+(.*?)'.$rdelim.'~',$this->parent->lang_callback,$source);
   $source = preg_replace_callback('~'.$ldelim.'e_\s+(.*?)'.$rdelim.'~i',$this->parent->lang_callback_e,$source);
   $source = preg_replace_callback('~'.$ldelim.'LANG(?:=([\'|"])?(.*?)\1)?'.$rdelim.'(.*?)'.$ldelim.'/LANG'.$rdelim.'~si',array($this,'_block_lang_callback'),$source);
  }
  if ($this->parent->_auto_detect_forms or sizeof($this->parent->_detect_forms) > 0)
  {
   $source = preg_replace_callback('~<form(\s+.*?)?>(.*?)</form>~si',array($this,'_form_detect'),$source);
  }
  if (!isset($this->prefs['allow_php_native']) or !$this->prefs['allow_php_native'])
  {
   $source = preg_replace('~<\?(?:php)?|\?>~i','<?php echo \'$0\'; ?>',$source);
  }
  $this->seq_hash = md5(microtime());
  $ldelim = preg_quote($this->left_delimiter,'~');
  $rdelim = preg_quote($this->right_delimiter,'~');
  $source = preg_replace_callback('~'.$ldelim.'literal'.$rdelim.'(.*?)'.$ldelim.'/literal'.$rdelim.'~si',array($this,'_write_seq'),$source);
  $cur_seq = $this->seq;
  $cur_hash = $this->seq_hash;
  $this->seq = array();
  $source = $this->_tag_token($source);
  $this->seq = $cur_seq;
  $this->seq_hash = $cur_hash;
  $this->_read_seq(TRUE);
  $source = preg_replace_callback('~'.$this->seq_hash.'~si',array($this,'_read_seq'),$source);
  $this->seq = array();
  if (!$this->no_optimize and FALSE)
  {
   $source = preg_replace_callback('~\?>(.{0,20}?)<\?php~s',create_function('$m','if ($m[1] === \'\') {return \'\';} return \' echo \\\'\'.Quicky_compiler::escape_string($m[1]).\'\\\';'."\n".'\';'),$source);
   $source = preg_replace_callback('~^(.{1,20}?)(<\?php)~s',create_function('$m','return $m[2].\' echo \\\'\'.Quicky_compiler::escape_string($m[1]).\'\\\';'."\n".'\';'),$source);
   $source = preg_replace_callback('~(\?>)(.{1,20})$~s',create_function('$m','return \' echo \\\'\'.Quicky_compiler::escape_string($m[2]).\'\\\';'."\n".'\'.$m[1];'),$source);
  }
  $header = '<?php /* Quicky compiler version '.$this->compiler_version.', created  on '.date('r').'
			 compiled from '.$from.' */'."\n";
  for ($i = 0,$s = sizeof($this->load_plugins); $i < $s; $i++)
  {
   $header .= 'require_once '.var_export($this->load_plugins[$i],TRUE).';'."\n";
  }
  $header .= '?>';
  if ($this->syntax_error !== NULL)
  {
   $line = '~';
   $e = preg_split('~\r?\n|\r(?=\n)~',$source);
   for ($i = 0,$s = sizeof($e); $i < $s; $i++)
   {
    if (strpos($e[$i],UNIQUE_HASH.'~~error here~~') !== FALSE) {$line = $i+1; break;}
   }
   return 'Quicky syntax error '.$this->syntax_error.' in template '.$from.' on line '.$line;
  }
  $this->_halt = FALSE;

  $a = array_values($this->postfilters);
  for ($i = 0,$s = sizeof($a); $i < $s; $i++) {$soruce = call_user_func($a[$i],$source,$this);}

  $this->load_plugins = $old_load_plugins;
  $source = preg_replace('~^(<\?php.*?)\?><\?php~si','$1',$header.$source);
  return $source;
 }
 function _compile_source($path) {return $this->_compile_source_string(file_get_contents($path),substr($path,strlen($this->parent->template_dir)));}
 function string_or_expr($s)
 {
  if (ctype_digit(substr($s,0,1) == '-'?substr($s,1):$s)) {return $s;}
  if (preg_match('~^\w+$~',$s))
  {
   if (defined($s) or in_array(strtolower($s),$this->magic_constants) or isset($this->_block_props[strtolower($s)]))
   {
    return $s;
   }
   return '\''.$s.'\'';
  }
  return $s;
 }
 function _parse_params($p,$plain = FALSE)
 {
  $params = array();
  preg_match_all('~\w+\s*=|(([\'"]).*?(?<!\\\\)\2|\w*\s*\(((?:(?R)|.)*?)\)'
    .'|_?[\$#]\w+#?(?:\\[(?:(?R)|((?:[^\\]\'"]*(?:([\'"]).*?(?<!\\\\)\5)?)*))*?\\]|\.[\$#]?\w+#?|->\s*[\$#]?\w+(?:\(((?:(?R)|.)*?)\))?)*'
    .'|-?\d+|(?<=^|[\s\)\:\.=+\-<>])(?:\w+)(?=$|[\s\|\.\:\(=+\-<>]))~s',$p,$m,PREG_SET_ORDER);
  $lastkey = '';
  foreach ($m as $v)
  {
   if (trim($v[0]) === '') {continue;}
   if (sizeof($v) == 1) {$lastkey = ltrim(rtrim($v[0]," =\t")); continue;}
   if ($lastkey === '') {$params[] = $plain?$v[0]:$this->_expr_token($this->string_or_expr($v[0]));}
   else {$params[$lastkey] = $plain?$v[0]:$this->_expr_token($this->string_or_expr($v[0]));}
   $lastkey = '';
  }
  return $params;
 }
 function _dequote($string)
 {
  $a = substr($string,0,1);
  $string = preg_replace('~^\s*([\'"])(.*)\1\s*$~','$2',$string);
  return preg_replace('~(?<!\\\\)\\\\'.preg_quote($a,'~').'~',$a,$string);
 }
 function _get_expr_blockprop($name,$blocktype,$prop)
 {
  $blocktype = strtolower($blocktype);
  $prop = strtolower($prop);
  $a = '$'.$blocktype.'['.var_export($name,TRUE).']';
  if ($blocktype == 'foreach')
  {
	   if ($prop == 'iteration' or $prop == 'i') {$prop = 'i';}
   elseif ($prop == 'total') {$prop = 's';}
   elseif ($prop == 'first') {return '('.$a.'[\'i\'] == 1)';}
   elseif ($prop == 'last') {return '('.$a.'[\'i\'] == '.$a.'[\'s\'])';}
  }
  elseif ($blocktype == 'section')
  {
	   if ($prop == 'iteration' or $prop == 'rownum') {return '('.$a.'[\'i\']+1)';}
   elseif ($prop == 'index' or $prop == $name) {return 'Quicky::ind('.$a.')';}
   elseif ($prop == 'index_prev' or $prop == $name) {return 'Quicky::ind('.$a.',-1)';}
   elseif ($prop == 'index_next' or $prop == $name) {return 'Quicky::ind('.$a.',1)';}
   elseif ($prop == 'total') {$prop = 's';}
   elseif ($prop == 'first') {return '('.$a.'[\'i\'] == 0)';}
   elseif ($prop == 'last') {return '('.$a.'[\'i\']+1 == '.$a.'[\'s\'])';}
  }
  elseif ($blocktype == 'form')
  {
   if ($prop == 'form') {return 'Quicky_form::$forms['.var_export($name,TRUE).']';}
  }
  return $a.'['.var_export($prop,TRUE).']';
 }
 function _joincalc_callback($m)
 {
  if (isset($this->_tmp[$m[0]])) {return 'document.getElementById(\''.$this->_tmp[$m[0]].'\').value';}
  else {return $m[0];}
 }
 function _optimize_callback($m)
 {
  $prefix = ' '.$this->_write_out_to !== ''?$this->_write_out_to.' .= ':'echo ';
  if (isset($m[1]) and $m[1] !== '') {$return = $prefix.var_export($m[1],TRUE).';';}
  elseif (isset($m[2]) and $m[2] !== '') {$return = $prefix.var_export($m[2],TRUE).';';}
  elseif (isset($m[3]) and $m[3] !== '') {$return = $prefix.var_export($m[3],TRUE).';';}
  else {$return = '';}
  return $return;
 }
 function _optimize($block)
 {
  $block = preg_replace_callback((!preg_match('~<\?php|\?>~i',$block))?'~(.*)~s':'~\?>(.*?)<\?php|^(.*?)<\?php|\?>(.*?)$~si',array($this,'_optimize_callback'),$block);
  return $block;
 }
 function _fetch_expr($expr)
 {
  $var = &$this->_cpl_vars;
  $config = &$this->_cpl_config;
  return @eval('return '.$expr.';');
 }
 function _tag_token($mixed,$block_parent = '')
 {
  if (!isset($this->_tag_stacks[$this->_tag_stack_n])) {$this->_tag_stacks[$this->_tag_stack_n] = array();}
  if ($this->_halt && !(is_array($mixed) && isset($mixed[4]) && strtolower($mixed[4]) == 'resumecompiler'))
  {
   return is_array($mixed)?$mixed[0]:$mixed;
  }
  if (is_array($mixed))
  {
   if ((isset($mixed[6]) && $mixed[6] !== '') || (isset($mixed[10]) && $mixed[10] !== ''))
   {
    $a = array(
     0 => $mixed[0],
     1 => $mixed[6]
    );
    if (isset($mixed[7])) {$a[2] = $mixed[7];}
    if (isset($mixed[9])) {$a[3] = $mixed[9];}
    if (isset($mixed[10])) {$a[4] = $mixed[10];}
    $mixed = $a;
    $this->_alt_tag = FALSE;
   }
   else {$this->_alt_tag = TRUE;}
   if (isset($mixed[4]) && $mixed[4] !== '')
   {
    preg_match('~^\s*(\S+)(.*)~s',$mixed[4],$m);
    if (!isset($m[0])) {$m[0] = '';}
    if (!isset($m[1])) {$m[1] = '';}
    $tag = strtolower($m[1]);
    if ($tag == 'if' or $tag == 'elseif' or $tag == 'else if')
    {
     if (trim($m[2]) == '') {return $this->_syntax_error('Empty condition.');}
     return '<?php '.$tag.' ('.$this->_expr_token($m[2],FALSE,TRUE).'): ?>';
    }
    elseif ($tag == 'case')
    {
     if (trim($m[2]) == '') {return $this->_syntax_error('Empty condition.');}
     return '<?php '.$tag.' '.$this->_expr_token($m[2],FALSE,TRUE).': ?>';
    }
    elseif ($tag == 'else' or $tag == 'default') {return '<?php '.$tag.': ?>';}
    elseif ($tag == '/if') {return '<?php endif; ?>';}
    elseif ($tag == 'return' or $tag == 'halt') {if ($tag == 'halt') {$this->_halt = TRUE;} $code = $this->_expr_token($m[2]); return '<?php return'.($code !== ''?' '.$code:'').'; ?>';}
    elseif ($tag == 'haltcompiler') {$this->_halt = TRUE;}
    elseif ($tag == 'resumecompiler') {$this->_halt = FALSE;}
    elseif ($tag == 'break') {$code = $this->_expr_token($m[2]); return '<?php break'.($code !== ''?' '.$code:'').'; ?>';}
    elseif ($tag == 'continue') {return '<?php continue; ?>';}
    elseif (preg_match('~^\w+$~',$tag) && ($p = $this->parent->fetch_plugin('compiler.'.$tag)))
    {
     require_once $p;
     $params = $this->_parse_params($m[2]);
     $a = 'quicky_compiler_'.$tag;
     $return = $a($params,$this);
     return $return;
    }
    elseif (preg_match('~^\w+$~',$tag)  && (($c = in_array($tag,$this->template_defined_functions)) || ($p = $this->parent->fetch_plugin('function.'.$tag))))
    {
     $params = $this->_parse_params($m[2]);
     $key_params = array();
     foreach ($params as $k => $v) {$key_params[] = var_export($k,TRUE).' => '.$v;}
     if (!in_array($p,$this->load_plugins) and !$c) {$this->load_plugins[] = $p;}
     return '<?php '.($this->_write_out_to !== ''?$this->_write_out_to.' .=':'echo').' quicky_function_'.$tag.'(array('.implode(',',$key_params).'),$this,TRUE);'."\n".'?>';
    }
    elseif ($tag == 'foreachelse')
    {
     $this->_tag_stacks[$this->_tag_stack_n][] = $tag;
     return '<?php endforeach; else: ?>';
    }
    elseif ($tag == 'sectionelse')
    {
     $this->_tag_stacks[$this->_tag_stack_n][] = $tag;
     return '<?php endfor; else: ?>';
    }
    elseif ($tag == 'optgroup')
    {
     $params = $this->_parse_params($m[2]);
     $params['text'] = isset($params['text'])?$params['text']:'';
     $params['text'] = strpos($params['text'],'$') !== FALSE?'<?php echo htmlspecialchars('.$params['text'].',ENT_QUOTES); ?>':htmlspecialchars($this->_dequote($params['text']));
     return '<optgroup label="'.$params['text'].'">';
    }
    elseif ($tag == '/optgroup') {return '</optgroup>';}
    elseif ($tag == 'option')
    {
     $tk = -1;
     for ($i = sizeof($this->_tag_stacks)-1; $i >= 0; $i--)
     {
      if (isset($this->_tag_stacks[$i]['type']) && $this->_tag_stacks[$i]['type'] == 'select') {$tk = $i;}
     }
     if ($tk != -1)
     {
      $params = $this->_parse_params($m[2]);
      $s = '';
      foreach ($params as $k => $v)
      {
       if ($k == 'text') {continue;}
       if ($k == 'checked') {$s .= '<?php if ('.$v.($params['type'] == '\'radio\''?' == '.$params['value'].(is_int(array_search('\'default\'',$params))?' or '.$v.' == \'\'':''):'').') {echo \' checked\';} ?>';}
       elseif (strpos($v,'$') !== FALSE) {$s .= ' '.$k.'="<?php echo htmlspecialchars('.$v.',ENT_QUOTES); ?>"';}
       else {$s .= ' '.$k.'="'.htmlspecialchars($this->_dequote($v,ENT_QUOTES)).'"';}
      }
      $params['text'] = isset($params['text'])?$params['text']:'';
      if (!isset($params['value'])) {$params['value'] = $params['text'];}
      $params['text'] = strpos($params['text'],'$') !== FALSE?'<?php echo htmlspecialchars('.$params['text'].',ENT_QUOTES); ?>':htmlspecialchars($this->_dequote($params['text']));
      if ($this->_tag_stacks[$tk]['value'] !== '' and isset($params['value']))
      {
       $s .= '<?php if ('.$this->_tag_stacks[$tk]['value'].' == '.$params['value'].') {echo \' selected\';} ?>';
      }
      return '<option'.$s.'>'.$params['text'].'</option>';
     }
     else {return $this->_syntax_error('Unexcepted tag \'option\'');}
    }
    elseif (preg_match('~^'.preg_quote($this->left_delimiter,'~').'\\*.*\\*'.preg_quote($this->right_delimiter,'~').'$~s',$mixed[0])) {return '';}
    else
    {
     if ($this->_alt_tag and preg_match('~^\w+$~',trim($m[0])))
     {
      $m[0] = '$'.trim($m[0]);
     }
     $outoff = FALSE;
     $plain = FALSE;
     if (substr($m[0],0,1) == '_')
     {
      $m[0] = substr($m[0],1);
      if (substr($m[0],0,1) == '_') {$m[0] = substr($m[0],1);}
      if (substr($m[0],0,1) == '?') {$m[0] = substr($m[0],1); $outoff = TRUE;}
      $e = $this->_fetch_expr($this->_expr_token($m[0],FALSE,TRUE));
      $plain = TRUE;
     }
     else
     {
      if (substr($m[0],0,1) == '?')
      {
       $e = $this->_expr_token(substr($m[0],1),FALSE,TRUE);
       $outoff = TRUE;
      }
      else {$e = $this->_expr_token($m[0],FALSE,TRUE);}
     }
     if ($plain) {return $e;}
     if ($e === '' or $e === '\'\'') {return '';}
     return '<?php '.($outoff?'':($this->_write_out_to !== ''?$this->_write_out_to.' .=':'echo').' ').$e.'; ?>';
    }
    return;
   }
   ++$this->_tag_stack_n;
   $block_name = strtolower($mixed[1]);
   $block_content = $mixed[3];
   $p = FALSE;
   if ($block_name == 'foreach')
   {
    $block_params = $this->_parse_params($mixed[2]);
    if (!isset($block_params['from'])) {return $this->_syntax_error('Missing parameter \'from\' in foreach tag.');}
    if (!isset($block_params['item']) && !isset($block_params['value'])) {return $this->_syntax_error('Missing parameter \'item\' in foreach tag.');}
    $return = '<?php $_from = '.$block_params['from'].';'."\n";
    if (isset($block_params['name']) and ($name = trim($this->_dequote($block_params['name']))) !== '')
    {
     $props = array('first','last','index','iteration','i','total');
     $a = $this->block_props;
     $this->push_block_props($props,$block_name,$name);
     $block = $this->_tag_token($block_content,$block_name);
     $this->block_props = $a;

     $name = var_export($name,TRUE);
     $return .=  "\n".'$foreach['.$name.'] = array();'
				."\n".'$foreach['.$name.'][\'i\'] = 0;'
				."\n".'$foreach['.$name.'][\'s\'] = sizeof($_from);'
	 ."\n";
    }
    else {$return .= ' '; $name = ''; $block = $this->_tag_token($block_content,$block_name);}
    $return .= 'if ('.($name !== ''?'$foreach['.$name.'][\'show\'] = ':'').($name !== ''?'$foreach['.$name.'][\'s\']':'sizeof($_from)').' > 0):'
    .' foreach ($_from as'.(isset($block_params['key'])?' $var['.$block_params['key'].'] =>':'').' $var['.(isset($block_params['item'])?$block_params['item']:$block_params['value']).']): '
	.($name !== ''?"\n".'++$foreach['.$name.'][\'i\'];'.'':'')
    .'?>'
    .$block;
    if (($k = array_search('foreachelse',$this->_tag_stacks[$this->_tag_stack_n])) !== FALSE)
    {
     $return .= '<?php endif; ?>';
     unset($this->_tag_stacks[$this->_tag_stack_n][$k]);
    }
    else {$return .= '<?php endforeach; endif; ?>';}
   }
   elseif ($block_name == 'joincalculator')
   {
    $block_params = $this->_parse_params($mixed[2]);
    if (!isset($block_params['name'])) {return $this->_syntax_error('Missing parameter \'name\' in joincalculator tag.');}
    if (!isset($block_params['fields'])) {return $this->_syntax_error('Missing parameter \'fields\' in joincalculator tag.');}
    $block_params['name'] = strpos($block_params['name'],'$') !== FALSE?'<?php echo '.$block_params['name'].'; ?>':$this->_dequote($block_params['name']);
    $block_params['fields'] = explode(',',$this->_dequote($block_params['fields']));
    $fields = array();
    $return = '<script type="text/javascript">
function calculator_'.$block_params['name'].'(field)
{
';
    foreach ($block_params['fields'] as $k => $v)
    {
     if (preg_match('~(.*?)\s+as\s+(.*)~i',$v,$q))
     {
      if (isset($fields[$q[2]])) {return $this->_syntax_error('Field name \''.$q[2].'\' alredy in use ');}
      $fields[$q[2]] = $q[1];
      $fields[$q[1]] = $q[1];
     }
     else {$fields[$v] = $v;}
    }
    $f = '';
    foreach ($fields as $k => $v) {$f .= '|'.preg_quote($k,'~');}

    if ($f === '') {return $this->_syntax_error('No fields');}

    $this->_tmp = $fields;
    preg_match_all('~\s*(.*?)\s*=\s*(.*?)(?:[\r\n]+|$)~m',$block_content,$m,PREG_SET_ORDER);
    foreach ($m as $v)
    {
     $name = $v[1];
     $expr = $v[2];
     $left = preg_replace_callback($a = '~([\'"]).*?\1'.$f.'~',array($this,'_joincalc_callback'),$name);
     $right = preg_replace_callback('~([\'"]).*?\1'.$f.'~',array($this,'_joincalc_callback'),$expr);
     $return .= 'if (field != \''.$name.'\') '.$left.' = '.$right.';
';
    }
    $this->_tmp = array();
    $return .= '}';
    foreach ($fields as $k => $v)
    {
     if ($k != $v) {continue;}
     $return .= '
document.getElementById(\''.$v.'\').onchange = function() {setTimeout(function() {calculator_'.$block_params['name'].'("'.$v.'");},50);}';
     if (is_int(array_search('\'onkeydown\'',$block_params)))
     {
      $return .= '
document.getElementById(\''.$v.'\').onkeydown = function() {setTimeout(function() {calculator_'.$block_params['name'].'("'.$v.'");},50);}';
     }
    }
    $return .= '
</script>';
   }
   elseif ($block_name == 'for')
   {
    $block_params = $this->_parse_params($mixed[2]);
    if (!isset($block_params['start'])) {$block_params['start'] = 0;}
    if (!isset($block_params['step'])) {$block_params['step'] = 1;}
    if (!isset($block_params['loop'])) {return $this->_syntax_error('Missing parameter \'loop\' in for tag.');}
    if (!isset($block_params['value'])) {return $this->_syntax_error('Missing parameter \'value\' in for tag.');}
    $return = '<?php'
			  ."\n".'for ('.$block_params['value'].' = '.$block_params['start'].'; '.$block_params['value'].' < '.$block_params['loop'].'; '.$block_params['value'].' += '.$block_params['step'].'): ?>'
			  .$this->_tag_token($block_content,$block_name)
			  .'<?php endfor; ?>';
   }
   elseif ($block_name == 'form')
   {
    $block_params = $this->_parse_params($mixed[2]);
    if (!isset($block_params['name']) && !isset($block_params['no_quicky_form']) && !is_int(array_search('no_quicky_form',$block_params))) {return $this->_syntax_error('Missing parameter \'name\' in section tag.');}
    $mode = !isset($block_params['no_quicky_form']) and !is_int(array_search('no_quicky_form',$block_params));
    $auto_object = isset($block_params['auto_object']);
    unset($block_params['auto_object']);
    $code = '';
    if ($mode)
    {
     if (!class_exists('Quicky_form'))
     {
      if ($auto_object) {require_once QUICKY_DIR.'Quicky.form.class.php';}
      else {return $this->_syntax_error('Class Quicky_form isn\'t loaded');}
     }
     $name = $this->_dequote($block_params['name']);
     if (!isset(Quicky_form::$forms[$name]))
     {
      if ($auto_object)
      {
       $code = '<?php'."\n".'if (!class_exists(\'Quicky_form\')) {require_once QUICKY_DIR.\'Quicky.form.class.php\';}'."\n".'if (!isset(Quicky_form::$forms[\''.$name.'\'])) {$form = new Quicky_form(\''.$name.'\');'."\n";
       preg_match_all('~'.preg_quote($this->left_delimiter,'~').'(input|textarea)(\s+.*?)?'.preg_quote($this->right_delimiter,'~').'~',$block_content,$m,PREG_SET_ORDER);
       foreach ($m as $v)
       {
        $params = $this->_parse_params($v[2],TRUE);
        if (!isset($params['name'])) {continue;}
        foreach ($params as $k => $v) {$params[$k] = $this->_dequote($v);}
        $code .= '$form->addElement('.var_export($params['name'],TRUE).','.var_export($params,TRUE).');'."\n";
       }
       $code .= '} ?>';
       eval('?>'.$code);
      }
      else {return $this->_syntax_error('Form \''.$name.'\' is not recognized');}
     }
     $objform = Quicky_form::$forms[$name];
     $props = array('form');
     $a = $this->block_props;
     $this->push_block_props($props,$block_name,$name);
     $block_content = $this->_tag_token($block_content,$block_name);
     $this->block_props = $a;
    }
    else {$block_content = $this->_tag_token($block_content,$block_name);}
    $s = '';
    foreach ($block_params as $k => $v)
    {
     if (strpos($v,'$') !== FALSE) {$s .= ' '.$k.'="<?php echo htmlspecialchars('.$v.',ENT_QUOTES); ?>"';}
     else {$s .= ' '.$k.'="'.htmlspecialchars($this->_dequote($v,ENT_QUOTES)).'"';}
    }
    $return = $code.'<form'.$s.'>'.$block_content.'</form>';
   }
   elseif ($block_name == '_if')
   {
    if (trim($mixed[2]) === '') {return $this->_syntax_error('Empty condition.');}
    $return = $this->_fetch_expr($this->_expr_token($mixed[2],FALSE,TRUE))?$this->_tag_token($block_content,$block_name):'';
   }
   elseif ($block_name == '_foreach')
   {
    $block_params = $this->_parse_params($mixed[2]);
    if (!isset($block_params['from'])) {return $this->_syntax_error('Missing parameter \'from\' in foreach tag.');}
    if (!isset($block_params['item']) && !isset($block_params['value'])) {return $this->_syntax_error('Missing parameter \'item\' in foreach tag.');}
    if (!isset($block_params['item'])) {$block_params['item'] = $block_params['value'];}
    $val = $this->_fetch_expr($block_params['from']);
    if (!is_array($val) and !is_object($val)) {return $this->_syntax_error('Parameter \'from\' must be an array or object, '.gettype($val).' given');}
    $return = '';
    if (isset($block_params['key']))
    {
     foreach ($val as $this->_cpl_vars[$this->_dequote($block_params['key'])] => $this->_cpl_vars[$this->_dequote($block_params['item'])]) {$return .= $this->_tag_token($block_content,$block_name);}
    }
    else
    {
     foreach ($val as $this->_cpl_vars[$this->_dequote($block_params['item'])]) {$return .= $this->_tag_token($block_content,$block_name);}
    }
   }
   elseif ($block_name == 'function' or $block_name == 'helper')
   {
    if (!preg_match('~^\s*(.*?)\s*\((.*)\)\s*$~',$mixed[2],$g)) {return $this->_syntax_error('Parse error in Function header.');}
    if (trim($g[2]) === '') {return $this->_syntax_error('Missing expression in Function tag.');}
    $this->_tag_stacks[$this->_tag_stack_n]['type'] = 'function';
    $this->_tag_stacks[$this->_tag_stack_n]['name'] = $g[1];
    $args = explode(',',$g[2]);
    $args_a = array();
    foreach ($args as $v)
    {
     if (!preg_match('~^\$(\w+)$~',$v,$q)) {return $this->_syntax_error('Parse error in Function header.');}
     $args_names[] = $q[1];
    }
    $args_s = '';
    $args_f = '';
    foreach ($args_names as $k => $v)
    {
     $args_f .= 'isset($args[\''.$v.'\'])?$args[\''.$v.'\']:NULL,';
     $args_s .= '$args[\''.$v.'\'],';
    }
    $args_s = rtrim($args_s,',');
    $args_f = rtrim($args_f,',');
    $return = '<?php'
			  ."\n".'function quicky_function_'.$g[1].' ($args,$quicky)'
			  ."\n".'{'
			  ."\n".'$var = &$quicky->_tpl_vars;'
			  ."\n".'$save_vars = array('.$args_f.');';
    foreach ($args as $k => $v)
    {
     $return .= "\n".'if (isset($args[\''.$args_names[$k].'\'])) {$var[\''.$args_names[$k].'\'] = $args[\''.$args_names[$k].'\'];}'.
				"\n".'elseif (isset($args['.$k.'])) {$var[\''.$args_names[$k].'\'] = $args['.$k.'];}';
	  }
	  $return  .= 
			  "\n".'$config = &$quicky->_tpl_config;'
			  ."\n".'$capture = &$quicky->_block_props[\'capture\'];'
			  ."\n".'$foreach = &$quicky->_block_props[\'foreach\'];'
			  ."\n".'$section = &$quicky->_block_props[\'section\'];'
			  ."\n?>";
    $this->template_defined_functions[] = $g[1];
    $return .= $this->_tag_token($block_content,$block_name)
			  ."\n".'<?php list('.$args_s.') = $save_vars;'
			  ."\n".'} ?>';
   }
   elseif ($block_name == 'while')
   {
    $expr = $this->_expr_token($mixed[2]);
    if (trim($expr) === '') {return $this->_syntax_error('Missing expression in while tag.');}
    $return = '<?php'
			  ."\n".'while ('.$expr.'): ?>'
			  .$this->_tag_token($block_content,$block_name)
			  .'<?php endwhile; ?>';
   }
   elseif ($block_name == 'switch')
   {
    $expr = $this->_expr_token($mixed[2]);
    if (trim($expr) === '') {return $this->_syntax_error('Missing expression in switch tag.');}
    $block = $this->_tag_token($block_content,$block_name);
    $block = ltrim($block);
    $return = '<?php'
			  ."\n".'switch ('.$expr.'): ?>'
			  .$block
			  .'<?php endswitch; ?>';
   }
   elseif ($block_name == 'section')
   {
    $block_params = $this->_parse_params($mixed[2]);
    if (!isset($block_params['name'])) {return $this->_syntax_error('Missing parameter \'name\' in section tag.');}
    if (!isset($block_params['loop'])) {return $this->_syntax_error('Missing parameter \'loop\' in section tag.');}
    else {$block_params['loop'] = $this->_dequote($block_params['loop']);}
    if (!isset($block_params['start'])) {$block_params['start'] = '0';}
    else {$block_params['start'] = $this->_dequote($block_params['start']);}
    if (!isset($block_params['max'])) {$block_params['max'] = '-1';}
    else {$block_params['max'] = $this->_dequote($block_params['max']);}
     if (!isset($block_params['step'])) {$block_params['step'] = '1';}
    else {$block_params['step'] = $this->_dequote($block_params['step']);}
    $name = $this->_dequote($block_params['name']);
    $props = array('index','index_prev','index_next','iteration','first','last','rownum','loop','show','total');
    if (in_array($name,$props)) {return $this->_syntax_error('Disallowed value (\''.$block_params['name'].'\') of parameter \'name\' in section tag.');}
    $props[] = $name;
    $a = $this->block_props;
    $this->push_block_props($props,$block_name,$name);
    $block = $this->_tag_token($block_content,$block_name);
    $this->block_props = $a;
    $name = var_export($name,TRUE);
    $return =  '<?php'
				."\n".'$section['.$name.'] = array();'
			    ."\n".'$section['.$name.'][\'s\'] = '.(isInteger($block_params['loop'])?$block_params['loop']:'isInteger('.$block_params['loop'].')?'.$block_params['loop'].':sizeof('.$block_params['loop'].')').';'
			    ."\n".'$section['.$name.'][\'st\'] = '.(isInteger($block_params['start'])?$block_params['start']:'isInteger('.$block_params['start'].')?'.$block_params['start'].':sizeof('.$block_params['start'].')').';'
			    ."\n".'$section['.$name.'][\'step\'] = '.(isInteger($block_params['step'])?$block_params['step']:'isInteger('.$block_params['step'].')?'.$block_params['step'].':sizeof('.$block_params['step'].')').';'
				."\n".'if ($section['.$name.'][\'s\'] > 0):'
				.' for ($section['.$name.'][\'i\'] = 0; $section['.$name.'][\'i\'] < $section['.$name.'][\'s\']-$section['.$name.'][\'st\']'.($block_params['max'] != '-1'?' and $section['.$name.'][\'i\'] < '.$block_params['max']:'').'; ++$section['.$name.'][\'i\']): '
				.'?>'.$block;
    if (($k = array_search('sectionelse',$this->_tag_stacks[$this->_tag_stack_n])) !== FALSE)
    {
     $return .= '<?php endif; ?>';
     unset($this->_tag_stacks[$this->_tag_stack_n][$k]);
    }
    else {$return .= '<?php endfor; endif; ?>';}
   }
   elseif ($block_name == 'literal') {$return = $block_content;}
   elseif ($block_name == 'strip')
   {
    $block_content = preg_replace('~[\t ]*[\r\n]+[\t ]*~','',$block_content);
    $return = $this->_tag_token($block_content,$block_name);
   }
   elseif ($block_name == 'php')
   {
    if (!isset($this->prefs['allow_php_native'])) {return $this->_syntax_error('Disallowed PHP-tag');}
    $return = '<?php '.$block_content.' ?>';
   }
   elseif ($block_name == 'capture')
   {
    $block_params = $this->_parse_params($mixed[2]);
    $name = isset($block_params['name'])?trim($this->_dequote($block_params['name'])):'default';
    $assign = isset($block_params['assign'])?trim($this->_dequote($block_params['assign'])):'';
    if (is_int(array_search('ob',$block_params))) {$return = '<?php ob_start(); ?>'.$this->_tag_token($block_content,$block_name).'<?php $capture[\''.$name.'\'] = ob_get_contents(); ob_end_clean();'.($assign !== ''?' $var[\''.$assign.'\'] = $capture[\''.$name.'\'];':'').' ?>';}
    else
    {
     $old_write_out_to = $this->_write_out_to;
     $this->_write_out_to = '$capture[\''.$name.'\']';
     $block = $this->_tag_token($block_content,$block_name);
     $block = $this->_optimize($block);
     $return = '<?php $capture[\''.$name.'\'] = \'\';'."\n".$block
     .($assign !== ''?'$var[\''.$assign.'\'] = $capture[\''.$name.'\'];':'').' ?>';
     $this->_write_out_to = $old_write_out_to;
    }
   }
   elseif ($block_name == 'dynamic')
   {
    $return = '<?php /*'.UNIQUE_HASH_STATIC.'{dynamic}*/ ?>'.$this->_tag_token($block_content,$block_name).'<?php /*{/dynamic}'.UNIQUE_HASH_STATIC.'*/ ?>';
   }
   elseif (preg_match('~^[\w+\-_]+$~',$block_name) && ((function_exists('quicky_block_'.$block_name) || ($p = $this->parent->fetch_plugin('block.'.$block_name)))))
   {
    $block_params = $this->_parse_params($mixed[2]);
    if ($p) {require_once $p;}
    $a = 'quicky_block_'.$block_name;
    return $a($block_params,$block_content,$this);
   }
   elseif ($block_name == 'select')
   {
    $block_params = $this->_parse_params($mixed[2]);
    $this->_tag_stacks[$this->_tag_stack_n]['type'] = $block_name;
    $this->_tag_stacks[$this->_tag_stack_n]['value'] = isset($block_params['value'])?$block_params['value']:'';
    unset($block_params['value']);
    if (isset($block_params['join']))
    {
     $fieldname = $this->_dequote($block_params['join']);
     if (!isset($this->block_props['form'])) {return $this->_syntax_error('Parameter \'join\' in tag select must be into a form.');}
     $form = Quicky_form::$forms[$this->block_props['form'][0]];
     if (!isset($form->elements->$fieldname)) {return $this->_syntax_error('There are no field \''.$fieldname.'\' in form \''.$form->name.'\'');}
     unset($block_params['join']);
     foreach ($form->elements->$fieldname as $k => $v)
     {
      if ($k == 'elements') {continue;}
      if (substr($k,0,1) != '_' and !isset($params[$k])) {$block_params[$k] = var_export($v,TRUE);}
     }
     foreach ($form->elements->$fieldname->elements as $k => $v)
     {
      $t = gettype($v);
      if ($t == 'string') {$block_content .= '{option text=\''.$this->escape_string($v).'\'}'."\n";}
      elseif ($t == 'array' or $t == 'object')
      {
       if ($t == 'array') {$v = (object) $v;}
       $type = isset($v->type)?$v->type:'option';
       unset($v->type);
       if ($type != 'option' and $type != 'optgroup') {return $this->_syntax_error('Unexcepted type of dropdown\'s child element: \''.$type.'\'');}
       $s = '';
       foreach ($v as $h => $b)
       {
        $s .= $h.'=\''.$this->escape_string($b).'\' ';
       }
       $block_content .= '{'.$type.' '.rtrim($s,' ').'}'."\n";
      }
     }
    }
    $s = '';
    foreach ($block_params as $k => $v)
    {
     if ($k == 'type') {continue;}
     if (strpos($v,'$') !== FALSE) {$s .= ' '.$k.'="<?php echo htmlspecialchars('.$v.',ENT_QUOTES); ?>"';}
     else {$s .= ' '.$k.'="'.htmlspecialchars($this->_dequote($v,ENT_QUOTES)).'"';}
    }
    $return = '<select'.$s.'>'.$this->_tag_token($block_content,$block_name).'</select>';
   }
   elseif ($block_name == 'begin')
   {
    $name = trim($mixed[2]);
    $this->_tag_stacks[$this->_tag_stack_n]['type'] = 'begin';
    $this->_tag_stacks[$this->_tag_stack_n]['name'] = $name;
    $fullpath = '/';
    $tk = 0;
    for ($i = sizeof($this->_tag_stacks)-1; $i >= 0; $i--)
    {
     if (isset($this->_tag_stacks[$i]['type']) && $this->_tag_stacks[$i]['type'] == 'begin') {$fullpath = '/'.$this->_tag_stacks[$i]['name'].$fullpath; ++$tk;}
    }
    $s_name = var_export($name,TRUE);
    $s_fullpath = var_export($fullpath,TRUE);
    $sf_name = 'quicky_context_'.$name;
    $old_write_out_to = $this->_write_out_to;
    $this->_write_out_to = '$return';
    $block = $this->_tag_token($block_content,$block_name);
    $block = $this->_optimize($block);
    $this->_write_out_to = $old_write_out_to;
    $return = '<?php'
							."\n".'if (!function_exists(\''.$sf_name.'\')) {function '.$sf_name.' () {$var = &Quicky::$obj->_tpl_vars; $return = \'\';'
							."\n".'if (isset(Quicky::$obj->_contexts_data['.$s_fullpath.']) and sizeof(Quicky::$obj->_contexts_data['.$s_fullpath.']) > 0) {'
							."\n".'$old = $var;'
							."\n".'foreach (Quicky::$obj->_contexts_data['.$s_fullpath.'] as $k => $v):'
							."\n".'$var = array_merge($var,$v);'
							.$block
							.'endforeach; $var = $old;} return $return; }} '.($tk > 1?'$return .=':'echo').' '.$sf_name.'(); ?>';
   }
   else {return $this->_syntax_error('Unrecognized block-type \''.$block_name.'\'');}
   unset($this->_tag_stacks[$this->_tag_stack_n]);
   --$this->_tag_stack_n;
   return $return;
  }
  $blocks = array_values($this->blocks + $this->parent->_blocks);
  for ($i = 0,$s = sizeof($blocks); $i < $s; $i++) {$blocks[$i] = preg_quote($blocks[$i],'~');}
  $blocks[] = 'foreach';
  $blocks[] = 'section';
  $blocks[] = 'for';
  $blocks[] = 'while';
  $blocks[] = 'switch';
  $blocks[] = 'literal';
  $blocks[] = 'capture';
  $blocks[] = 'php';
  $blocks[] = 'strip';
  $blocks[] = 'textformat';
  $blocks[] = 'dynamic';
  $blocks[] = 'select';
  $blocks[] = 'joincalculator';
  $blocks[] = 'function|helper';
  $blocks[] = 'form';
  $blocks[] = '_if|_foreach';
  $ldelim = preg_quote($this->left_delimiter,'~');
  $rdelim = preg_quote($this->right_delimiter,'~');
  $return = preg_replace_callback('~'
								.'\{\{?\s*(begin)(?:\s+(.*?))?\}\}?((?:(?R)|.)*?)\{\{?\s*(?:end(?:\s+\2)?)?\s*\}\}?'
								.'|\{\{(\\??(?:[^'.$rdelim.'\'"]*([\'"]).*?(?<!\\\\)\5)*.*?)\}\}'
								.'|'.$ldelim.'\s*('.implode('|',$blocks).')(\s(?:[^'.$rdelim.'\'"]*([\'"]).*?(?<!\\\\)\8)*.*?)?'.$rdelim.'((?:(?R)|.)*?)'.$ldelim.'/\s*\6?\s*'.$rdelim
								.'|'.$ldelim.'(\\??(?:[^'.$rdelim.'\'"]*([\'"]).*?(?<!\\\\)\11)*.*?)'.$rdelim
								.'~si',array($this,'_tag_token'),$mixed);								
  return $return;
 }
 function _var_token($token)
 {
  preg_match_all($a = '~([\'"]).*?(?<!\\\\)\1|\(((?:(?R)|.)*?)\)|->((?:_?[\$#]?\w*(?:\(((?:(?R)|.)*?)\)|(\\[((?:(?R)|(?:[^\\]\'"]*([\'"]).*?(?<!\\\\)\4)*.*?))*?\\]|\.[\$#]?\w+#?|(?!a)a->\w*(?:\(((?:(?R)|.)*?)\))?)?)?)+)~',$token,$properties,PREG_SET_ORDER);
  $token = preg_replace_callback($a,create_function('$m','if (!isset($m[3])) {return $m[0];} return \'\';'),$token);
  $obj_appendix = '';
  for ($i = 0,$s = sizeof($properties); $i < $s; $i++)
  {
   if (isset($properties[$i][3]))
   {
    $plain = FALSE;
    preg_match('~^((?:_?[\$#])?\w+#?)(.*)$~',$properties[$i][3],$q);
    if (preg_match('~^_?[\$#]~',$q[1]))
    {
     if (substr($q[1],0,1) == '_') {$plain = TRUE; $q[1] = substr($q[1],1);}
     $q[1] = $this->_var_token($q[1]);
     if ($plain) {$q[1] = $this->_fetch_expr($q[1]);}
    }
    $obj_appendix .= '->'.$q[1];
    preg_match_all('~(\\[((?:(?R)|(?:[^\\]\'"]*([\'"]).*?(?<!\\\\)\3)*.*?))*?\\]|\.[\$#]?\w+#?)~',$q[2],$w,PREG_SET_ORDER);
    for ($j = 0,$n = sizeof($w); $j < $n; $j++)
    {
     if (substr($w[$j][1],0,1) == '.')
     {
      $expr = substr($w[$j][1],1);
      if (!isset($this->block_props[$expr]))
      {
       $expr = '"'.$expr.'"';
       $instring = TRUE;
      }
      else {$instring = FALSE;}
     }
     else {$expr = substr($w[$j][1],1,-1); $instring = FALSE;}
     $r = $this->_expr_token($expr,$instring);
     $obj_appendix .= '['.(preg_match('~^\w+$~',$r)?'\''.$r.'\'':$r).']';
    }
    if (isset($properties[$i][4]) && ($properties[$i][4] !== '' || !isset($properties[$i][5])))
    {
     $params = $this->_expr_token_parse_params($properties[$i][4]);
     $obj_appendix .= '('.implode(',',$params).')';
    }
   }
  }
  if (is_numeric($token)) {return $token;}
  if (substr($token,0,1) == '#' or substr($token,0,1) == '$' or (isset($this->block_props[$token]) and !$this->_no_magic))
  {
   $this->_no_magic = preg_match('~^\$(?:quicky|smarty)[\.\[]~i',$token);
   preg_match_all('~([\$#]?\w*#?)(\\[((?:(?R)|(?:[^\\]\'"]*([\'"]).*?(?<!\\\\)\4)*.*?))*?\\]|\.[\$#]?\w+#?|->\w*(?:\(((?:(?R)|.)*?)\))?)~',$token,$w,PREG_SET_ORDER);
   $appendix_set = array();
   for ($i = 0,$s = sizeof($w); $i < $s; $i++)
   {
    if ($w[$i][1] !== '') {$token = $w[$i][1];}
    if (substr($w[$i][2],0,1) == '.')
    {
     $expr = substr($w[$i][2],1);
     if (!isset($this->block_props[$expr]))
     {
      $expr = '"'.$expr.'"';
      $instring = TRUE;
     }
     else {$instring = FALSE;}
    }
    else {$expr = substr($w[$i][2],1,-1); $instring = FALSE;}
    $r = $this->_expr_token($expr,$instring);
    $appendix_set[] = preg_match('~^\w+$~',$r)?'\''.$r.'\'':$r;
   }
   $this->_no_magic = FALSE;
  }
  static $operators = array('or','xor','and','true','false','null');
  $mode = 0;
  $mode_special_var = FALSE;
  if (substr($token,0,1) == '\'' or substr($token,0,1) == '"')
  {
   if (substr($token,-1) != $token[0]) {return $this->_syntax_error('Bad string definition.');}
   if ($token[0] == '"') {return $this->_expr_token($token,TRUE);}
   return var_export($this->_dequote($token),TRUE);
  }
  elseif ($token == '$tplpath') {return '$path';}
  elseif ($token == '$tplpathdir') {return '$dir';}
  elseif ($token == '$rdelim') {return var_export($this->right_delimiter,TRUE);}
  elseif ($token == '$ldelim') {return var_export($this->left_delimiter,TRUE);}
  elseif ($token == '$SCRIPT_NAME') {return '$_SERVER[\'SCRIPT_NAME\']';}
  elseif ($token[0] == '$')
  {
   $token = substr($token,1);
   if (array_key_exists($token,$this->precompiled_vars)) {return var_export($this->precompiled_vars[$token],TRUE);}
   $type = 'var';
   if (strtolower($token) == 'quicky' || strtolower($token) == 'smarty')
   {
    $t = isset($appendix_set[0])?strtolower($this->_dequote($appendix_set[0])):'';
    $appendix_set = array_slice($appendix_set,1);
    $type = '';
    if ($t == 'rdelim') {return var_export($this->right_delimiter,TRUE);}
    elseif ($t == 'ldelim') {return var_export($this->left_delimiter,TRUE);}

    elseif ($t == 'request') {$type = '_REQUEST'; $mode = 1;}
    elseif ($t == 'tplscope') {$type = 'var'; $mode = 1;}
    elseif ($t == 'cfgscope') {$type = 'config'; $mode = 1;}
    elseif ($t == 'get') {$type = '_GET'; $mode = 1;}
    elseif ($t == 'post') {$type = '_POST'; $mode = 1;}
    elseif ($t == 'cookie' or $t == 'cookies') {$type = '_COOKIE'; $mode = 1;}

    elseif ($t == 'requeststring') {$type = '_REQUEST'; $mode = 2;}
    elseif ($t == 'getstring') {$type = '_GET'; $mode = 2;}
    elseif ($t == 'poststring') {$type = '_POST'; $mode = 2;}
    elseif ($t == 'cookiestring' or $t == 'cookiesstring') {$type = '_COOKIE'; $mode = 2;}

    elseif ($t == 'session') {$type = '_SESSION';}
    elseif ($t == 'server') {$type = '_SERVER';}
    elseif ($t == 'env') {$type = '_ENV';}
    elseif ($t == 'capture') {$type = 'capture';}
    elseif ($t == 'now') {return 'time()';}
    elseif ($t == 'const') {return 'constant('.(isset($appendix_set[0])?$appendix_set[0]:'').')';}
    elseif ($t == 'template') {return '$path';}
    elseif ($t == 'version') {return '$this->version';}
    elseif ($t == 'foreach' or $t == 'section')
    {
     $name = isset($appendix_set[0])?strtolower($this->_dequote($appendix_set[0])):'';
     $prop = isset($appendix_set[1])?strtolower($this->_dequote($appendix_set[1])):'';
     return $this->_get_expr_blockprop($name,$t,$prop);
    }
    else {return $this->_syntax_error('Unknown property \''.$t.'\' of $quicky');}
    $token = '';
   }
  }
  elseif (substr($token,0,1) == '#')
  {
   if (substr($token,-1) != '#') {return var_export($token,TRUE);}
   $type = 'config'; $token = substr($token,1,-1);
  }
  elseif ($token == 'tplpath') {return var_export($this->template_from,TRUE);}
  elseif ($token == 'tplpathdir') {$a = dirname($this->template_from); return var_export($a !== ''?$a:'.',TRUE);}
  elseif ($token == 'rdelim') {return var_export($this->right_delimiter,TRUE);}
  elseif ($token == 'ldelim') {return var_export($this->left_delimiter,TRUE);}
  elseif (isset($this->block_props[$token]) and !$this->_no_magic)
  {
   $return = $this->_get_expr_blockprop($this->block_props[$token][0],$this->block_props[$token][1],$token);
   $mode_special_var = TRUE;
  }
  elseif (isset($this->block_props[$token])) {return $token;}
  elseif (in_array($token,$this->allowed_php_constants) || in_array(strtolower($token),$operators) || (defined($token) && preg_match('~^M_\w+$~',$token))) {return $token;}
  elseif (preg_match('~^\w+$~',$token))
  {
   if ($this->_cast_undefined_token_to_strings) {return var_export($token,TRUE);}
   return $this->_syntax_error('Unexpected constant \''.$token.'\'');
  }
  else {return $this->_syntax_error('Unrecognized token \''.$token.'\'');}
  $appendix = '';
  for ($i = 0,$s = sizeof($appendix_set); $i < $s; $i++) {$appendix .= '['.$appendix_set[$i].']';}
  if ($mode_special_var) {return $return.$appendix.$obj_appendix;}
  $return = '$'.$type.($token !== ''?'['.var_export($token,TRUE).']':'').$appendix.$obj_appendix;
  if ($mode == 2) {$return = 'gpcvar_strnull('.$return.')';}
  return $return;
 }
 function _expr_token_parse_params($expr)
 { // This function without regular expressions just for fun
  $params = array();
  $cpos = 0;
  $instring = FALSE;
  $instring_delim = '';
  $bnl = 0;
  $size = strlen($expr);
  $param = '';
  while ($cpos <= $size)
  {
   if ($cpos == $size) {$params[] = $this->_expr_token($param); break;}
   $char = $expr[$cpos];
   if (!$instring)
   {
    if ($char == '"' or $char == '\'') {$instring = TRUE; $instring_delim = $char;}
    elseif ($char == '(') {$bnl++;}
    elseif ($char == ')') {$bnl--;}
   }
   else
   {
    if ($char == $instring_delim and $expr[$cpos-1] != '\\') {$instring = FALSE;}
   }
   if (!$instring and $bnl == 0 and $char == ',') {$params[] = $this->_expr_token($param); $param = '';}
   else {$param .= $char;}
   $cpos++;
  }
  return $params;
 }
 function _expr_token_callback($m)
 {
  if (isset($m[13]) and $m[13] !== '')
  {
   preg_match('~^(\s*)(.*)(\s*)$~',$m[13],$q);
   $lspace = $q[1];
   $operator = $q[2];
   $rspace = $q[3];
   $operator = trim(preg_replace('~\s+~',' ',strtolower($operator)));
   if ($operator == 'eq' or $operator == 'is') {$code = '==';}
   elseif ($operator == 'ne' || $operator == 'neq') {$code = '!=';}
   elseif ($operator == 'gt') {$code = '>';}
   elseif ($operator == 'lt') {$code = '<';}
   elseif ($operator == 'ge' || $operator == 'gte') {$code = '>=';}
   elseif ($operator == 'le' || $operator == 'lte') {$code = '<=';}
   elseif ($operator == 'not') {$code = '!'; $rspace = '';}
   elseif ($operator == 'mod') {$code = '%';}
   elseif ($operator == 'not eq' or $operator == 'is not') {$code = '!=';}
   else {return $this->_syntax_error('Unknown operator '.var_export($operator,TRUE));}
   return $code;
  }
  elseif (isset($m[3]) and $m[3] !== '' || preg_match('~^(\w+)\s*\(~',$m[1]))
  {
   if (preg_match('~^(\w+)\s*\(~',$m[1],$q)) {$func = $q[1];}
   else {$func = '';}
   $expr = $m[3];
   if (trim($func.$expr) == '') {return;}
   if ($func != '')
   {
    $a = strtolower($func);
    $b = $p = $c = FALSE;
    foreach ($this->allowed_php_tokens as $i)
    {
     if (preg_match($e = '~^'.str_replace('\*','.*',preg_quote($i,'~')).'$~i',$a)) {$b = TRUE; break;}
    }
    if (!$b) {$c = in_array($a,$this->template_defined_functions);}
    if (!$b and !$c)
    {
     $y = $this->_alt_tag && !in_array($a,get_class_methods('Quicky'));
    }
    if (preg_match('~^\w+$~',$a) && ($b || $c || $y || ($p = $this->parent->fetch_plugin('function.'.$a))))
    {
     $params = $this->_expr_token_parse_params($expr);
     if ($p || $c)
     {
      if ($p !== FALSE and !in_array($p,$this->load_plugins)) {$this->load_plugins[] = $p;}
      $return = 'quicky_function_'.$a.'(array('.implode(',',$params).'),$this,TRUE)';
     }
     elseif ($b)
     {
      if ($a == 'count') {$a = 'sizeof';}
      $return = $a.'('.implode(',',$params).')';
     }
     elseif ($y)
     {
      $tk = FALSE;
      $ta = array('begin','function');
      for ($i = sizeof($this->_tag_stacks)-1; $i >= 0; $i--)
      {
       if (isset($this->_tag_stacks[$i]['type']) && in_array($this->_tag_stacks[$i]['type'],$ta)) {$tk = TRUE; break;}
      }
      if ($tk) {$prefix = 'Quicky::$obj->';}
      else {$prefix = '$this->';}
      return $prefix.$a.'('.implode(',',$params).')';
     }
     else {return $this->_syntax_error('Function \''.$func.'\' not available');}
    }
    else {return $this->_syntax_error('Function \''.$func.'\' not available');}
   }
   else {$return = '('.$this->_expr_token($expr).')';}
  }
  elseif (isset($m[1]) and $m[1] !== '')
  {
   $return = $this->_var_token($m[1]);
  }
  else {$return = '';}
  if (isset($m[7]) and $m[7] !== '')
  {
   preg_match('~^(\s*)(.*)(\s*)$~',$m[7],$q);
   $lspace = $q[1];
   $operator = $q[2];
   $rspace = $q[3];
   $operator = trim(preg_replace('~\s+~',' ',strtolower($operator)));
   if ($operator == 'is not odd') {$return = '(('.$return.') % 2 != 0)';}
   if ($operator == 'is not even') {$return = '(('.$return.') % 2 == 0)';}
   elseif ($operator == 'is odd') {$return = '(('.$return.') % 2 == 0)';}
   elseif ($operator == 'is even') {$return = '(('.$return.') % 2 != 0)';}
   elseif (preg_match('~^is( not)? odd by (.*)$~',$operator,$e))
   {
    $return = '(('.$return.' / '.$this->_expr_token($e[2]).') % 2 '.($e[1] != ''?'!':'=').'= 0)';
   }
   elseif (preg_match('~^is( not)? even by (.*)$~',$operator,$e))
   {
    $return = '(('.$return.' / '.$this->_expr_token($e[2]).') % 2 '.($e[1] == ''?'!':'=').'= 0)';
   }
   elseif (preg_match('~^is( not)? div by (.*)$~',$operator,$e))
   {
    $return = '(('.$return.' % '.$this->_expr_token($e[2]).') '.($e[1] != ''?'!':'=').'= 0)';
   }
   else {return $this->_syntax_error('Unexpected operator \''.$operator.'\'');}
  }
  if (isset($m[8]) and $m[8] !== '')
  {
   $mods_token = $m[8];
   preg_match_all('~\|@?\s*\w+(?:\:(?:[^\:\|\'"]*(?:([\'"]).*?(?<!\\\\)\1[^\:\|\'"]*)*))*~',$mods_token,$mods_m,PREG_SET_ORDER);
   $mods = array();
   for ($i = 0,$s = sizeof($mods_m); $i < $s; $i++)
   {
    preg_match('~\|(@?\w+)(.*)~',$mods_m[$i][0],$q);
    $mod_name = $q[1];
    $params_token = $q[2];
    preg_match_all('~\:([^\:\|\'"]*(?:([\'"]).*?(?<!\\\\)\2[^\:\|\'"]*)*)~',$params_token,$p,PREG_SET_ORDER);
    $params = array();
    $mod = array($mod_name,array());
    for ($j = 0,$ps = sizeof($p); $j < $ps; $j++) {$mod[1][] = $this->_expr_token($p[$j][1]);}
    $mods[] = $mod;
   }
   for ($i = 0,$s = sizeof($mods); $i < $s; $i++)
   {
    if (substr($mods[$i][0],0,1) == '@') {$no_errors = TRUE; $mods[$i][0] = substr($mods[$i][0],1);}
    else {$no_errors = FALSE;}
    $mod_name = strtolower($mods[$i][0]);
    $mod_params = $mods[$i][1];
    if ($mod_name == 'upper' or $mod_name == 'lower') {$mod_name = 'strto'.$mod_name;}
    $short = FALSE;
    foreach ($this->allowed_php_tokens as $av)
    {
     if (preg_match($e = '~^'.str_replace('\*','.*',preg_quote($av,'~')).'$~i',$mod_name)) {$short = TRUE; break;}
    }
    if ($short) {}
    elseif (!preg_match('~^\w+$~',$mod_name) || !($p = $this->parent->fetch_plugin('modifier.'.$mod_name)))
    {
     return $this->_syntax_error('Undefined modifier \''.$mod_name.'\'');
    }
		if ($mod_name == 'escape' && sizeof($mod_params) == 0) {$return  = 'htmlspecialchars('.$return.')'; continue;}
		elseif ($mod_name == 'escape' && sizeof($mod_params) > 0 && $mod_params[0] == '\'urlencode\'') {$return  = 'urlencode('.$return.')'; continue;}
		elseif ($mod_name == 'escape' && sizeof($mod_params) > 0 && $mod_params[0] == '\'urldecode\'') {$return  = 'urldecode('.$return.')'; continue;}
    elseif ($mod_name == 'count' || $mod_name == 'sizeof') {$return  = 'sizeof('.$return.')'; continue;}
    elseif ($mod_name == 'urlencode') {$return  = 'urlencode('.$return.')'; continue;}
    elseif ($mod_name == 'cat' && isset($mod_params[0])) {$return  = $return.'.'.$mod_params[0]; continue;}
    if (!$short and !in_array($p,$this->load_plugins)) {$this->load_plugins[] = $p;}
    $return = ($no_errors?'@':'').(!$short?'quicky_modifier_':'').$mod_name.'('.$return.(sizeof($mod_params)?','.implode(',',$mod_params):'').')';
   }
  }
  return $return;
 }
 function _var_string_callback($m)
 {
  if (isset($m[6])) {return stripslashes($m[6]);}
  if ($m[0] == '\\"') {return '"';}
  $prefix = '';
  if (strlen($m[1]) != 0)
  {
   if (strlen($m[1]) % 2 != 0) {return stripslashes($m[1]).$m[2];}
   else {$prefix = var_export(stripslashes($m[1]),TRUE).'.';}
  }
  $expr = $m[2];
  if ((substr($expr,0,1) == $this->left_delimiter and substr($expr,-1) == $this->right_delimiter) ||
	 (substr($expr,0,1) == '`' and substr($expr,-1) == '`'))
  {
   $expr = substr($expr,1,-1);
   $return = $this->_expr_token(stripslashes($expr));
  }
  elseif (substr($expr,0,1) == '_') {$return = $this->_expr_token(stripslashes($expr));}
  else {$return = $prefix.$this->_var_token($m[2]);}
  return '\'.'.$return.'.\'';
 }
 function _expr_token($token,$instring = FALSE,$emptynull = FALSE)
 {
  if ($token === '') {return '';}
  if (substr($token,0,1) == '_')
  {
   if (substr($token,1,1) == '_') {$token = substr($token,1);}
   $token = substr($token,1);
   $return = var_export($this->_fetch_expr($this->_expr_token($token,FALSE,TRUE)),TRUE);
   return $return;
  }
  $in = $token;
  $token = ltrim($token);
  if ($instring)
  {
   $a = $token;
   if ($a[0] == '"')
   {
    $a = '\''.str_replace('\'','\\\'',substr($a,1,-1)).'\'';
    $ldelim = preg_quote($this->left_delimiter,'~');
    $rdelim = preg_quote($this->right_delimiter,'~');
    $o = $this->_cast_undefined_token_to_strings;
    $this->_cast_undefined_token_to_strings = TRUE;
    $a = preg_replace_callback('~(\\\*)('.$ldelim.'.*?'.$rdelim.'|`.*?`|_?[\$#]\w+#?(?:\[[\$#]?\w+#?\])*)|((?<!\\\\)\\\\")~',array($this,'_var_string_callback'),$a);
    $this->_cast_undefined_token_to_strings = $o;
    $a = preg_replace('~\.\'(?<!\\\\)\'|(?<!\\\\)\'\'\.|^\'\.(?=[\$\(])|(?<=[\)\'])\.\'$|\'\.\'~','',$a);
   }
   return $a;
  }
  $return = preg_replace_callback(
    '~(([\'"]).*?(?<!\\\\)\2|\w*\s*\(((?:(?R)|.)*?)\)'
    .'|(?!(?:is\s+not|is|not\s+eq|eq|neq?|gt|lt|gt?e|ge|lt?e|mod)\W)_?[\$#]?\w+#?(?:\\[(?:(?R)|\w+|((?:[^\\]\'"]*(?:([\'"]).*?(?<!\\\\)\5)?)*))*?\\]|\.[\$#]?\w+#?|->\s*_?[\$#]?\w+#?(?:\(((?:(?R)|.)*?)\))?)*'
    .'|-?\d+|(?<=^|[\s\)\:\.=+\-<>])(?!(?:is\s+not|is|not\s+eq|eq|neq?|gt|lt|gt?e|ge|lt?e|mod)\W)(?:\w+)(?=$|[\s\|\.\:\(=+\-<>]))(\s+(?:is(?:\s+not)?\s+(?:odd|div|even)\s+by\s+-?\d+|is(?:\s+not)?\s+(?:odd|even)))?((?:\|@?\w+(?:\\:(?:'.'\w*\(((?:(?R)|.)*?)\)|[\$#]\w+#?(?:\\[(?:(?R)|((?:[^\\]\'"]*(?:([\'"]).*?(?<!\\\\)\11)?)*))*?\\]|\.[\$#]?\w+#?)*|[^\'"\:]*(?:[^\'"\:]*([\'"]).*?(?<!\\\\)\12[^\'"\:]*)*'.'))*)*)'
    .'|((?<=\s|\))(?:is\s+not|is|not\s+eq|eq|neq?|gt|lt|gt?e|ge|lt?e|mod)(?=\s|\()|(?:not\s+))'
    .'~si',array($this,'_expr_token_callback'),$token);
  if ($emptynull and trim($return) === '') {return 'NULL';}
  return $return;
 }
}