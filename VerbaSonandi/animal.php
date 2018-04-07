<?php

	$animal = urldecode($_GET['animal']);

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
   
   $GLOBALS['db'] = $db;
   
   $GLOBALS['spec_letters'] = array(
	1 => array('ё' => 'е'),
	2 => array('' => ''),
	3 => array('â' => 'a', 'é' => 'e'),
	4 => array('' => '')
	);
   
	$lang = 'francais';
	$lang_id = 3;
	
	
	function langues($animal) {
		$sql =<<<EOF
		SELECT * from AnimalNames WHERE lang_id=3 and name="$animal";
EOF;
		$ret = $GLOBALS['db']->query($sql);
		$row = $ret->fetchArray(SQLITE3_ASSOC);
		$anim_id = $row['animal_id'];
		$sql =<<<EOF
		SELECT * from AnimalNames WHERE animal_id=$anim_id;
EOF;
		$ret = $GLOBALS['db']->query($sql);
		$langs = array();
		$letters = array();
		$lang_dict = array();
		while ($row = $ret->fetchArray(SQLITE3_ASSOC)) {
			$lang_id = $row['lang_id'];
			$sql_l =<<<EOF
			SELECT * from Languages WHERE id=$lang_id;
EOF;
			$ret_l = $GLOBALS['db']->query($sql_l);
			$row_l = $ret_l->fetchArray(SQLITE3_ASSOC);
			$name = $row_l['name'];
			$lang_dict[$lang_id] = $name;
			array_push($langs, $name); 
			$let = mb_substr($name, 0, 1, 'UTF8');
			if (in_array($let, array_keys($GLOBALS['spec_letters'][$lang_id]))) {
				array_push($letters, $GLOBALS['spec_letters'][$lang_id][$let]);
			} else {
				array_push($letters, $let);
			}
		}
		$letters = array_unique($letters);
		$langs = array_unique($langs);
		sort($letters);
		sort($langs);
		return compact('letters', 'langs', 'lang_dict', 'anim_id');
   }
 
	
	function content($value, $langs, $lang_dict, $anim_id) {
		$sections = '';
		foreach ($langs as $l) {
			$let = mb_substr($l, 0, 1, 'UTF8');
			$l_id = array_search($l, $lang_dict);
			if ($let == $value || in_array($let, array_keys($GLOBALS['spec_letters'][$l_id])) && $GLOBALS['spec_letters'][$l_id][$let] == $value){
				$sections .= '<tr><td>'.$l.'</td>';
				$sql_a =<<<EOF
				SELECT * from AnimalNames WHERE lang_id=$l_id and animal_id=$anim_id;
EOF;
				$ret_a = $GLOBALS['db']->query($sql_a);
				$anims = array();
				while ($row_a = $ret_a->fetchArray(SQLITE3_ASSOC)) {
					array_push($anims, $row_a['name']);
				}
				$y = 0;
				foreach ($anims as $a) {
					if ($y == 0) {
						$sections .= '<td>'.$a.'</td><td></td>';
						$y .= 1;
					} else {
						$sections .= '<tr><td></td><td>'.$a.'</td><td></td>';
					}	
					$sql =<<<EOF
					SELECT * from SoundsTrans WHERE animal_id=$anim_id and lang_id=$l_id;
EOF;
					$ret = $GLOBALS['db']->query($sql); 
					$verbes = array();
					while($row = $ret->fetchArray(SQLITE3_ASSOC) ){
						$t = $row['verb'];
						$sql_v =<<<EOF
						SELECT * from Verbs WHERE id='$t';
EOF;
						$ret_v = $GLOBALS['db']->query($sql_v);
						$row_v = $ret_v->fetchArray(SQLITE3_ASSOC);
						$v = $row_v['verb3sg'];
						array_push($verbes, $v);
					}
					$verbes = array_unique($verbes);
					sort($verbes);
					$x = 0;
					foreach ($verbes as $v) {
						if ($x == 0) {
							$sections .= '<td><a href="verb.php?verb='.urlencode($v).'" target="_blank">'.$v.'</a></td></tr>';
							$x .= 1;
						} else {
							$sections .= '<tr><td></td><td></td><td></td><td><a href="verb.php?verb='.urlencode($v).'" target="_blank">'.$v.'</a></td></tr>';
						}
					}
				}
			}
		}
		return $sections;
	}
	
	
	function nav_sections($animal) {
		$array = langues($animal);
		$letters = $array['letters'];
		$langs = $array['langs'];
		$lang_dict = $array['lang_dict'];
		$anim_id = $array['anim_id'];
		$nav = '';
		$sections = '';
		foreach ($letters as $value) {
			$upper = mb_strtoupper($value, 'UTF-8');
			$nav .= '<a type="button" class="btn btn-sq-xs btn-default" href="#'.$upper.'">'.$upper.'</a>';
			$sections .= '<section id="'.$upper.'">
			<div class="section-content">
			<div class="card">
				<h4 class="card-header text-xs-center">'.$upper.'</h4>
				<table class="table table-striped table-bordered table-sm">
				<thead class="thead-inverse">
					<tr>
					<th style="background-color: #00134d;">Langue</th>
					<th style="background-color: #00134d;">Animal</th>
					<th style="background-color: #00134d;">Ideophone</th>
					<th style="background-color: #00134d;">Prédicat</th>
					</tr>
				</thead>
				<tbody>';
			$sections .= content($value, $langs, $lang_dict, $anim_id);
			$sections .= '</tbody></table></div></div></section>';
		}
		return compact('nav', 'sections');
	}
   
   $array = nav_sections($animal);
   $nav = $array['nav'];
   $sections = $array['sections'];
   
   
   $letr0 = mb_strtoupper(mb_substr($animal, 0, 1, 'UTF-8'), 'UTF-8');
  // $animal[0] = mb_strtoupper(mb_substr($animal, 0, 1, 'UTF-8'), 'UTF-8');
   $letr1 = mb_substr($animal, 0, 1, 'UTF-8');
   $letr2 = mb_strtoupper('é', 'UTF-8');
   $animal = preg_replace('/' . $letr1 . '/', $letr0, $animal, 1);
  	
	$GLOBALS['db']->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="x-ua-compatible" content="ie=edge">

    <title><?php echo $animal ?></title>

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
				width="600" 
				height="100" >
				<a xlink:href="https://fr.wikipedia.org/wiki/<?php echo $animal ?>" target="_blank">
				<text x="250" y="30" dy="0">
					<tspan x="300" dy=".6em" font-size="60" style="stroke: #00134d;"><?php echo $animal; ?></tspan> <!--echo $letr1; echo $letr2 -->
				</text>
				</a>
			</svg>
        </div>
		<div class="card-columns">
		<?php echo $sections ?>  
		</div>
    </div>


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