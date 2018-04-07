<?php

	
	$lang = urldecode($_GET['lang']);
	
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
		sort($anim);
		return compact('letters', 'anim', 'anim_dict', 'table_name', 'sql_verb');
   }
 
	
	function anim_verb($lang_id, $value, $anim, $anim_dict, $table_name, $sql_verb) {
		$sections = '';
		$anim_uniq = array_unique($anim);
		foreach ($anim_uniq as $a) {
			$let = mb_substr($a, 0, 1, 'UTF8');
			if ($let == $value || in_array($let, array_keys($GLOBALS['spec_letters'][$lang_id])) && $GLOBALS['spec_letters'][$lang_id][$let] == $value){
				$sections .= '<tr><td>'.$a.'</td>';
				$key = array_search($a, $anim_dict);
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
						$sections .= '<td>'.$v.'</td></tr>,';
						$x .= 1;
					} else {
						$sections .= '<tr><td></td><td>'.$v.'</td></tr>,';
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
		$sections = '<div class="section-content">
			<div class="container">
			<div class="card">
				<table class="table table-striped table-bordered table-sm">
				<thead class="thead-inverse">
					<tr>
					<th>Animal</th>
					<th>Verbe</th>
					</tr>
				</thead>
				<tbody>,';
		foreach ($letters as $value) {
			$upper = mb_strtoupper($value, 'UTF-8');
			$sections .= anim_verb($lang_id, $value, $anim, $anim_dict, $table_name, $sql_verb);
		}
		$sections .= '</tbody></table></div></div></div>';
		return $sections;
	}
   
   $sections = nav_sections($lang);
   
	$pieces = explode(",", $sections);
	$count = count($pieces);
	$mod = ($count-2) % 4;
	$x = (($count-2) - $mod) / 4;
	$new_sections = '';
	$f = 1;
	if ($mod == 0) {
		for ($i=1; $i<=4; $i++) {
			$end = $x*$i;
			$new_sections .= $pieces[0];
			for ($j=$f; $j<=$end; $j++){
				$new_sections .= $pieces[$j];
				$f = $end+1;
			}
			$new_sections .= $pieces[$count-1];
		}
	}
	else {
		$b = 4-$mod+1;
		for ($i=1; $i<=4; $i++) {
			if ($i >= $b) {
				$end = $x*$i+$i-1;
			} else {
				$end = $x*$i;
			}
			$new_sections .= $pieces[0];
			for ($j=$f; $j<=$end; $j++){
				$new_sections .= $pieces[$j];
				$f = $end+1;
			}
			$new_sections .= $pieces[$count-1];
		}
	}
	
	$GLOBALS['db']->close();
?>

<html lang="en">

<head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="x-ua-compatible" content="ie=edge">

    <title>Télécharger</title>

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.6.0/css/font-awesome.min.css">
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/mdb.min.css" rel="stylesheet">
    <link href="" rel="stylesheet">
	
	<style>
	.card-columns  {
    column-count: 4;
  }
  </style>
	
</head>

<body>

    <div class="container">
	<br><br>
	<h3> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
	Tableau des <i>verba sonandi</i> associés aux animaux en <?php echo $lang ?> </h3>
	<br><br>
	<div class="card-columns">
		<?php echo $new_sections ?>  
	</div>
	<div class="container">
		<a role="button" class="btn btn-default" href="excel.php" target="_blank">Télécharger Excel</a>
	</div>
	</div>

    <script type="text/javascript" src="js/jquery-2.2.3.min.js"></script>
    <script type="text/javascript" src="js/tether.min.js"></script>
    <script type="text/javascript" src="js/bootstrap.min.js"></script>
    <script type="text/javascript" src="js/mdb.min.js"></script>

</body>
</html>