<?php
error_reporting(E_ALL);
define('BENCH_ITERATIONS_DEFAULT', 1000);
define('BENCH_ITERATIONS_MAX', 30000);
define('BENCH_CONCURENCY_FACTOR',  1 / 200);
define('BENCH_CONCURENCY_MIN',  1);
define('BENCH_ENGINES_DIR', dirname(__FILE__));
require_once 'bench.inc.php';
define('BENCH_PATH_TO_AB', '/usr/sbin/ab');
define('BENCH_BASE_URL', get_bench_root_url());
define("BENCH_LOG_PATH", dirname(__FILE__).'/bench.log');

function get_action()
{
  if(isset($_REQUEST['bench_all']))
    return 'bench_all';
  if(isset($_REQUEST['bench_selected']) && isset($_REQUEST['engines']))
    return 'bench_selected';
  return 'default';  
}

function is_result_action()
{
  return ('default' != get_action());
}
?>

<!-- Powered by LIMB | http://www.limb-project.com/ -->
<!-- Designed by BIT | http://www.bit-creative.com/ -->
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <title>
      Limb PHP Framework &nbsp; » &nbsp; Home &nbsp; » &nbsp; Limb3
    </title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="title" content="Limb PHP Web Application Framework"/>
    <meta name="robots" content="index, follow"/>
    <meta name="description" content="Limb PHP Web Application Framework"/>
    <meta name="keywords" content="limb, project, framework, php, php5, open-source, lgpl, limb3, agile, tdd"/>
    <meta name="language" content="en"/>
    <base href='http://limb-project.com/'/>
    <link rel="stylesheet" type="text/css" href="styles/main.css"/>
    <link rel="stylesheet" media="print" type="text/css" href="styles/print.css"/>
    <link rel="alternate" type="application/rss+xml" title="Recent News" href="/news/rss"/>
    <script src="http://www.google-analytics.com/urchin.js" type="text/javascript">
