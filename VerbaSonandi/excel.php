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
   
   $GLOBALS['db'] = $db;

	$GLOBALS['spec_letters'] = array(
	1 => array('¸' => 'å'),
	2 => array('' => ''),
	3 => array('a' => 'a', 'e' => 'e')
	);
      
	require_once 'Spreadsheet/Excel/Writer.php';

	// We give the path to our file here
	$workbook = new Spreadsheet_Excel_Writer('verbasonandi.xls');

	$worksheet =& $workbook->addWorksheet('verbasonandi');

	$worksheet->write(0, 0, 'Animal');
	$worksheet->write(0, 1, 'Verbe');

   
   //choose tables
	function choose_table($lang_id) {
	   if ($lang_id == 3) {
			$table_name = 'FrenchTable';
			$sql_anim = 'SELECT name from Animals WHERE id=';
			$sql_verb ='SELECT verb from Sounds WHERE id=';
	   } else {
			$table_name = 'SoundsTrans';
			$sql_anim ='SELECT name from AnimalNames WHERE lang_id='.$lang_id.' and animal_id=';
			$sql_verb ='SELECT verb from SoundsTrans WHERE lang_id='.$lang_id.' and verb_id=';
	   }
		return compact('table_name', 'sql_anim', 'sql_verb'); 
   }
	
	
	function animals($lang_id) {
		$array = choose_table($lang_id);
		$table_name = $array['table_name'];
		$sql_anim = $array['sql_anim'];
		$sql_verb = $array['sql_verb'];
		if ($lang_id == 3){
			$sql =<<<EOF
		SELECT DISTINCT animal_id from "$table_name";
EOF;
		} else {
			$sql =<<<EOF
		SELECT DISTINCT animal_id from "$table_name" WHERE lang_id=$lang_id;
EOF;
		}
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
		$f = 1;
		foreach ($anim as $a) {
			$let = mb_substr($a, 0, 1, 'UTF8');
			if ($let == $value || in_array($let, array_keys($GLOBALS['spec_letters'][$lang_id])) && $GLOBALS['spec_letters'][$lang_id][$let] == $value){
				$key = array_search($a, $anim_dict);
				if ($lang_id ==3) {
					$sql =<<<EOF
					SELECT * from "$table_name" WHERE animal_id=$key;
EOF;
					$ret = $GLOBALS['db']->query($sql); 
					$verbs = array();
					while($row = $ret->fetchArray(SQLITE3_ASSOC) ){
						array_push($verbs, $row['verb_id']);
					}
					for ($x = 0; $x < count($verbs); $x++) {
						$sql_v = <<<EOF
						$sql_verb $verbs[$x];
EOF;
						$ret_verb = $GLOBALS['db']->query($sql_v);
						$row_v = $ret_verb->fetchArray(SQLITE3_ASSOC);
						$verb_n = $row_v['verb'];
							$worksheet->write($f, 0, $a);
							$worksheet->write($f, 1, $verb_n);
							$f .= 1;
					}
				} else {
					$sql =<<<EOF
					SELECT * from "$table_name" WHERE animal_id=$key and lang_id=$lang_id;
EOF;
					$ret = $GLOBALS['db']->query($sql); 
					$x = 0;
					while($row = $ret->fetchArray(SQLITE3_ASSOC) ){
						$verb_n = $row['verb'];
							$worksheet->write($f, 0, $a);
							$worksheet->write($f, 1, $verb_n);
							$f .= 1;
					}
				}
			}
		}
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
		foreach ($letters as $value) {
			anim_verb($lang_id, $value, $anim, $anim_dict, $table_name, $sql_verb);
		}
	}
   
   
nav_sections($lang);
   


// We still need to explicitly close the workbook
$workbook->close();

$GLOBALS['db']->close();
?>