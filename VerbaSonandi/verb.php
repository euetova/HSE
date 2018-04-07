<?php

$verb = urldecode($_GET['verb']);

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
   

	function make_table($item, $table_name, $infin_id) {
		$section = '';		
		foreach ($item as $sub) {
			$section .= '<tr><td><a href="animal.php?animal='.urlencode($sub).'" target="_blank">'.$sub.'</a></td>';
			$sql =<<<EOF
			SELECT * from "$table_name" WHERE infin_id=$infin_id and subject="$sub";
EOF;
			$ret = $GLOBALS['db']->query($sql); 
			$examples = array();
			while($row = $ret->fetchArray(SQLITE3_ASSOC) ){
				array_push($examples, $row['example']);
			}
			for ($x = 0; $x < count($examples); $x++) {
				if ($x == 0) {
					$section .= '<td>'.$examples[$x].'</td></tr>';
				} else {
					$section .= '<tr><td></td><td>'.$examples[$x].'</td></tr>';
				}
			}
		}
		return $section;
	}
    
   
   $sql_inf =<<<EOF
		SELECT * from Verbs WHERE verb3sg="$verb";
EOF;
	$ret_inf = $GLOBALS['db']->query($sql_inf);
	$row = $ret_inf->fetchArray(SQLITE3_ASSOC);
	$infin_id = $row['id'];
	$infin = $row['infin'];
	$lang_id = $row['lang_id'];
	
	$sql =<<<EOF
		SELECT name from Languages WHERE id=$lang_id; 
EOF;
	$ret = $GLOBALS['db']->query($sql);
	$row = $ret->fetchArray(SQLITE3_ASSOC);
	$lang = $row['name'];

	
	$section = '<section id="animal">
		<div class="section-content">
			<div class="card">
				<h3 class="card-header text-xs-center">'.$infin.'</h3>
				<table class="table table-striped table-bordered table-sm">
				<thead class="thead-inverse">
					<tr>
					<th class="text-xs-center">Animaux</th>
					<th class="text-xs-center">Example</th>
					</tr>
				</thead>
				<tbody>';
	
	
	$table_name = 'Examples_'.$lang;
	
	$sql =<<<EOF
	SELECT * from "$table_name" WHERE infin_id=$infin_id;
EOF;
	$ret = $GLOBALS['db']->query($sql);
	$subjects = array();
	while ($row = $ret->fetchArray(SQLITE3_ASSOC) ) {
		array_push($subjects, $row['subject']);
	}
	$subjects = array_unique($subjects);
	
	$animaux = array();
	$emetteurs = array();
	
	$sql =<<<EOF
	SELECT * from AnimalNames WHERE lang_id=$lang_id;
EOF;
	$ret = $GLOBALS['db']->query($sql);
	$anim = array();
	while ($row = $ret->fetchArray(SQLITE3_ASSOC) ) {
		array_push($anim, $row['name']);
	}
	$anim = array_unique($anim);
	
	foreach ($subjects as $sub) {
		if (in_array($sub, $anim)) {
			array_push($animaux, $sub);
		} else {
			array_push($emetteurs, $sub);
		}
	}
	
	sort($animaux);
	sort($emetteurs);
	
	$section .= make_table($animaux, $table_name, $infin_id);
	$section .= '</tbody></table>
    </div></div></section>
	
	
	</div><div class="container">
	<div class="autre">
	
 	<!--<div class="sticky">
        Graph
    </div> -->
  <div id="accordion" role="tablist" aria-multiselectable="true">
  <div class="card">
    <div class="card-header" role="tab" id="headingTwo">
      <h5 class="mb-0">
        <a class="collapsed" data-toggle="collapse" data-parent="#accordion" href="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
          Autres émetteurs
        </a>
      </h5>
    </div>
    <div id="collapseTwo" class="collapse" role="tabpanel" aria-labelledby="headingTwo">
	<table class="table table-striped table-bordered table-sm">
				<thead class="thead-inverse">
					<tr>
					<th class="text-xs-center">Émetteur</th>
					<th class="text-xs-center">Example</th>
					</tr>
				</thead>
				<tbody>';
		
	$section .= make_table($emetteurs, $table_name, $infin_id);
	$section .= '</tbody></table></div></div></div><!--<div class="xnav">
  <div class="xnav-wrapper">
 <div id="body">
	  <div id="chart"></div>
    </div>
  </div>
