<?php 
/**
 * switch
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
function lapa_compiler_section($act, $parser)
{        
    if ( 1 == $act ) {
        $plugin_name = 'section';
        
        $attr = $parser->parseDirectiveAttributes();
        if ( !key_exists('name', $attr) ) {
             throw new LapaEngineException('Syntax: пропущен параметр "name" в section, строка %s', $parser->templateLine());
        }else {
            $name = trim($attr['name'], "'");
            $parser->setPluginProperty($plugin_name, $name, '',  $parser->templateLine());
        }
        if ( !key_exists('loop', $attr) && !key_exists('from', $attr) ) {
            throw new LapaEngineException('Syntax: пропущен параметр "loop/from" в section, строка %s', $parser->templateLine());
        }
        
        $parser->openBlock($plugin_name, false );
        
        $section_name = "\$_var_section['$name']";
            
        $result = "if (isset($section_name)) {\n    unset($section_name);\n}\n";
        
        
        
        foreach ( $attr as $key => $val ) {
            switch ($key) {
                case 'loop':case 'from':
                    $result .= "{$section_name}['$key'] = is_array(\$_loop=$val)||is_object(\$_loop=$val) ? count(\$_loop) : max(0, (int)\$_loop); unset(\$_loop);\n";
                break;
                case 'show':
                    $result .= "{$section_name}['show'] = " . (bool) $val? true: false . ";\n";
                break; 
                case 'max': case 'start':
                    $result .= "{$section_name}['$key'] = $val;\n";
                break;
                case 'step':
                    $result .= "{$section_name}['$key'] = ((int)$val) == 0 ? 1 : (int)$val;\n";
                break;
            }
        }
        //$result .= "{$section_name}['loop'] = 1;\n";
        if ( !key_exists('show', $attr) ) {
            $result .= "{$section_name}['show'] = true;\n";  
        }
        if ( !key_exists('max', $attr) ) {
            $result .= "{$section_name}['max'] = {$section_name}['loop'];\n";
        }else {
            $result .= "if ({$section_name}['max'] < 0)\n" .
                   "    {$section_name}['max'] = {$section_name}['loop'];\n";
        }
        if ( !key_exists('step', $attr) ) {
            $result .= "{$section_name}['step'] = 1;\n";
        }
        if ( !key_exists('start', $attr) ) {
            $result .= "{$section_name}['start'] = {$section_name}['step'] > 0 ? 0 : {$section_name}['loop']-1;\n";
        }else {
            $result .= "if ({$section_name}['start'] < 0)\n" .
                     "    {$section_name}['start'] = max({$section_name}['step'] > 0 ? 0 : -1, {$section_name}['loop'] + {$section_name}['start']);\n" .
                     "else\n" .
                     "    {$section_name}['start'] = min({$section_name}['start'], {$section_name}['step'] > 0 ? {$section_name}['loop'] : {$section_name}['loop']-1);\n";
        }

        $result .= "if ({$section_name}['show']) {\n";
        if ( !key_exists('start', $attr) && !key_exists('step', $attr) && !key_exists('max', $attr)) {
            $result .= "    {$section_name}['total'] = {$section_name}['loop'];\n";
        } else {
            $result .= "    {$section_name}['total'] = min(ceil(({$section_name}['step'] > 0 ? {$section_name}['loop'] - {$section_name}['start'] : {$section_name}['start']+1)/abs({$section_name}['step'])), {$section_name}['max']);\n";
        }
        $result .= "    if ({$section_name}['total'] == 0)\n" .
                 "        {$section_name}['show'] = false;\n" .
                 "} else\n" .
                 "    {$section_name}['total'] = 0;\n";
        $result .= "if ({$section_name}['show']):\n";
        $result .= "
            for ({$section_name}['index'] = {$section_name}['start'], {$section_name}['iteration'] = 1;
            {$section_name}['iteration'] <= {$section_name}['total'];
            {$section_name}['index'] += {$section_name}['step'], ++{$section_name}['iteration']):\n";
        return $result;
        
    }else {
        return lapa_compiler_endsection($act, $parser);
    }
}

function lapa_compiler_endsection($act, $parser)
{
    $plugin_name = 'section';
    
    if ( !$parser->getBlock($plugin_name, $plugin_params) ) {
        throw new LapaEngineException('Syntax: "section", проверьте уровень вложенности, cтрока %s', $parser->templateLine() );
    }
    $parser->closeBlock($plugin_name);
    
    if ( $plugin_params ) {
        return 'endif;' . "\n";
    }else {
        return 'endfor; endif;' . "\n";
    }
}

function lapa_compiler_sectionelse($act, $parser)
{
    $plugin_name = 'section';
    if ( !$parser->getBlock($plugin_name, $plugin_params) || $plugin_params) {
        throw new LapaEngineException('Syntax: "section", проверьте уровень вложенности, cтрока %s', $parser->templateLine() );
    }
    $parser->setBlock($plugin_name, true);
    return 'endfor; else:' . "\n";
}


function lapa_compiler_section_var($params, $parser)
{
    $plugin_name = 'section';
    
    $property_name  = count($params) > 1 ? $params[1] : null;
    $property_value = count($params) > 2 ? $params[2] : null;
    
    $property_var['rownum']       = '%s[\'iteration\']';
    $property_var['iteration']    = '%s[\'iteration\']';
    $property_var['total']        = '%s[\'iteration\']';
    $property_var['index_prev']   = '(%s[\'index\'] - %s[\'step\'])';
    $property_var['prev']         = '(%s[\'index\'] - %s[\'step\'])';
    $property_var['index_next']   = '(%s[\'index\'] + %s[\'step\'])';
    $property_var['next']         = '(%s[\'index\'] + %s[\'step\'])';
    $property_var['first']        = '(%s[\'iteration\'] == 1)';
    $property_var['last']         = '(%s[\'iteration\'] == %s[\'total\'])';
    $property_var['loop']         = '(%s[\'loop\'])';
    $property_var['show']         = '(%s[\'show\'])'; 
    $property_var['index']        = '(%s[\'index\'])';  
    
    if ( $parser->isPluginProperty($plugin_name, $property_name) ) {
        if ( key_exists($property_value, $property_var) ) {
            $result = str_replace('%s', "\$_var_section['{$property_name}']", $property_var[$property_value]);
        }else {
            $parser->warning_error('Syntax: "section", свойства "%s" не существует, cтрока %s', $property_value, $parser->templateLine() );
            $result = "''";
        }
    }else {
        $parser->warning_error('Блок "section->%s" не зарегистрирован , строка %s', $property_name, $parser->templateLine() );
        $result = "''";
    }
    return $result;
}