</script>

    <link rel="stylesheet" media="screen" type="text/css" href="/menu/styles/menu.css"/>
  </head>
  <body id="page">
    <div id="wrapper">
      <div id="header">
        <div class="center">
          <a href="/"><img src="images/logo.limb.gif" width="153" height="58"
          alt="Limb Web Application Framework" title=
          "Limb Web Application Framework" id="logo" name="logo"/></a>
        </div>
      </div>

      <div class="center_menu">
        <script type="text/javascript" src="/menu/scripts/jquery.js"></script>
        <script type="text/javascript" src="/menu/scripts/dropdownmenu2.js"></script>
        <script type="text/javascript">
          var menu = new LimbMenu();
          menu.addMenu('menu1');
          menu.addMenu('menu2');
          menu.addMenu('menu3');
          menu.addMenu('menu4');
          menu.addMenu('menu5');
          menu.addMenu('menu6');
        </script>
        <div id="menu" onmouseover="menu.keepMenuVisible();" onmouseout=
        "menu.delayHideMenus();">
          <div id="top_menu">
            <div id="page-width">

              <ul id="mainmenulist" class="float-break">
                <li>
                  <a href="http://limb-project.com" onmouseover=
                  "menu.showMenu('menu1');">limb-project.com</a>
                  <div style="display: none;" class="dropdownmenu" id="menu1">
                    <ul>
                      <li>
                        <div>
                          <a href="http://limb-project.com/news">News</a>

                        </div>
                      </li>
                      <li>
                        <div>
                          <a href=
                          "http://wiki.limb-project.com/roadmap">Roadmap</a>
                        </div>
                      </li>
                      <li>

                        <div>
                          <a href="http://limb-project.com/limb3">Limb3</a>
                        </div>
                      </li>
                      <li>
                        <div>
                          <a href="http://limb-project.com/limb2">Limb2</a>
                        </div>

                      </li>
                      <li>
                        <div>
                          <a href="http://old.limb-project.com">Old site</a>
                        </div>
                      </li>
                    </ul>
                  </div>

                </li>
                <li class="current">
                  <a href="http://limb-project.com/community" onmouseover=
                  "menu.showMenu('menu2');">Community</a>
                  <div style="display: none;" class="dropdownmenu" id="menu2">
                    <ul>
                      <li>
                        <div>
                          <a href="http://forum.limb-project.com">Forum</a>

                        </div>
                      </li>
                      <li>
                        <div>
                          <a href=
                          "http://lists.limb-project.com/mailman/listinfo">Mailing
                          lists</a>
                        </div>
                      </li>
                    </ul>

                  </div>
                </li>
                <li class="current">
                  <a href="http://limb-project.com/development" onmouseover=
                  "menu.showMenu('menu3');">Development</a>
                  <div style="display: none;" class="dropdownmenu" id="menu3">
                    <ul>
                      <li>
                        <div>

                          <a href="http://limb-project.com/svn">Subversion</a>
                        </div>
                      </li>
                      <li>
                        <div>
                          <a href="http://jira.limb-project.com">Tickets</a>
                        </div>
                      </li>

                    </ul>
                  </div>
                </li>
                <li>
                  <a href="http://limb-project.com/documentation" onmouseover=
                  "menu.showMenu('menu4');">Documentation</a>
                  <div style="display: none;" class="dropdownmenu" id="menu4">
                    <ul>
                      <li>

                        <div>
                          <a href="http://wiki.limb-project.com">Wiki</a>
                        </div>
                      </li>
                      <li>
                        <div>
                          <a href="http://api.limb-project.com">API</a>
                        </div>

                      </li>
                      <li>
                        <div>
                          <a href="http://xref.limb-project.com">Cross
                          Reference</a>
                        </div>
                      </li>
                    </ul>
                  </div>

                </li>
                <li>
                  <a href="http://limb-project.com/download" onmouseover=
                  "menu.showMenu('menu5');">Download</a>
                  <div style="display: none;" class="dropdownmenu" id="menu5">
                    <ul>
                      <li>
                        <div>
                          <a href="http://pear.limb-project.com">PEAR
                          channel</a>

                        </div>
                      </li>
                      <li>
                        <div>
                          <a href=
                          "http://sourceforge.net/project/showfiles.php?group_id=109345">
                          SourceForge</a>
                        </div>
                      </li>

                      <li>
                        <div>
                          <a href="http://snaps.limb-project.com">Nightly
                          builds</a>
                        </div>
                      </li>
                    </ul>
                  </div>
                </li>

                <li>
                  <a href="http://projects.limb-project.com" onmouseover=
                  "menu.showMenu('menu6');">Projects</a>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>

      <div class="hr">
        <img src="images/1x1.gif" height="1" alt=""/>
      </div>
      <div class="center">
        <div id="container">
          <div id="page_content"> &raquo; <a href="/">Home</a>
            <?php if(!is_result_action()) { ?>
            <h1>Limb template engine benchmark</h1>
            <?php } else { ?>
            <h1>Benchmark results</h1>
            <?php } ?>
            <table width="100%" border="0" cellspacing="0" cellpadding="0"
            class="border1">
              <tr>
                <td>
                <?php
                $action = get_action();
                if('bench_all' != $action && 'bench_selected' != $action) {
                  ?>
                  <form action="" method="post">
                    <label for="iterations">Iterations:</label><input type="text" name="iterations" value="<?=BENCH_ITERATIONS_DEFAULT;?>" size="4"/>
                    <input type="submit" name="bench_selected" value="Compare selected"/>
                    <input type="submit" name="bench_all" value="Compare all"/>
                    <hr style="margin:10px 0"/>
                    <table>
                    <?php
                    foreach(get_engines() as $engine) {
                      echo '<tr>';
                      echo '<td><input type="checkbox" name="engines[]" value="'.$engine.'"/></td>';
                      echo "<td><a href=\"".get_engine_bench_url($engine)."\">$engine</a></td>\n";
                      echo "<td>".get_engine_version($engine)."</td>\n";
                      echo "<td>".get_engine_home_link($engine)."</td>\n";
                      echo "<td><small>".get_engine_description($engine)."</small></td>\n";
                      echo '</tr>';
                    }
                    ?>
                    </table>                    
                  </form>                  
                <?php } else {
                  if(!isset($_REQUEST['iterations']) || $_REQUEST['iterations'] > BENCH_ITERATIONS_MAX)
                    $iterations = BENCH_ITERATIONS_DEFAULT;
                  else
                    $iterations = (int) $_REQUEST['iterations'];
                    
                  $concurency = max($iterations * BENCH_CONCURENCY_FACTOR, BENCH_CONCURENCY_MIN);
                    
                  echo '<p><a href="'.get_bench_root_url().'">Back to template engines list</a></p>';                  
                  
                  if('bench_all' == $action)
                    $engines = get_engines();
                  else
                    $engines = $_REQUEST['engines'];
                    
                  $results = bench_engines($engines, $iterations, $concurency);
                  
                  $max_value = max($results);
                  
                  ?><table class="table">
                  <tr><th>name</th><th>rps</th><th>%</th></tr>
                  <?php foreach($results as $name=>$value) { ?>
                    <tr>
                      <td><a href="<?=get_bench_root_url().$name?>/main.php"><?=$name?></a></td>
                      <td><?=$value?></td>
                      <td><?=round($value/$max_value*100)?>%</td>
                    </tr>
                  <?php } ?>    
                  </table>
                  <p>Iterations count: <?=$iterations;?></p>
                  <p>Concurency: <?=$concurency;?></p>                  
                <?php } ?>
                <h3>Примечания:</h3>
                <ul>
                  <li>В роли опкод-кешера используется <a href="http://ru2.php.net/manual/ru/ref.apc.php">APC</a>.</li>
                  <li>Постфикс 'one_tpl' указывает, что шаблон один, и нет инклудов и враппов.</li>                
                  <li>macro_sl - версия <a href="http://wiki.limb-project.com/doku.php?id=limb3:ru:packages:macro">macro</a> - с простым локатором шаблонов.</li>
                  <li>macro_sl_boundled - версия <a href="http://wiki.limb-project.com/doku.php?id=limb3:ru:packages:macro">macro</a> - с простым локатором и объединенная в один файл.</li>
                </ul>
                <h3>You can get benchmark sources:</h3>
                <pre><code>svn co <a href="https://svn.limb-project.com/limb/misc/template_engines_bench/">https://svn.limb-project.com/limb/misc/template_engines_bench/</a></code></pre>
                </td>
              </tr>
            </table>
          </div>
        </div>
      </div>
      <div class="clearing"></div>
      <div id="bottom">
        <div id="bottom_menu">

          <div class="center">
            <ul>
              <li>Quick links:
              </li>
              <li>
                <a href="/download">Download</a>
              </li>
              <li>
                <a href="http://wiki.limb-project.com">Documentation(wiki)</a>

              </li>
              <li>
                <a href="http://forum.limb-project.com">Forum</a>
              </li>
              <li>
                <a href="http://jira.limb-project.com">Tickets</a>
              </li>
            </ul>

          </div>
        </div>
        <div class="center">
          <span id="copyright">Copyright © 2003-2007 BIT</span> <a href=
          "http://www.bit-creative.com/" target="_blank" title=
          "Бюро Информационных Технологий (ООО БИТ). Создание сайтов на собственной платформе Limb. Мультимедиа презентации. Дизайн."
          id="bit" name="bit"><img src="images/bit.gif" onmouseout=
          "this.src='images/bit.gif';" onmouseover=
          "this.src='images/bit_hover.gif';" alt=
          "Бюро Информационных Технологий (ООО БИТ). Создание сайтов на собственной платформе Limb. Мультимедиа презентации. Дизайн."
          width="80" height="21"/></a>
        </div>
      </div>
    </div>
  </body>

</html>
