

	<?php
	/*
	$var1 = ["AI", "Weapons"=>"aa", "Enhancements"];
	$var2 = ["AI", "Weapons"=>3, "AD"];
	
	var_dump(array_merge($var1, $var2));
	
	function array_merge_recursive_distinct ( array &$array1, array &$array2 )
	{
	  $merged = $array1;

	  foreach ( $array2 as $key => &$value )
	  {
		if ( is_array ( $value ) && isset ( $merged [$key] ) && is_array ( $merged [$key] ) )
		{
		  $merged [$key] = array_merge_recursive_distinct ( $merged [$key], $value );
		}
		else
		{
		  $merged [$key] = $value;
		}
	  }

	  return $merged;
	}

	exit;
	*/
	
	/*
		({)(\s*(\s*'(\w|\<|\>|_| )*',*)+),\s*(})
	
	*/

	include('RES/SCRIPTS/luaToPhp.php');
	
	
	
	function rrmdir($src) {
		if (file_exists($src)){
			$dir = opendir($src);
			while($dir !== false && false !== ( $file = readdir($dir)) ) {
				if (( $file != '.' ) && ( $file != '..' )) {
					$full = $src . '/' . $file;
					if ( is_dir($full) ) {
						rrmdir($full);
					}
					else {
						unlink($full);
					}
				}
			}
			closedir($dir);
			rmdir($src);
		}
	}
	
	function scandirVisible($dir){
		return array_diff(scandir($dir), array('..', '.'));
	}
	
	function prepareForConversion($string_bp){
		$string_bp = preg_replace('/--(.*)/', "", $string_bp);
		$string_bp = preg_replace('/#(.*)/', "", $string_bp);
		$string_bp = str_replace("'", '"', $string_bp);
		$string_bp = str_replace('Sound', '', $string_bp);
		//$string_bp = str_replace('UnitBlueprint', '', $string_bp);
		$string_bp = preg_replace ('/(<LOC.*>+)/', '', $string_bp);
		return $string_bp;
	}
	
	
	//echo '<style>html{background-color:white;}</style><div>';
	
	//	file_put_contents('test.json', json_encode(makePhpArray(prepareForConversion(file_get_contents('DATA/GAMEDATA/URL0001_unit.bp')))));
		//			exit;
	
	
	//GET EXTRACTION INFO AND PREPARE FALLBACK
	$toExtract = json_decode(file_get_contents('FILES.JSON'));
	
	
	//STEP 1 : UNZIP DATA
	$failed = 0;
	for ($h = 0; $h < sizeOf($toExtract); $h ++){
		$zip = new ZipArchive;
		if ($zip->open(''.($toExtract[$h]).'') === TRUE) {	
			for ($i=0; $i<$zip->numFiles;$i++) {
				$name = $zip->statIndex($i)['name'];
				if (strpos($name, '.bp') !== false){
					$zip->extractTo('DATA/_TEMP/'.$toExtract[$h].'/',$name);	//Ex : extracts "units.scd.3599" to /DATA/GAMEDATA/_TEMP/units.scd.3599
				}
			}
			$zip->close();
		} else {
			$failed++;
		}
	}
	if ($failed > 0){
		//echo $failed.' data file.s could not be extracted or updated. Falling back to default values.';
	}
	
	//STEP 2 : MERGING FILES
	$idsUnitsList = [];
	$dir = 'DATA/_TEMP/';
	//echo "<br><br>STEP 2 <br>"."Data files extractions to seek for :";
	//print_r($toExtract);
	//echo "<br>";
	
	foreach($toExtract as $fileFolder) { //For every PAK to use, like units.3599.scd or units.nx2
		$realPath = $dir.$fileFolder;
		//echo "Currently working on ".$realPath." <br>";
		$skipping = false;
		if (!is_dir($realPath)){
			//echo "Skipping <br>";
			continue;
		}
		
		$dirs = scandirVisible($realPath);
		$thisPakUnitsList = [];
		
		foreach($dirs as $thisDirectory){	//For every subfolder of the PAK, like "/units" or "/projectiles"
			
			echo 'working on dir '.$thisDirectory.'<br>';
			$unitList = scandirVisible($realPath.'/'.$thisDirectory);
			$thisSubfolderUnitsList = [];
			
			foreach($unitList as $thisUnit){ // For every unit inside this folder.
				//echo '--> unit '.$thisUnit.'<br>';
				
				$thisUnitDirectory = $realPath.'/'.$thisDirectory.'/'.$thisUnit;
				$thisUnitFile = $thisUnitDirectory.'/'.$thisUnit.'_unit.bp';
				
				if (file_exists($thisUnitFile)){
					$blueprint = file_get_contents($thisUnitFile);
					$blueprint = makePhpArray(prepareForConversion($blueprint));
					$blueprint['Id'] = $thisUnit;
					$thisSubfolderUnitsList[$thisUnit]= $blueprint;
				}
				
			}
			/*
			echo "PRE-REPLACE<br>";
			var_dump($thisPakUnitsList);
			echo "REPLACE WITH<br>";
			var_dump($thisSubfolderUnitsList);
			*/
			$thisPakUnitsList = array_merge($thisPakUnitsList, $thisSubfolderUnitsList);
			/*
			echo "POST-REPLACE<br>";
			var_dump($thisPakUnitsList);
			*/
		}
		
		$o = $idsUnitsList;
		$idsUnitsList = array_merge($idsUnitsList, $thisPakUnitsList);
		
	}
	
	//STEP 3 : MAKING JSON
	$finalUnitList = [];
	foreach($idsUnitsList as $thisUnit){
		$finalUnitList[]= $thisUnit;
	}
	file_put_contents('DATA/FALLBACK.JSON', json_encode($finalUnitList));
	
	//STEP 4 : CLEANING UP
	$files = scandir($dir);
	foreach($files as $unit) {
		rrmdir($dir.'/'.$unit);
	};
	
	//echo '</div>';
	
	//exit;
	
	?>
