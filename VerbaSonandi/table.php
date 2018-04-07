<?php

	if (isset($_GET['cur-lang'])) {
		$lang = $_GET['cur-lang'];
	} else {
		$lang = 'français';
	}
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
   
   
   //choose tables
	function choose_table($lang_id) {
		$table_name = 'SoundsTrans';
		$sql_anim ='SELECT name from AnimalNames WHERE lang_id='.$lang_id.' and animal_id=';
		$sql_verb ='SELECT verb from SoundsTrans WHERE lang_id='.$lang_id.' and verb_id=';
		return compact('table_name', 'sql_anim', 'sql_verb'); 
   }
	
	
	function animals($lang_id) {
		$array = choose_table($lang_id);
		$table_name = $array['table_name'];
		$sql_anim = $array['sql_anim'];
		$sql_verb = $array['sql_verb'];
		$sql =<<<EOF
		SELECT DISTINCT animal_id from "$table_name" WHERE lang_id=$lang_id;
EOF;
		$ret = $GLOBALS['db']->query($sql);
		$anim = array();
		$letters = array();
		$anim_dict = array();
		while ($row = $ret->fetchArray(SQLITE3_ASSOC) ) {
			$anim_id = $row['animal_id'];
			$sql_a =<<<EOF
			$sql_anim $anim_id;
EOF;
			$ret_anim = $GLOBALS['db']->query($sql_a);
			$row = $ret_anim->fetchArray(SQLITE3_ASSOC);
			$name = $row['name'];
			$anim_dict[$anim_id] = $name;
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
		if ($lang_id == 2) {
			$key = array_search('c', $letters);
			$key2 = array_search('č', $letters);
			$c = array('č');
			unset($letters[$key2]);
			array_splice( $letters, $key+1, 0, $c);
		}
		if ($lang_id == 4) {
			$key = array_search('l', $letters);
			$key2 = array_search('ł', $letters);
			$l = array('ł');
			unset($letters[$key2]);
			array_splice( $letters, $key+1, 0, $l);
			$key = array_search('s', $letters);
			$key2 = array_search('ś', $letters);
			$s = array('ś');
			unset($letters[$key2]);
			array_splice( $letters, $key+1, 0, $s);
		}
		sort($anim);
		return compact('letters', 'anim', 'anim_dict', 'table_name', 'sql_verb');
   }
 
	
	function anim_verb($lang_id, $value, $anim, $anim_dict, $table_name, $sql_verb) {
		$sections = '';
		$anim_uniq = array_unique($anim);
		foreach ($anim_uniq as $a) {
			$let = mb_substr($a, 0, 1, 'UTF8');
			if ($let == $value || in_array($let, array_keys($GLOBALS['spec_letters'][$lang_id])) && $GLOBALS['spec_letters'][$lang_id][$let] == $value){
				$key = array_search($a, $anim_dict);
				$sql =<<<EOF
				SELECT * from Animals WHERE id=$key;
EOF;
				$ret = $GLOBALS['db']->query($sql); 
				$row = $ret->fetchArray(SQLITE3_ASSOC);
				$fr = $row['name'];
				$sections .= '<tr><td data-toggle="tooltip" data-placement="top" title="'.$fr.'">'.$a.'</td>';
				$sql =<<<EOF
				SELECT * from "$table_name" WHERE animal_id=$key and lang_id=$lang_id;
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
						$sections .= '<tr><td></td><td><a href="verb.php?verb='.urlencode($v).'" target="_blank">'.$v.'</a></td></tr>';
					}
				}
			}
		}
		return $sections;
	}
	  
    
     //choose lang of table
	function lang_of_table($lang) {
		//echo $lang;
		$sql =<<<EOF
			SELECT id from Languages WHERE name="$lang"; 
EOF;
		$ret = $GLOBALS['db']->query($sql);
		$lang_id = '';
		if($row = $ret->fetchArray(SQLITE3_ASSOC) ){
			$lang_id = $row['id'];
		}
		return $lang_id;
	}
	
	
	function nav_sections($lang) {
		$lang_id = lang_of_table($lang);
		$array = animals($lang_id);
		$letters = $array['letters'];
		$anim = $array['anim'];
		$anim_dict = $array['anim_dict'];
		$table_name = $array['table_name'];
		$sql_verb = $array['sql_verb'];
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
					<th>Animal</th>
					<th>Prédicat</th>
					</tr>
				</thead>
				<tbody>';
			$sections .= anim_verb($lang_id, $value, $anim, $anim_dict, $table_name, $sql_verb);
			$sections .= '</tbody></table></div></div></div></section>';
		}
		return compact('nav', 'sections');
	}
   
   $array = nav_sections($lang);
   $nav = $array['nav'];
   $sections = $array['sections'];
   
   $telech = '<a href="#" class="btn btn-default" onclick="window.open(\'telecharger.php?lang='.urlencode($lang).'\', \'newpage\', \'width=1200, height=1200\')">Télécharger</a>';
	
	
	function taxes($lang) {
		$table_name = 'Examples_'.$lang;
		for ($x = 1; $x <= 17; $x++) {
			$sql =<<<EOF
			SELECT * from "$table_name" WHERE tax_id = $x;
EOF;
			$ret = $GLOBALS['db']->query($sql);
			$infin = array();
			while ($row = $ret->fetchArray(SQLITE3_ASSOC) ) {
				$infin_id = $row['infin_id'];
				if ($infin_id != 0) {
					array_push($infin, $row['infin_id']);
				}
			}
			$infin = array_unique($infin);
			$sql =<<<EOF
			SELECT * from Taxonomie WHERE id = $x;
EOF;
			$ret = $GLOBALS['db']->query($sql);
			if ($row = $ret->fetchArray(SQLITE3_ASSOC) ) {
				$name = $row['name'];
			}
			$subjects["$name"] = count($infin);
		}
		$sum = array_sum($subjects);
		return $subjects;
	}
	
	
	function taxes_emo($lang, $t, $c) {
		$table_name = 'Examples_'.$lang;
		$sql =<<<EOF
		SELECT * from "$table_name" WHERE tax_id = 1;
EOF;
		$ret = $GLOBALS['db']->query($sql);
		$emo = array();
		while ($row = $ret->fetchArray(SQLITE3_ASSOC) ) {
			$em = $row['emo_id'];
			if ($em != '') {
				$sql1 =<<<EOF
				SELECT * from Emotion WHERE id = '$em';
EOF;
				$ret1 = $GLOBALS['db']->query($sql1);
				if ($row1 = $ret1->fetchArray(SQLITE3_ASSOC)) {
					if ($row1['ton'] == $t and $row1['category'] == $c) {
						array_push($emo, $em);
					}
				}
			}
		}	
		$emo = array_unique($emo);
		sort($emo);
		if ($emo != array()) {
		foreach ($emo as $e) {
			$sql_e =<<<EOF
			SELECT * from "$table_name" WHERE emo_id = '$e';
EOF;
			$ret_e = $GLOBALS['db']->query($sql_e);
			$infin = array();
			while ($row_e = $ret_e->fetchArray(SQLITE3_ASSOC) ) {
				$infin_id = $row_e['infin_id'];
				if ($infin_id != 0) {
					array_push($infin, $row_e['infin_id']);
				}
			}
			$infin = array_unique($infin);
			$sql =<<<EOF
			SELECT * from Emotion WHERE id = '$e';
EOF;
			$ret = $GLOBALS['db']->query($sql);
			if ($row = $ret->fetchArray(SQLITE3_ASSOC) ) {
				$name = $row['name'];
			}
			$subjects[$name] = count($infin);	
		}
		} else {
			$subjects = array();
		}
		return $subjects;
	}	


	function merge_data($data, $keys) {
		$values = array_keys($data);
		$data1 = array();
		foreach ($keys as $k) {
			if (!in_array($k, $values)) {
				$data1[$k] = 0;
			} else {
				$data1[$k] = $data[$k];
			}
		}
		return $data1;
	}
	
	
	function json_cb(&$item, $key) { 
		if (is_string($item)) $item = mb_encode_numericentity($item, array (0x80, 0xffff, 0, 0xffff), 'UTF-8'); 
	}

	function my_json_encode($arr){
    //convmap since 0x80 char codes so it takes all multibyte codes (above ASCII 127). So such characters are being "hidden" from normal json_encoding
		array_walk_recursive($arr, 'json_cb');
		return mb_decode_numericentity(json_encode($arr), array (0x80, 0xffff, 0, 0xffff), 'UTF-8');
	}

	$d = taxes($lang);
	$data = my_json_encode(array_values($d));
	$d_lab = my_json_encode(array_keys($d));
	
	$data_11 = taxes_emo($lang, 1, 1);
	$data_12 = taxes_emo($lang, 1, 2);
	$data_13 = taxes_emo($lang, 1, 3);
	$data_01 = taxes_emo($lang, 0, 1);
	$data_02 = taxes_emo($lang, 0, 2);
	$data_03 = taxes_emo($lang, 0, 3);

	$labels1 = array_keys($data_11 + $data_12 + $data_13);
	
	$data_11f = my_json_encode(array_values(merge_data($data_11, $labels1)));
	$data_12f = my_json_encode(array_values(merge_data($data_12, $labels1)));
	$data_13f = my_json_encode(array_values(merge_data($data_13, $labels1)));
	$labels1 = my_json_encode($labels1);
	
	$labels0 = array_keys($data_01 + $data_02 + $data_03);
	
	$data_01f = my_json_encode(array_values(merge_data($data_01, $labels0)));
	$data_02f = my_json_encode(array_values(merge_data($data_02, $labels0)));
	$data_03f = my_json_encode(array_values(merge_data($data_03, $labels0)));
	$labels0 = my_json_encode($labels0);
	
	
	$GLOBALS['db']->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="x-ua-compatible" content="ie=edge">

    <title>Tableau récapitulatif</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.6.0/css/font-awesome.min.css">
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/mdb.min.css" rel="stylesheet">
    <link href="css/style_table.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.0/Chart.min.js"></script>
     
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
                <a role="button" class="btn btn-default btn-sm" href="#animaux" style="font-size: .8rem;">Animaux</a>
				<?php echo $nav ?>
				<form class="form-inline pull-xs-right">
                	<a role="button" class="btn btn-default btn-sm" href="#graphe" style="font-size: .8rem;">Graphe</a>
            	</form>
			</ul>
        </div>
    </nav>
    

    <div class="container" id="animaux">
        <br><br><br><br>
    	<div class="container flex-center">
			<ul><li>
				<svg xmlns="http://www.w3.org/2000/svg"
					version="1.1"
					width="600" 
					height="130" >
					<text x="250" y="30" dy="0">
						<tspan x="300" dy=".6em" font-size="60">Tableau récapitulatif </tspan>
						<tspan x="290" dy="1.2em">des </tspan>
						<tspan font-style = "italic">verba sonandi</tspan>
						<tspan> par langue</tspan>
					</text>
				</svg>
			</li>
            <li>
				<form action="" id="myform">
				    <select name="cur-lang" onchange="$('#myform').submit()" class="selectpicker">
				    	<option value="français" <?php echo $lang == "français" ? "selected" : "" ?>>français</option>
						<option value="russe" <?php echo $lang == "russe" ? "selected" : "" ?>>russe</option>
						<option value="serbe" <?php echo $lang == "serbe" ? "selected" : "" ?>>serbe</option>
						<option value="polonais" <?php echo $lang == "polonais" ? "selected" : "" ?>>polonais</option>
					</select>
					<br><br>
				</form></li>
			<li><?php echo $telech ?></li>
        	</ul>
        </div>
		<div class="card-columns">
			<?php echo $sections ?>   
		</div>

		<section id="graphe">
			<div class="section-content" style="background-color: rgba(0, 0, 0, 0.3);">
				<canvas id="radarChart"></canvas>
			</div>
		</section>
		
		<section id="graphe">
			<div class="section-content" style="background-color: rgba(0, 0, 0, 0.3);">
				<canvas id="radarChart_pos"></canvas>
			</div>
		</section>
		
		<section id="graphe">
			<div class="section-content" style="background-color: rgba(0, 0, 0, 0.3);">
				<canvas id="radarChart_neg"></canvas>
			</div>
		</section>
		
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
    <script type="text/javascript" src="js/Chart.bundle.min.js"></script>


    <!-- Animations init-->
    <script>
        new WOW().init();
    </script>
    <script>
		$(function () {
  			$('[data-toggle="tooltip"]').tooltip()
		})
	</script>

	<script>
	//radar
		var dataset = <?php echo $data; ?>;
		var d_labels = <?php echo $d_lab; ?>;
		Chart.defaults.global.defaultFontColor = 'white';
		Chart.defaults.global.defaultFontSize = 14;
		var ctxR = document.getElementById("radarChart").getContext('2d');
		var myRadarChart = new Chart(ctxR, {
    		type: 'radar',
    		data: {
        		labels: d_labels,
        		datasets: [
            		{
                		label: "Répartition des verbes d'après l'émetteur et autres significations",
						backgroundColor: "rgba(43, 187, 173, 0.5)",
						borderColor: "rgba(28, 125, 115, 0.8)",
						borderWidth: 2,
						pointBackgroundColor: "rgb(33, 145, 134)",
						pointRadius: 3,
						pointHoverBackgroundColor: "rgb(19, 83, 77)",
						pointHoverRadius: 4,
                		data: dataset
            		},
        		]
    		},
    		options: {
        		responsive: true,
        		legend: {
            		labels: {
                		fontSize: 16
            		}
        		},
        		scale: {
    				gridLines: {
      					color: "rgb(179, 179, 179)"
    				},
    				angleLines: {
      					color: "rgb(179, 179, 179)"
    				},
    				ticks: {
      					beginAtZero: true,
      					backdropColor: "rgba(0, 0, 0, 0.3)"
    				},
    				pointLabels: {
      					fontSize: 15,
      					fontFamily: 'Helvetica'
    				}
  				},
    		}    
		});
	</script>
	
	
	<script>
	//radar
		var dataset_11 = <?php echo $data_11f; ?>;
		var dataset_12 = <?php echo $data_12f; ?>;
		var dataset_13 = <?php echo $data_13f; ?>;
		var labels = <?php echo $labels1; ?>;
		Chart.defaults.global.defaultFontColor = 'white';
		Chart.defaults.global.defaultFontSize = 14;
		var ctxR = document.getElementById("radarChart_pos").getContext('2d');
		var myRadarChart = new Chart(ctxR, {
    		type: 'radar',
    		data: {
        		labels: labels,
        		datasets: [
            		{
                		label: "Par rapport à soi-même",
						backgroundColor: "rgba(255, 255, 102, 0.5)",
						borderColor: "rgba(255, 255, 77, 0.8)",
						borderWidth: 2,
						pointBackgroundColor: "rgb(255, 255, 51)",
						pointRadius: 3,
						pointHoverBackgroundColor: "rgb(255, 255, 102)",
						pointHoverRadius: 4,
                		data: dataset_11
            		},{
                		label: "Par rapport à l'autre",
						backgroundColor: "rgba(230, 0, 0, 0.5)",
						borderColor: "rgba(255, 51, 0, 0.8)",
						borderWidth: 2,
						pointBackgroundColor: "rgb(255, 0, 0)",
						pointRadius: 3,
						pointHoverBackgroundColor: "rgb(255, 51, 51)",
						pointHoverRadius: 4,
                		data: dataset_12
            		},{
                		label: "D'anticipation",
						backgroundColor: "rgba(0, 204, 0, 0.5)",
						borderColor: "rgba(26, 255, 26, 0.8)",
						borderWidth: 2,
						pointBackgroundColor: "rgb(0, 230, 0)",
						pointRadius: 3,
						pointHoverBackgroundColor: "rgb(0, 255, 0)",
						pointHoverRadius: 4,
                		data: dataset_13
            		},
        		]
    		},
    		options: {
        		responsive: true,
        		legend: {
            		labels: {
                		fontSize: 16
            		}
        		},
        		scale: {
    				gridLines: {
      					color: "rgb(179, 179, 179)"
    				},
    				angleLines: {
      					color: "rgb(179, 179, 179)"
    				},
    				ticks: {
      					beginAtZero: true,
      					backdropColor: "rgba(0, 0, 0, 0.3)"
    				},
    				pointLabels: {
      					fontSize: 15,
      					fontFamily: 'Helvetica'
    				}
  				},
    		}    
		});
	</script>
	
	<script>
	//radar
		var dataset_01 = <?php echo $data_01f; ?>;
		var dataset_02 = <?php echo $data_02f; ?>;
		var dataset_03 = <?php echo $data_03f; ?>;
		var labels = <?php echo $labels0; ?>;
		Chart.defaults.global.defaultFontColor = 'white';
		Chart.defaults.global.defaultFontSize = 14;
		var ctxR = document.getElementById("radarChart_neg").getContext('2d');
		var myRadarChart = new Chart(ctxR, {
    		type: 'radar',
    		data: {
        		labels: labels,
        		datasets: [
            		{
                		label: "Par rapport à soi-même",
						backgroundColor: "rgba(255, 255, 102, 0.5)",
						borderColor: "rgba(255, 255, 77, 0.8)",
						borderWidth: 2,
						pointBackgroundColor: "rgb(255, 255, 51)",
						pointRadius: 3,
						pointHoverBackgroundColor: "rgb(255, 255, 102)",
						pointHoverRadius: 4,
                		data: dataset_01
            		},{
                		label: "Par rapport à l'autre",
						backgroundColor: "rgba(230, 0, 0, 0.5)",
						borderColor: "rgba(255, 51, 0, 0.8)",
						borderWidth: 2,
						pointBackgroundColor: "rgb(255, 0, 0)",
						pointRadius: 3,
						pointHoverBackgroundColor: "rgb(255, 51, 51)",
						pointHoverRadius: 4,
                		data: dataset_02
            		},{
                		label: "D'anticipation",
						backgroundColor: "rgba(0, 204, 0, 0.5)",
						borderColor: "rgba(26, 255, 26, 0.8)",
						borderWidth: 2,
						pointBackgroundColor: "rgb(0, 230, 0)",
						pointRadius: 3,
						pointHoverBackgroundColor: "rgb(0, 255, 0)",
						pointHoverRadius: 4,
                		data: dataset_03
            		},
        		]
    		},
    		options: {
        		responsive: true,
        		legend: {
            		labels: {
                		fontSize: 16
            		}
        		},
        		scale: {
    				gridLines: {
      					color: "rgb(179, 179, 179)"
    				},
    				angleLines: {
      					color: "rgb(179, 179, 179)"
    				},
    				ticks: {
      					beginAtZero: true,
      					backdropColor: "rgba(0, 0, 0, 0.3)"
    				},
    				pointLabels: {
      					fontSize: 15,
      					fontFamily: 'Helvetica'
    				}
  				},
    		}    
		});
	</script>
	

</body>
</html>