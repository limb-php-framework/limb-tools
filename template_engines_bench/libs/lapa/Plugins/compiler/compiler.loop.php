<?php

function lapa_compiler_loop($act, $parser) 
{
    if ( 1 == $act ) {
        $plugin_name    = 'loop';
        
        $attr = $parser->parseDirectiveAttributes();
            
        if ( !key_exists('name', $attr) ) {
            $property_name = $this->template_line;
        }else {
            $property_name = $attr['name'];
            $parser->setPluginProperty($plugin_name, trim($property_name, "'"), '',  $parser->templateLine());
        }
        $parser->openBlock($plugin_name, $property_name);
            
        $str_name = '$_loop_for[' . $property_name . ']';        
        if ( key_exists('from', $attr) ) {
            $result  = 'if (is_array(' . $attr['from'] . ') || is_object(' . $attr['from'] . ') ): ' . "\n"
            . $str_name . "['total'] = count(" .  $attr['from'] . ')-1;' 
            . 'else:'
            . $str_name . "['total'] = (int)" . $attr['from'] . ';' 
            . 'endif;' . "\n";
        }else {
            throw new LapaEngineException('Syntax: пропущен параметр "from", строка %s',  $parser->templateLine());    
        }

        if ( key_exists('value', $attr) ) { 
            $step    = key_exists('step', $attr) ? $attr['step']: 1;
            $result .= $str_name . "['step'] = (int)" . $step . ";\n";
            $start   = key_exists('start', $attr) ? '(int) ' . $attr['start'] : 0; 
            if ( $attr['value'][0] == '$' ) {
                $str_i = $attr['value'];
            }else {
                $str_i = '$_var[' . $attr['value'] . ']';
            }
        }else {
            throw new LapaEngineException('Syntax: пропущен "value", строка %s',  $this->template_line);    
        }
        $result .= 'for(' . "$str_i = $start" . ';' . $str_name . "['total'] >= " . $str_i . ';' . $attr['value'] . ' += ' . $str_name . "['step']" . '):';
    }else {
        $result = lapa_compiler_endloop($act, $parser);    
    }   
    return $result;
}
    
function lapa_compiler_endloop($act, $parser)
{
    $plugin_name    = 'loop';
    if ( !$parser->getBlock($plugin_name, $plugin_params) ) {
        throw new LapaEngineException('Syntax: "loop", проверьте уровень вложенности, cтрока %s', $parser->templateLine() );
    }
    $parser->closeBlock($plugin_name);
    return 'endfor;' . "\n";
}

function lapa_compiler_loop_var($params, $parser)
{
    $plugin_name    = 'loop';
    if ( count($params)  > 1) {
        $property_name  =  $params[1];
    }else {
        throw new LapaEngineException('Syntax: "loop", пропущен параметр, строка %s', $parser->templateLine() );
    }
    
    if ( $parser->isPluginProperty($plugin_name, $property_name) ) {
        if ( 'total' == $params[2] ) {
            $result = '$_loop_for' . "['" . $property_name . "']['total']";
        }else {
            $this->notice_error('Имя блока не существует, строка %s', $this->template_line);
            $result = "''";
        }
    }else {
        $parser->warning_error('Блок "loop.%s" не зарегистрирован, строка %s', $property_name, $parser->templateLine() );
        $result = "''";
    }
    return $result;
}