</div></div>-->';
	
	
	
	$sql =<<<EOF
	SELECT * from Taxonomie;
EOF;
	$ret = $GLOBALS['db']->query($sql);
	$tax = array();
	while ($row = $ret->fetchArray(SQLITE3_ASSOC) ) {
		$name = $row['name'];
		$tax[$name] = $row['id'];
	}
	
	function taxes($lang, $tax, $infin_id) {
		$table_name = 'Examples_'.$lang;
		$sql =<<<EOF
		SELECT * from "$table_name" WHERE infin_id=$infin_id and tax_id not NULL;
EOF;
		$ret = $GLOBALS['db']->query($sql);
		foreach(array_keys($tax) as $v) {
			$subjects[$v] = 0;
		}
		while ($row = $ret->fetchArray(SQLITE3_ASSOC) ) {
			$t = $row['tax_id'];
			if ($t != '0') {
				$key = array_search($t, $tax);
				$subjects[$key]++;
			}
		}
		$sum = array_sum($subjects);
		$param = '';
		foreach(array_keys($subjects) as $s) {
			if ($subjects[$s] != 0) {
				$subjects[$s] = $subjects[$s] / $sum;
			}
			$param .= '{axis:'.$s.',value:'.$subjects[$s].'},';
		}
		return $subjects;
	}
	
	function json_cb(&$item, $key) { 
		if (is_string($item)) $item = mb_encode_numericentity($item, array (0x80, 0xffff, 0, 0xffff), 'UTF-8'); 
	}

	function my_json_encode($arr){
    //convmap since 0x80 char codes so it takes all multibyte codes (above ASCII 127). So such characters are being "hidden" from normal json_encoding
		array_walk_recursive($arr, 'json_cb');
		return mb_decode_numericentity(json_encode($arr), array (0x80, 0xffff, 0, 0xffff), 'UTF-8');
	}
	

	$param1 = my_json_encode(taxes($lang, $tax, $infin_id));
	$var = '[['.$param1.']]';	
	
	$GLOBALS['db']->close();
?>

<html lang="en">

<head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="x-ua-compatible" content="ie=edge">

    <title>Verbe</title>

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.6.0/css/font-awesome.min.css">
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/mdb.min.css" rel="stylesheet">
    <link href="css/style_table2.css" rel="stylesheet">
	
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
	<script src="http://d3js.org/d3.v3.min.js"></script>
	<script src="RadarChart.js"></script>
</head>

<body>

    <div class="container">
		<?php echo $section ?>    
    </div>
	
	
	
	<script>
	$(document).ready(function() {
  var stickyTop = $('.sticky').offset().top;

  $(window).scroll(function() {
    var windowTop = $(window).scrollTop();
    if (stickyTop < windowTop && $(".autre").height() + $(".autre").offset().top - $(".sticky").height() > windowTop) {
      $('.sticky').css('position', 'fixed');
      $('.xnav').css('position', 'fixed');
    } else {
      $('.sticky').css('position', 'relative');
      $('.xnav').css('position', 'relative');
    }
  });
});

// new
$('.collapsed').on('click', showSticky);

function showSticky() {
	var stickyButton = $('.sticky');
	if (stickyButton.css('display') == "none") {
		stickyButton.css('display', 'block');
	} else {
		stickyButton.css('display', 'none');
	}
}
// new

$('.sticky').click(

  function() {
    $(".autre").toggleClass("mobile-menu-open");
    $(".xnav-wrapper").delay(500).queue(function(reset_scroll) {
      $(this).scrollTop(0);
      reset_scroll();
    });
  });
	</script>

	<script type="text/javascript">
		var p1 = <?php echo $param1; ?>;
	</script>
    <script type="text/javascript" src="script.js"></script>
	
	
    <script type="text/javascript" src="js/jquery-2.2.3.min.js"></script>
    <script type="text/javascript" src="js/tether.min.js"></script>
    <script type="text/javascript" src="js/bootstrap.min.js"></script>
    <script type="text/javascript" src="js/mdb.min.js"></script>

</body>
</html>