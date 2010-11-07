<?php

include('../data.inc');
include('View.inc');
$_tpl_path = './templates/';

$T = new View($_tpl_path,$_DATA);
echo $T->parse();
?>
