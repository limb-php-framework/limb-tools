<?php
// автор WP - phpclub.ru -> user
require_once $smarty->_get_plugin_filepath('make_timestamp', 'shared');

function lapa_modifier_date_format($string, $format="%b %e, %Y", $default_date=null)
{
    if (substr(PHP_OS,0,3) == 'WIN') {
        $hours = strftime('%I', $string);
        $short_hours = ( $hours < 10 ) ? substr( $hours, -1) : $hours; 
        $_win_from = array ('%e',  '%T',       '%D',        '%l');
        $_win_to   = array ('%#d', '%H:%M:%S', '%m/%d/%y',  $short_hours);
        $format = str_replace($_win_from, $_win_to, $format);
    }
    if($string != '') {
        return strftime($format, lapa_make_timestamp($string));
    } elseif (isset($default_date) && $default_date != '') {
        return strftime($format, lapa_make_timestamp($default_date));
    } else {
        return;
    }
}
