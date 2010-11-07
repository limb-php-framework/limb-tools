<?
include_once('../data.inc');

$data = array (
        'num_total' => & $_STAT['TOTAL'],
        'num_online'=> count($_STAT['ONLINE']),
        'poll_title' => & $_POLL['TITLE'],
        'poll_question' =>  & $_POLL['QUESTION'],
        'poll_button' => & $_POLL['BUTTON'],
        'adverts' =>  array(),
        'sections' => array(),
        'users' => array(),
        'news' => array(),
        'answers' => array()
);

$random_keys =  array_rand($_ADVERTS, 3);
foreach ($random_keys as $i)
  $data['adverts'][] = $_ADVERTS[$i];

foreach ($_SECTIONS as $i => $r)
{
    $row = array(
            'id' => $r['id'],
            'name' => $r['name']);
    if ($r['rip'])
    {
      $row['rip'] = array(array());
    }
    if ($i % 2)
    {
      $row['odd'] = array(array());
    }
    else
    {
      $row['even'] = array(array());
    }
    $data['sections'][] = $row;
}

// users
$data['users'] = $_STAT['ONLINE'];
$data['news'] = $_NEWS;

foreach ($_POLL['ANSWERS'] as $i => $r)
{
    $data['poll_answers'][] = array(
        'id' => $i,
        'answer' => $r);
}


$T = new Blitz('main.tpl');
$T->set($data);

echo $T->parse();

?>
