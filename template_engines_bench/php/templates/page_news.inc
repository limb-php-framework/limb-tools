<h2>Introduction</h2>
<p>Mr. Treehorn draws a lot of water in this town. You don't draw shit, Lebowski. Now we got a nice, quiet little beach community here, and I aim to keep it nice and quiet. So let me make something plain. I don't like you sucking around, bothering our citizens, Lebowski. I don't like your jerk-off name. I don't like your jerk-off face. I don't like your jerk-off behavior, and I don't like you, jerk-off.</p>
<h2>News</h2>
<?php
$n_news = count($_NEWS);
for($i=0;$i<$n_news;$i++) {
  $item = $_NEWS[$i];
  include('news_item.inc');
} ?>