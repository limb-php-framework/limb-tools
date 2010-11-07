<?php

include('../data.inc');

$adverts = array();
foreach(array_rand($_ADVERTS,3) as $i) {
  array_push($adverts, $_ADVERTS[$i]);
}

$data= array(
	'adverts' => $adverts,
	'sections' => $_SECTIONS,
	'users' => $_STAT,
	'news' => $_NEWS,
	'poll' => $_POLL,
	'title' => 'XSLTExample',
);
$even= 1;
foreach( $data['sections'] as $numb => $sect ):
	$data['sections'][ $numb ]['rip']= $sect['rip'] ? '' : null;
	$data['sections'][ $numb ]['even']= $even^= 1;
endforeach;
$data['users']['ONLINE']['count']= count($_STAT['ONLINE']);
$data['sections']['count']= count($_SECTIONS);


$buffer= array( 
    "<?xml version='1.0' encoding='utf-8' ?>", 
    "<?xml-stylesheet version='1.0' type='text/xsl' href='page.xsl' ?>",
    '<m:page xmlns="http://www.w3.org/1999/xhtml" xmlns:h="http://www.w3.org/1999/xhtml" xmlns:m="urn:model">', 
); 

$stack= array( array( 'page', $data ) ); 
$escape= true; 
while( $stack ){ 
    if( @list( $name, $value )= each( $stack[0][1] ) ): 
        if( is_null( $value ) ) continue; 
        if( $name === 'text/xml' ) $name= $escape= ''; 
        if( is_numeric( $name ) ) $name= 'item'; 
        if( $name && ( $value === '' ) ): 
            $buffer[]= "<m:{$name} />"; 
        elseif( is_array( $value ) ): 
            if( $name ) $buffer[]= "<m:{$name}>"; 
             array_unshift( $stack, array( $name, $value ) ); 
        else: 
            if( $name ) $buffer[]= "<m:{$name}>"; 
            if( $escape ) $value= htmlspecialchars( $value ); 
            $buffer[]= $value; 
            if( $name ) $buffer[]= "</m:{$name}>"; 
        endif; 
    else: 
        list( $name )= array_shift( $stack ); 
        if( $name ) $buffer[]= "</m:{$name}>"; 
        if( is_int( $name ) ) $escape= true; 
    endif; 
}; 

$xmlstr= implode( '', $buffer );

//header( 'Content-Type: text/xml', true );

$xml= new DOMDocument; 
$xml->loadXML( $xmlstr ); 

$xsl= new DOMDocument; 
$xsl->load( dirname(__FILE__) . '/templates/page_news.xsl' ); 

$proc= new XSLTProcessor( ); 
$proc->importStyleSheet($xsl);
$xmlstr= $proc->transformToXML($xml);

echo $xmlstr;

?>