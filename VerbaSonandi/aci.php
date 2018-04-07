<?php
	
   ini_set('display_errors', 1);
   ini_set('display_startup_errors', 1);
   error_reporting(E_ALL);
   
      class MyDB extends SQLite3
   {
      function __construct()
      {
         $this->open('animals_db.db');
      }
   }
   $db = new MyDB();
   if(!$db){
      echo $db->lastErrorMsg();
   } else {
      //echo "Opened database successfully<br>";
   }
   
   $GLOBALS['spec_letters'] = array(
	1 => array('ё' => 'е'),
	2 => array('' => ''),
	3 => array('â' => 'a', 'é' => 'e')
	);
	
	
	function animals($lang_id) {
		$sql =<<<EOF
		SELECT * from Animals;
EOF;
		$ret = $GLOBALS['db']->query($sql);
		$anim = array();
		$letters = array();
		while ($row = $ret->fetchArray(SQLITE3_ASSOC) ) {
			$name = $row['name'];
			array_push($anim, $name); 
			$let = mb_substr($name, 0, 1, 'UTF8');
			if (in_array($let, array_keys($GLOBALS['spec_letters'][$lang_id]))) {
				array_push($letters, $GLOBALS['spec_letters'][$lang_id][$let]);
			} else {
				array_push($letters, $let);
			}
		}
		$letters = array_unique($letters);
		sort($letters);
		sort($anim);
		return compact('letters', 'anim');
   }
 
	
	function anim_verb($lang_id, $value, $anim) {
		$sections = '';
		$anim_uniq = array_unique($anim);
		foreach ($anim_uniq as $a) {
			$let = mb_substr($a, 0, 1, 'UTF8');
			if ($let == $value || in_array($let, array_keys($GLOBALS['spec_letters'][$lang_id])) && $GLOBALS['spec_letters'][$lang_id][$let] == $value){
				$sections .= '<tr><td><a href="animal.php?animal='.urlencode($a).'" target="_blank">'.$a.'</a></td></tr>';
			}
		}
		return $sections;
	}
	
	
	function nav_sections($lang_id) {
		$array = animals($lang_id);
		$letters = $array['letters'];
		$anim = $array['anim'];
		$nav = '';
		$sections = '';
		foreach ($letters as $value) {
			$upper = mb_strtoupper($value, 'UTF-8');
			$nav .= '<a type="button" class="btn btn-sq-xs btn-default" href="#'.$upper.'">'.$upper.'</a>';
			$sections .= '<section id="'.$upper.'">
			<div class="section-content">
			<div class="container">
			<div class="card">
				<h4 class="card-header text-xs-center">'.$upper.'</h4>
				<table class="table table-striped table-bordered table-sm">
				<thead class="thead-inverse">
					<tr>
					<th class="text-xs-center" style="background-color: #00134d;">Animal</th>
					</tr>
				</thead>
				<tbody>';
			$sections .= anim_verb($lang_id, $value, $anim);
			$sections .= '</tbody></table></div></div></div></section>';
		}
		return compact('nav', 'sections');
	}
   
	$lang = 'français';
	$lang_id = 3;
   
   $array = nav_sections($lang_id);
   $nav = $array['nav'];
   $sections = $array['sections'];
   	
	$GLOBALS['db']->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="x-ua-compatible" content="ie=edge">

    <title>Animaux</title>

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.6.0/css/font-awesome.min.css">
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/mdb.min.css" rel="stylesheet">
    <link href="css/style_table.css" rel="stylesheet">
    
    
    <script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-90767668-1', 'auto');
  ga('send', 'pageview');

    </script>
	

	
</head>

<body>

    <!-- Yandex.Metrika counter -->
<script type="text/javascript">
    (function (d, w, c) {
        (w[c] = w[c] || []).push(function() {
            try {
                w.yaCounter42305059 = new Ya.Metrika({
                    id:42305059,
                    clickmap:true,
                    trackLinks:true,
                    accurateTrackBounce:true
                });
            } catch(e) { }
        });

        var n = d.getElementsByTagName("script")[0],
            s = d.createElement("script"),
            f = function () { n.parentNode.insertBefore(s, n); };
        s.type = "text/javascript";
        s.async = true;
        s.src = "https://mc.yandex.ru/metrika/watch.js";

        if (w.opera == "[object Opera]") {
            d.addEventListener("DOMContentLoaded", f, false);
        } else { f(); }
    })(document, window, "yandex_metrika_callbacks");
</script>
<noscript><div><img src="https://mc.yandex.ru/watch/42305059" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
<!-- /Yandex.Metrika counter -->
    
    
	 <nav class="navbar navbar-dark navbar-fixed-top scrolling-navbar">
		<div class="collapse navbar-toggleable-xs" id="collapseEx">
			<ul class="nav navbar-nav mx-auto">
				<?php echo $nav ?>
			</ul>
          </div>
        </nav>
    

    <div class="container">
	<br><br><br>
    <div class="container flex-center">
			<svg xmlns="http://www.w3.org/2000/svg"
				version="1.1"
				width="720" 
				height="130" >
				<text x="330" y="30" dy="0">
					<tspan x="380" dy=".6em" font-size="60" style="stroke: #00134d;">Animaux</tspan>
					<tspan x="370" dy="1.2em" font-size="40" style="stroke: #00134d;">cris et leur lexicalisation dans les langues </tspan></tspan>
				</text>
			</svg>
        </div>
		<div class="card-columns text-xs-center">
		<?php echo $sections ?>   
		</div>
    </div>


    <!--Footer-->
    <footer class="page-footer center-on-small-only mdb-color darken-4">
        <div class="container-fluid">
        </div>
    </footer>
    <!--/.Footer-->

    <script type="text/javascript" src="js/jquery-2.2.3.min.js"></script>
    <script type="text/javascript" src="js/tether.min.js"></script>
    <script type="text/javascript" src="js/bootstrap.min.js"></script>
    <script type="text/javascript" src="js/mdb.min.js"></script>

    <!-- Animations init-->
    <script>
        new WOW().init();
    </script>
    

</body>
</html>