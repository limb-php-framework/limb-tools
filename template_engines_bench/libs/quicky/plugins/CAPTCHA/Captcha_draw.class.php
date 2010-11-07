<?php
/**************************************************************************/
/* (c)oded 2006 by white phoenix
/* http://whitephoenix.ru
/*
/* CAPTCHA_draw.class.php
/**************************************************************************/
class CAPTCHA_draw
{
 public $text;
 public $noise1 = TRUE;
 public $noise2 = FALSE;
 public $noise3 = FALSE;
 public $noise_wave_n = 0;
 public $noise_skew_n = 1;
 public $fonts_dir;
 public $simbols = 'ABCDEFGHKLMNPQRSTUVWXYZ23456789abcdefghkmnpqrstuvwxyz23456789';
 function __construct() {$this->fonts_dir = dirname(__FILE__).'/fonts/';}
 function generate_text()
 {
  $this->text = '';
  $l = strlen($this->simbols)-1;
  for ($i = 0; $i < 4; $i++) {$this->text .= substr($this->simbols,rand(0,$l),1);}
  return $this->text;
 }
 function show()
 {
  require_once dirname(__FILE__).'/imagedraw.class.php';
  ob_start();
  $text = $this->text;
  $draw = new imagedraw;
  $draw->W = 150;
  $draw->H = 50;
  $draw->init();
  $draw->createtruecolor();
  $draw->antialias(TRUE);
  $draw->setbgcolor();
  $fonts = array();
  $handle = opendir($this->fonts_dir);
  while (($f = readdir($handle)) !== FALSE) {if (preg_match('~\.ttf$~i',$f)) {$fonts[] = $f;}}
  closedir($handle);
  for($i=0;$i<strlen($text);$i++)
  {
   $size = rand(25,30);
   $angle = rand(-10,10);
   $x = 15+rand(-5,5)+$i*30;
   $y = 30+rand(-5,5)+$size/5;
   $draw->ttftext($text{$i},0x000000,$this->fonts_dir.$fonts[array_rand($fonts)],$size,$x,$y,$angle);
  }
  for ($j = 0; $j < 2; $j++)
  {
   $lastX = -1;
   $lastY = -1;
   $N = rand(20,40);
   for ($i = 5; $i < $draw->W-1; $i+=10)
   {
    $X = $i;
    $Y = $draw->H/2-sin($X/$N)*10+$j*20;
    if ($lastX > -1) {$draw->line1($lastX,$lastY,$X,$Y,0x000000,2);}
    $lastX = $X;
    $lastY = $Y;
   }
  }
  if ($this->noise3)
  {
   for ($i = 0; $i < 2; $i++)
   {
    $draw->line1(
				5,				rand(0,$draw->W-1),
				$draw->sX()-5,	rand(0,$draw->H),
    0x000000,3);
   }
  }
  for ($i = 0; $i < $this->noise_wave_n; $i++) {$draw->wave_region(0,0,$draw->W,$draw->H);}
  for ($i = 0; $i < $this->noise_skew_n; $i++) {$draw->skew_waves();}
  if ($this->noise1) {for($x = 1; $x < $draw->W-1; $x++) {for($y = 1; $y < $draw->H-1; $y++) {if ($y%2 == 0 and $x%2 == 0) {$draw->setpixel($x,$y,'000000');}}}}
  if ($this->noise2) {for($x=1;$x<$draw->W;$x++) {for($y=1;$y<$draw->H;$y++) {if (rand(0,10) == 0) {$draw->setpixel($x,$y,'FFFFFF');}}}}
  $draw->border();
  $draw->colortransparent();
  if (strlen(ob_get_contents()) == 0) {header('Content-type: image/png');}
  $draw->out();
 }
}