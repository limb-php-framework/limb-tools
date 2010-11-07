<?php



function lapa_compiler_foreach($act, $parser)
{
    if ( 1 == $act ) {
        $plugin_name    = 'foreach';
        
        $attr = $parser->parseDirectiveAttributes();
        
        if ( key_exists('from', $attr) ) {
                $from = $attr['from'];
        }else{
            throw new LapaEngineException('Syntax: "foreach", пропущен параметр "from", строка %s ', $parser->templateLine());
        }   
        
        if ( !key_exists('name', $attr) ) {
            $name = $parser->templateLine();
        }else {
            $name = trim($attr['name'], "'");
            $parser->setPluginProperty($plugin_name, $name, '',  $parser->templateLine());
        }
                
        if ( key_exists('key', $attr) ) {
            $key = $attr['key'][0] != '$' ? "\$_var[{$attr['key']}]": $attr['key'];
        }else {
            $key = null;
        }
        if ( key_exists('item', $attr) ) {
            $item = $attr['item'][0] != '$' ? "\$_var[{$attr['item']}]": $attr['item'];
        }else {
            $item = null;
        }
        if ( $key == null && $item == null ) {
            throw new LapaEngineException('Syntax: "foreach" пропущен параметр "from" и/или "item", строка %s', $parser->templateLine());
        }
        
        $result = '';
        $str_name = "\$_foreach['$name']";        
        $result = '$copy_from = ' . $attr['from'] . ";\n";
        
        $result .= "if ( is_array($from) || is_object($from) ):\n"
            . $str_name . " = array('total' => count($from), 'iteration' => 0); " 
            . "\nelse:\n" 
            . $str_name . " = array('total' => 0, 'iteration' => 0);\nendif;\n" 
            . "if ( {$str_name}['total'] > 0):\n"
            . 'foreach ($copy_from as ';
        
        if ( is_null($key) ) {
            $result .= $item;
        }else {
            $result .= $key;
            if ( !is_null($item) ) {
                $result .= ' => ' . $item;
            }
        }
        $result .= "):\n" . "        ++{$str_name}['iteration'];\n"; 
        if ( key_exists('max', $attr) ) {
            $result .= "        if ({$str_name}['iteration'] == {$attr}['max']):/n            break;\n        endif;\n";
        }  
                      
        $parser->openBlock($plugin_name, array('foreachelse' => false));
        return $result;
          
    }else {    
        return lapa_compiler_endforeach($act, $parser);
    }
}

function lapa_compiler_foreachelse($act, $parser)
{
    $plugin_name    = 'foreach';
    
    if ( !$parser->getBlock($plugin_name, $plugin_params) || $plugin_params['foreachelse'] ) {
        throw new LapaEngineException('Syntax: "foreach", проверьте уровень вложенности, cтрока %s', $parser->templateLine() );
    }
    $parser->setBlock($plugin_name, array('foreachelse' => true) );
    return 'endforeach; unset($copy_from);' . "\nelse:\n";
}

function lapa_compiler_endforeach($act, $parser)
{
    $plugin_name = 'foreach';
    
    if ( !$parser->getBlock($plugin_name, $plugin_params) ) {
        throw new LapaEngineException('Syntax: "foreach", проверьте уровень вложенности, cтрока %s', $parser->templateLine() );
    }
    $parser->closeBlock($plugin_name);
    
    if ( $plugin_params['foreachelse'] ) {
        return 'endif;' . "\n";
    }else {
        return 'endforeach; endif; unset($copy_from);' . "\n";
    }
}

function lapa_compiler_foreach_var($params, $parser)
{
    $plugin_name = 'foreach';
    
    $property_var['iteration']    = '(%s[\'iteration\'])';
    $property_var['first']        = '(%s[\'iteration\'] <= 1)';
    $property_var['last']         = '(%s[\'iteration\'] == %s[\'total\'])';
    $property_var['show']         = '(%s[\'total\'] > 1)';
    $property_var['total']        = '(%s[\'total\'])';
    
    $property_name  = count($params) > 1 ? $params[1] : null;
    $property_value = count($params) > 2 ? $params[2] : null;
    
    if ( $parser->isPluginProperty($plugin_name, $property_name) ) {
        if ( key_exists($property_value, $property_var) ) {
            $result = str_replace('%s', "\$_foreach['{$property_name}']", $property_var[$property_value]);
        }else {
            $this->warning_error('Syntax: "foreach", свойства "%s" не существует, cтрока %s', $property_value, $parser->templateLine() );
            $result = "''";
        }
    }else {
        $parser->warning_error('Блок "foreach->%s" не зарегистрирован , строка %s', $property_name, $parser->templateLine() );
        $result = "''";
    }
    return $result;
}
