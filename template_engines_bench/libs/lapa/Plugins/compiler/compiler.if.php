<?php 

function lapa_compiler_if($act, $parser)
{
    if ( 1 == $act ) {
        $plugin_name = 'if';
        
        $parser->openBlock($plugin_name, false);
        $params = $parser->_parseExpression();
        if ( '' ==  $params ) {
            throw new LapaEngineException('Syntax: "if", пустое выражение, cтрока %s', $parser->templateLine() );
        }
        return 'if (' . $params . "): \n";
    }else {
        return lapa_compiler_endif($act, $parser);
    }
}
        
function lapa_compiler_elseif($act, $parser)
{
    $plugin_name = 'if';
    
    if ( !$parser->getBlock($plugin_name, $plugin_params) || $plugin_params ) {
        throw new LapaEngineException('Syntax: "if", проверьте уровень вложенности, cтрока %s', $parser->templateLine() );
    }
    $params = $parser->_parseExpression();
    if ( '' ==  $params ) {
        throw new LapaEngineException('Syntax: "if", пустое выражение, cтрока %s', $parser->templateLine() );
    }
    return 'elseif (' . $params . '):' . "\n";
 }

function lapa_compiler_else($act, $parser)
{
    $plugin_name = 'if';
    
    if ( !$parser->getBlock($plugin_name, $plugin_params) || $plugin_params ) {
        throw new LapaEngineException('Syntax: "if", проверьте уровень вложенности, cтрока %s', $parser->templateLine() );
    }
    $parser->setBlock($plugin_name, true);
    return 'else:' . "\n";
}

function lapa_compiler_endif($act, $parser)
{
    $plugin_name = 'if';
    
    if ( !$parser->getBlock($plugin_name, $plugin_params)) {
        throw new LapaEngineException('Syntax: "if", проверьте уровень вложенности, cтрока %s', $parser->templateLine() );
    }
    $parser->closeBlock($plugin_name); 
    return "\n" . 'endif;' . "\n";  
}