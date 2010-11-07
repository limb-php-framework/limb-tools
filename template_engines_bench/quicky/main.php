<?
include('../data.inc');
include('../libs/quicky/Quicky.class.php');

$tpl = new Quicky();

$tpl->template_dir = './templates/';
$tpl->compile_dir = './cache/';
$tpl->inline_includes = true;
$tpl->compile_check = false;

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
$random_keys =  array_rand($_DATA['ADVERTS'],3);
$adverts = array();
foreach($random_keys as $i) {
  array_push($adverts, $_DATA['ADVERTS'][$i]);
}
$tpl->assign_by_ref('adverts',$adverts);
$tpl->assign_by_ref('sections',$_SECTIONS);
$tpl->assign_by_ref('users',$_STAT['ONLINE']);
$tpl->assign_by_ref('news',$_NEWS);
$tpl->assign_by_ref('poll_answers',$_POLL['ANSWERS']);

// out
$tpl->assign('page','page_news');
$tpl->display('page_news.tpl');

?>