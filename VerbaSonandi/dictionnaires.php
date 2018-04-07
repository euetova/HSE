<?php

	$word = urldecode($_GET['word']);
	$langs = urldecode($_GET['langs']);

	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);

    class MyDB extends SQLite3{
		function __construct(){
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
	3 => array('a' => 'a', 'e' => 'e')
	);
		
	function lang_id($lang) {
		$sql =<<<EOF
			SELECT id from Languages WHERE name="$lang"; 
EOF;
		$ret = $GLOBALS['db']->query($sql);
		$id = '';
		if($row = $ret->fetchArray(SQLITE3_ASSOC) ){
			$id = $row['id'];
		}
		return $id;
	}
	
	function animals_dct($word, $lang1_id, $lang2_id) {
		$sql_a =<<<EOF
		SELECT * from AnimalNames WHERE lang_id=$lang1_id and name="$word";
EOF;
		$ret_a = $GLOBALS['db']->query($sql_a);
		$w_id = array();
		while($row = $ret_a->fetchArray(SQLITE3_ASSOC) ) {
			array_push($w_id, $row['animal_id']);
			$w_id = array_unique($w_id);
		}
		$list = '<ul style="list-style-type:none">';
		foreach ($w_id as $w) {
			$sql_a =<<<EOF
			SELECT * from AnimalNames WHERE lang_id="$lang2_id" and animal_id=$w;
EOF;
			$ret_a = $GLOBALS['db']->query($sql_a);
			$row = $ret_a->fetchArray(SQLITE3_ASSOC);
			$list .= '<li><p style="font-size:18px;">'.$row['name'].'</p></li>';
		}
		$list .= '</ul>';
		return $list;
	}
	
	function verbs_dct($word, $lang1_id, $lang2_id, $column1, $column2) {
		$sql =<<<EOF
		SELECT * from Verbs WHERE lang_id=$lang1_id and infin="$word";
EOF;
		$ret = $GLOBALS['db']->query($sql);
		$row = $ret->fetchArray(SQLITE3_ASSOC);
		$v_id = $row['id'];
		$list = '<ul style="list-style-type:none; line-height: 0.9rem; padding-left: 30px">';
		$verb_trans = array();
		$sql =<<<EOF
		SELECT * from "$column1" WHERE infin_id=$v_id;
EOF;
		$ret = $GLOBALS['db']->query($sql);
		while ($row = $ret->fetchArray(SQLITE3_ASSOC) ) {
			$sent_id = $row['sent_id'];
			$sql2 =<<<EOF
			SELECT * from "$column2" WHERE sent_id=$sent_id;
EOF;
			$ret2 = $GLOBALS['db']->query($sql2);
			while($row2 = $ret2->fetchArray(SQLITE3_ASSOC) ){
				$v_id_trans = $row2['infin_id'];
				if ($v_id_trans != 0) {
					$sql3 =<<<EOF
				SELECT * from Verbs WHERE lang_id=$lang2_id and id=$v_id_trans;
EOF;
					$ret3 = $GLOBALS['db']->query($sql3);
					$row3 = $ret3->fetchArray(SQLITE3_ASSOC);
					$infin = $row3['infin'];
					array_push($verb_trans, $infin);
				}
				else {
					$trans_verb = $row2['trans_verb'];
					if ($trans_verb != 'None') {
						array_push($verb_trans, $trans_verb);
					}
				}
			}
		}
		$verb_trans = array_unique($verb_trans);
		foreach ($verb_trans as $v) {
			$list .= '<li><p style="font-size:18px;">&emsp;'.$v.'</p></li>';
		}
		$list .= '</ul>';
		return $list;
	}
	
	function make_animal_table($word, $column1, $column2){
		$table = '';
		$sql =<<<EOF
		SELECT * from "$column1" WHERE subject="$word";
EOF;
		$ret = $GLOBALS['db']->query($sql);
		while ($row = $ret->fetchArray(SQLITE3_ASSOC) ) {
			$table .= '<tr><td>'.$row['example'].'</td>';
			$sent_id = $row['sent_id'];
			$sql2 =<<<EOF
			SELECT * from "$column2" WHERE sent_id=$sent_id;
EOF;
			$ret2 = $GLOBALS['db']->query($sql2);
			$sents = array();
			while($row2 = $ret2->fetchArray(SQLITE3_ASSOC) ){
				array_push($sents, $row2['example']);
			}
			$x = 0;
			foreach ($sents as $s) {
				if ($x == 0) {
					$table .= '<td>'.$s.'</td></tr>';
					$x .= 1;
				} else {
					$table .= '<tr><td></td><td>'.$s.'</td></tr>';
				}
			}
		}
		return $table;
	}
	
	function make_verb_table($word, $column1, $column2){
		$table = '';
		$sql_inf =<<<EOF
		SELECT * from Verbs WHERE infin="$word";
EOF;
		$ret_inf = $GLOBALS['db']->query($sql_inf);
		$row_inf = $ret_inf->fetchArray(SQLITE3_ASSOC);
		$infin_id = $row_inf['id'];
		$sql =<<<EOF
		SELECT * from "$column1" WHERE infin_id=$infin_id;
EOF;
		$ret = $GLOBALS['db']->query($sql);
		while ($row = $ret->fetchArray(SQLITE3_ASSOC) ) {
			$table .= '<tr><td>'.$row['example'].'</td>';
			$sent_id = $row['sent_id'];
			$sql2 =<<<EOF
			SELECT * from "$column2" WHERE sent_id=$sent_id;
EOF;
			$ret2 = $GLOBALS['db']->query($sql2);
			$sents = array();
			while($row2 = $ret2->fetchArray(SQLITE3_ASSOC) ){
				array_push($sents, $row2['example']);
			}
			$x = 0;
			foreach ($sents as $s) {
				if ($x == 0) {
					$table .= '<td>'.$s.'</td></tr>';
					$x .= 1;
				} else {
					$table .= '<tr><td></td><td>'.$s.'</td></tr>';
				}
			}
		}
		return $table;
	}
	
	list($lang1, $lang2) = explode('-', $langs);
	$lang1_id = lang_id($lang1);
	$lang2_id = lang_id($lang2);

	
	$table = '<table class="table table-striped table-bordered table-sm">
				<thead class="thead-inverse">
					<tr>
					<th class="text-xs-center" width="50%">'.$lang1.'</th>
					<th class="text-xs-center" width="50%">'.$lang2.'</th>
					</tr>
				</thead>
				<tbody>';
	
	$column1 = 'Examples_'.$lang1;
	$column2 = 'Examples_'.$lang2;
	
	$sql =<<<EOF
		SELECT * from AnimalNames WHERE lang_id="$lang1_id";
EOF;
	$ret = $GLOBALS['db']->query($sql);
	$animals = array();
	while($row = $ret->fetchArray(SQLITE3_ASSOC) ) {
		array_push($animals, $row['name']);
		$animals = array_unique($animals);
	}
	if (in_array($word, $animals)) {
		$list = animals_dct($word, $lang1_id, $lang2_id);
		$table .= make_animal_table($word, $column1, $column2);
	} else {
		$sql_v =<<<EOF
		SELECT * from Verbs WHERE lang_id="$lang1_id";
EOF;
		$ret_v = $GLOBALS['db']->query($sql_v);
		$verbs = array();
		while($row = $ret_v->fetchArray(SQLITE3_ASSOC) ) {
			array_push($verbs, $row['infin']);
			$verbs = array_unique($verbs);
		}
		if (in_array($word, $verbs)) {
			$list = verbs_dct($word, $lang1_id, $lang2_id, $column1, $column2);
			$table .= make_verb_table($word, $column1, $column2);
		} else {
			$list = 'wrong word';
		}
	}
	$table .= '</tbody></table>';

	$GLOBALS['db']->close();
	
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="x-ua-compatible" content="ie=edge">

    <title>Dictionnaire</title>

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
      
	  <div class="container">
	  <br>
    <div class="container flex-center">
				<img src="img/verba.png" align="left">
	<ul>
			<!--<svg xmlns="http://www.w3.org/2000/svg"
				version="1.1"
				width="600" 
				height="60" >
				<text x="275" y="35" dy="0">
					<tspan>Verba sonandi</tspan>
				</text>
			</svg>-->
		<li>
			<form class="form-inline" action="dictionnaires.php" method="get">
				<div class="form-group">
					<select id="langs" name="langs" class="selectpicker"> <!-- form-control-->
					<option value="français-serbe">français -&gt; serbe</option>
					<option value="serbe-français">serbe -&gt; français</option>
					<option value="serbe-russe">serbe -&gt; russe</option>
					</select>
					</div>
					<br><br>
					<div class="form-group mx-sm-3">
						<input id="word" name="word" type="text" class="form-control" placeholder="Search for..." style="color:white; font-size: 1.5em;">
					</div>
					<div class="form-group">
						<button class="btn btn-default" type="submit">
							<i class="fa fa-search" aria-hidden="true"></i>
						</button>
					</div>
				</form>
		</li></ul>
        </div>
		<div class="container">
		<br>
            <div class="card">
                <h5 class="card-header text-xs-center"> Dictionnaire <?php echo $langs ?></h5>
                    <div class="card-block">
						<h4> <?php echo $word ?> </h4>
						<?php echo $list ?>
                    </div>
            </div>
            <div class="card">
                <h5 class="card-header text-xs-center"> Examples <?php echo $langs ?></h5>
						<?php echo $table ?> 
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