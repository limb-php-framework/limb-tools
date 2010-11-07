<?php
set_include_path(
  dirname(__FILE__) . '/' . PATH_SEPARATOR .
  dirname(__FILE__) . '/../libs/lapa/' . PATH_SEPARATOR .
  get_include_path()
);

include('../data.inc');
include('View.php');

$tpl = new LapaView();

$tpl->template_dir = './templates';
$tpl->compile_dir = './cache';
$tpl->force_compile = false;

// root vars
$tpl->assign(
    array (
        'num_total' => $_STAT['TOTAL'],
        'num_online'=> count($_STAT['ONLINE']),
        'poll_title' => $_POLL['TITLE'],
        'poll_question' =>  $_POLL['QUESTION'],
        'poll_button' => $_POLL['BUTTON'],
    )
);

shuffle($_ADVERTS);
$tpl->assign_by_ref('adverts',$_ADVERTS);
$tpl->assign_by_ref('sections',$_SECTIONS);
$tpl->assign_by_ref('users',$_STAT['ONLINE']);
$tpl->assign_by_ref('news',$_NEWS);
$tpl->assign_by_ref('poll_answers',$_POLL['ANSWERS']);

// out
$tpl->display('page.tpl');

?>
