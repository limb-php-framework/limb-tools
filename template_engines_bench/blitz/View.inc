<?php
class View extends Blitz {
  protected $data;
  protected $path;

  function __construct($path,$data) {
    $this->data = $data;
    $this->path = $path;
    parent::__construct($path.'page.tpl');
    $this->set($this->get_root_vars());
    
  }
  
  function page_content() {
    return $this->include($this->path.'page_news.tpl');
  }  

  function get_root_vars() {
    $stat = & $this->data['STAT'];
    $total = isset($stat['TOTAL']) ? (int)$stat['TOTAL'] : 0;
    $online = isset($stat['ONLINE']) ? count($stat['ONLINE']) : 0;
    
    $answers_count = count($this->data['POLL']['ANSWERS']);
    $answers = array();
    for($i = 0; $i < $answers_count; $i++) {
      $answers[] = array(
        'id' => $i,
        'answer' => $this->data['POLL']['ANSWERS'][$i]
      );
    }    
    
    $sections_count = count($this->data['SECTIONS']);
    $sections = array();
    for($i = 0; $i < $sections_count; $i++) {
    	$idata = $this->data['SECTIONS'][$i];
    	$sections[] = array(
        'id'   => $idata['id'],
        'name' => $idata['name'],
        'odd'  => $i%2,
        'rip'  => $idata['rip']
      );
    }
    
    return array(
      'total_users'   => $total,
      'total_online_users'    => $online,
      'poll_title' => $this->data['POLL']['TITLE'],
      'poll_question' =>  $this->data['POLL']['QUESTION'],
      'poll_button' => $this->data['POLL']['BUTTON'],
      'users_online' => $this->data['STAT']['ONLINE'],
      'answers' => $answers,
      'sections' => $sections
    );
  }
  
  function news() {
    $tpl_name = $this->path.'news_item.tpl';
    $result = '';
    $n_news = count($this->data['NEWS']);
    for($i=0; $i<$n_news; $i++) {
      $result .= $this->include($tpl_name, $this->data['NEWS'][$i]);
    }

    return $result;
  }

  function adverts() {
    $random_keys =  array_rand($this->data['ADVERTS'],3);
    $result = '';
    $tpl_name = $this->path.'advert_item.tpl';

    foreach($random_keys as $i) {
      $idata = & $this->data['ADVERTS'][$i];
      $result .= $this->include($tpl_name, $idata);
    }

    return $result;
  }  
}