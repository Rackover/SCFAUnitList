

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
		//$string_bp = preg_replace ('/(<LOC.*>+)/', '', $string_bp);
		return $string_bp;
	}
	
	function locfileToPhp($locContent){
		$exp = explode("\n", $locContent);
		$finalLoc = [];
		foreach($exp as $line){
			$content = explode('=', $line);
			if (count($content) <= 1){
				continue;
			}
			$name = $content[0];
			$translation = $content[1];
			$translation = preg_replace("/(--\[\[(.*)--\]\]+)/", "", $translation);
			//$translation = str_replace('"', '', $translation);
			
			$finalLoc[$name] = $translation;
		}
		return $finalLoc;
	}
	
	
	//echo '<style>html{background-color:white;}</style><div>';
	
	//	file_put_contents('test.json', json_encode(makePhpArray(prepareForConversion(file_get_contents('DATA/GAMEDATA/URL0001_unit.bp')))));
		//			exit;
	
	
	//GET EXTRACTION INFO AND PREPARE FALLBACK
	$toExtract = json_decode(file_get_contents('CONFIG/DATAFILES.JSON'));
	$toExtractLoc = json_decode(file_get_contents('CONFIG/LOCFILES.JSON'));
	
	file_put_contents("CONFIG/UPDATE.TMP", "If this file is present, either the database is updating or the last update failed.");	
	set_time_limit(120);
	
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
	
	//LOC -->
	$failed = 0;
	foreach($toExtractLoc as $locArch){
			
		$zip = new ZipArchive;
		
		if ($zip->open(''.($locArch).'') === TRUE) {
			
			for ($i=0; $i<$zip->numFiles;$i++) {
				$name = $zip->statIndex($i)['name'];
				if (strpos($name, '.lua') !== false){
					$zip->extractTo('DATA/_TEMP/'.$locArch.'/', $name);
				}
			}
			
			$zip->close();
		} 
		
		else {
			$failed++;
		}
	}
	//endof
	
	//STEP 2 : MERGING FILES
	$idsUnitsList = [];
	$finalLangs = [];
	$dir = 'DATA/_TEMP/';
	if (is_dir($dir)){
		
		foreach($toExtract as $fileFolder) { //For every PAK to use, like units.3599.scd or units.nx2
			$realPath = $dir.$fileFolder;
			
			$skipping = false;
			if (!is_dir($realPath)){
				continue;
			}
			
			$dirs = scandirVisible($realPath);
			$thisPakUnitsList = [];
			
			foreach($dirs as $thisDirectory){	//For every subfolder of the PAK, like "/units" or "/projectiles"
				
				$unitList = scandirVisible($realPath.'/'.$thisDirectory);
				$thisSubfolderUnitsList = [];
				
				foreach($unitList as $thisUnit){ // For every unit inside this folder.
					$thisUnit = strtoupper($thisUnit);
					
					$thisUnitDirectory = $realPath.'/'.$thisDirectory.'/'.$thisUnit;
					$thisUnitFile = $thisUnitDirectory.'/'.$thisUnit.'_unit.bp';
					$thisMissileFile = $thisUnitDirectory.'/'.$thisUnit.'_proj.bp';
					$proj = false;
					
					if (file_exists($thisMissileFile)){
						$proj = true;
						$file = $thisMissileFile;
					}
					else{
						$file = $thisUnitFile;
					}
					
					if (file_exists($file)){
						$blueprint = file_get_contents($file);
						$blueprint = makePhpArray(prepareForConversion($blueprint));
						$blueprint['Id'] = ($thisUnit);
						if ($proj){
							$blueprint['BlueprintType'] = 'ProjectileBlueprint';
						}
						else{
							$blueprint['BlueprintType'] = 'UnitBlueprint';
						}
						$thisSubfolderUnitsList[$thisUnit]= $blueprint;	//Key is ID
						//echo '--> Found unit '.$thisUnit.'<br>';
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
			
			//$o = $idsUnitsList;
			$idsUnitsList = array_merge($idsUnitsList, $thisPakUnitsList);
			
		}
		
		//LOC
		foreach($toExtractLoc as $locFolder){
			$realPath = $dir.$locFolder;
			
			if (!is_dir($realPath)){
				continue;
			}
			
			$dirs = scandirVisible($realPath);
			$thisPakLangs = [];
			
			foreach($dirs as $thisDirectory){	//For every subfolder of the PAK, like "/units" or "/projectiles"
				
				$langs = scandirVisible($realPath.'/'.$thisDirectory);
				$thisSubfolderLocList = [];
				
				foreach($langs as $thisLang){ // For every LANG inside the folder
					$thisLang = strtoupper($thisLang);
					
					$thisLangDirectory = $realPath.'/'.$thisDirectory.'/'.$thisLang;
					$file = $thisLangDirectory.'/'.'strings_db.lua';
					
					if (file_exists($file)){
						$lines = file_get_contents($file);
						$lines = locfileToPhp($lines);
						$thisSubfolderLocList[$thisLang]= $lines;
						//echo '--> Found lang '.$thisLang.'<br>';
					}
					
				}
				$thisPakLangs = array_merge($thisPakLangs, $thisSubfolderLocList);
			}
			
			$finalLangs = array_merge($finalLangs, $thisPakLangs);
			
		}
	//ENDOF
	}
	
	//STEP 3 : MAKING JSON
	$finalUnitList = [];
	foreach($idsUnitsList as $thisUnit){
		$finalUnitList[]= $thisUnit;
	}
	file_put_contents('DATA/FALLBACK.JSON', json_encode($finalUnitList));
	file_put_contents('DATA/LANG.JSON', json_encode($finalLangs));
	
	//STEP 4 : CLEANING UP
	if (is_dir($dir)){
		$files = scandir($dir);
		foreach($files as $unit) {
			rrmdir($dir.'/'.$unit);
		};
	}
	
	//echo '</div>';
	
	//exit;
	
	unlink("CONFIG/UPDATE.TMP");
	
	?>

	
<div style="
		z-index:10;
		position:fixed;
		left:50%;
		top:25%;
		margin-left:-300px;
		color:#EEEEEE;
		background-color:#303030;
		border:1px solid white;
		width:600px;
		display:block;" id="updateMenu">
	<div style="font-family:Zeroes;text-align:center;width:100%;margin-top:8px;margin-bottom:16px;">
		Unit database has been updated.
	</div>
	<div class="flexRows" style="width:100%;text-align:center;margin-bottom:32px;">
		<div>
			<button style="
				font-family:Zeroes;
				color:#303030;
				background-color:#EEEEEE;
				width:30%;" onClick="hideUpdateMenu()">OK
			</button>
		</div>
	</div>
</div>	
