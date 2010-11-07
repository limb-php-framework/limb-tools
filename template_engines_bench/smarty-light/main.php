<?

include('../data.inc');
include('../libs/smarty-light/src/class.template.php');

$tpl = new template();

$tpl->template_dir = './templates';
$tpl->compile_dir = './cache';
$tpl->compile_check = true;
$tpl->compile_check = false;
$tpl->left_tag = '{';
$tpl->right_tag = '}';

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

$adverts = array();
foreach(array_rand($_ADVERTS,3) as $i) {
  array_push($adverts, $_ADVERTS[$i]);
}

$tpl->assign('adverts',$adverts);
$tpl->assign('sections',$_SECTIONS);
$tpl->assign('users',$_STAT['ONLINE']);
$tpl->assign('news',$_NEWS);
$tpl->assign('poll_answers',$_POLL['ANSWERS']);

// out
$tpl->assign('page','page_news.tpl');
$tpl->display('page.tpl');

?>
