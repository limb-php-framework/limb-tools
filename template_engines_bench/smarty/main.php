<?

include('../data.inc');
include('../libs/smarty/libs/Smarty.class.php');

$tpl = new Smarty();

$tpl->template_dir = './templates';
$tpl->compile_dir = './cache';
$tpl->compile_check = true;
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
$tpl->assign_by_ref('adverts',$_ADVERTS);
$tpl->assign_by_ref('sections',$_SECTIONS);
$tpl->assign_by_ref('users',$_STAT['ONLINE']);
$tpl->assign_by_ref('news',$_NEWS);
$tpl->assign_by_ref('poll_answers',$_POLL['ANSWERS']);

// out
$tpl->assign('page','page_news');
$tpl->display('page.tpl');

?>
