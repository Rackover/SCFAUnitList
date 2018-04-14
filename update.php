<?php
	
	/// If this environment variable exist, it should be the same as the GET_ key provided
	$keyName = 'UNITDB_UPGRADE_SECRET';
	
	if (getenv($keyName) !== false){
		
		if ($_GET['token'] != $_ENV[$keyName]){
			header('HTTP/1.1 403 Forbidden');
			exit;
		}
		
	}
	
	/*
		({)(\s*(\s*'(\w|\<|\>|_| )*',*)+),\s*(})
	
	*/
	require('RES/SCRIPTS/luaToPhp.php');
	
	function rrmdir($src) {
		if (file_exists($src)){
			// echo '<p>--> Found '.$src.' [Exists] </p>';	////////DEBUG
			
			if (is_dir($src)){
				$ls = scandirVisible($src);
				foreach($ls as $thisSub){
					if ($thisSub != "." && $thisSub != ".."){
						$full = $src.'/'.$thisSub;
						rrmdir($full);
					}
				}
			}
			else { 
				// echo '<p>--> Not a directory ("'.$src.'"), unlinking </p>';	////////DEBUG
				unlink($src);
			}
			
			// echo '<p>--> Removing source "'.$src.'" </p>';	////////DEBUG
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
	
	
	//GET EXTRACTION INFO AND PREPARE FALLBACK
	$toExtract = json_decode(file_get_contents('CONFIG/DATAFILES.JSON'));
	$toExtractLoc = json_decode(file_get_contents('CONFIG/LOCFILES.JSON'));
	
	$debug = false;
	
	if (isset($_GET['debug'])){
		$debug = $_GET['debug'];
		echo '<div style="color:orange;background-color:#111111;font-family:Consolas;padding:8px;">';
	}
	
	file_put_contents("CONFIG/UPDATE.TMP", "If this file is present, either the database is updating or the last update failed.");	
		
	// STEP 0 : DOWNLOAD DATA IF NEED
	
	if ($debug) echo '<p>STEP 0 ----- </p>';	////////DEBUG
	if (isset($_GET['version']) && $_GET['version'] != "local"){
	
		$version = $_GET['version'];
		$urlVar = "UNITDB_FILES_API_URL_FORMAT";
		$apiUrl = "https://api.faforever.com/featuredMods/0/files/%s";
		
		if (getenv($urlVar) !== false){
			$apiUrl = $_ENV[$urlVar];
		}
		
		$url = sprintf($apiUrl, $version);
		
		if ($debug){
			echo "<p>Using url ".$url."</p>";
		}
		
		
		$neededFiles = array("units.nx2", "projectiles.nx2", "loc.nx2");
		$jsonString = file_get_contents($url);
		$json = json_decode($jsonString, true);
		$files = $json["data"];
		$path = "DATA/GAMEDATA/";
		
		foreach($files as $thisFile){
			$name = $thisFile["attributes"]["name"];
			$md5 = $thisFile["attributes"]["md5"];
			$url = $thisFile["attributes"]["url"];
			
			if (in_array($name, $neededFiles)){
				if ($debug) echo "Downloading ".$name." from ".$url." [".$md5."]<br>";
				
				unlink($path.$name);
				file_put_contents($path.$name, fopen($url, 'r'));
				
				$sum = md5_file($path.$name);
				if ($sum != $md5){
					if ($debug) echo "=> MD5 MISMATCH !<br>";
					if ($debug) echo "==> Exiting.<br>";
					exit;
				}
				else{
					if ($debug) echo "=> MD5 OK !";
				}
			}
		}
	}
	
	
	
	//STEP 1 : UNZIP DATA
	
	if ($debug) echo '<p>STEP 1 ----- </p>';	////////DEBUG
	
	$failed = 0;
	for ($h = 0; $h < sizeOf($toExtract); $h ++){
		$zip = new ZipArchive;
		if ($zip->open(''.($toExtract[$h]).'') === TRUE) {	
			if ($debug) echo '<p>-> Opened archive '.$toExtract[$h].' and found '.($zip->numFiles).' files. </p>';	////////DEBUG
			for ($i=0; $i<$zip->numFiles;$i++) {
				$name = ($zip->statIndex($i)['name']);
				//if ($debug) echo '<p>--> Found file '.$name.'</p>';	////////DEBUG
				if (strpos($name, '.bp') !== false){
					if ($debug) echo '<p>---> Extracting '.$name.' to DATA/_TEMP/'.$toExtract[$h].'/ ...</p>';	////////DEBUG
					$success = $zip->extractTo('DATA/_TEMP/'.$toExtract[$h].'/',($name));	//Ex : extracts "units.scd.3599" to /DATA/GAMEDATA/_TEMP/units.scd.3599
					rename('DATA/_TEMP/'.$toExtract[$h].'/'.$name, 'DATA/_TEMP/'.$toExtract[$h].'/'.strtoupper($name));
					if (!$success){
						if ($debug) echo '<p>----> Extraction FAILED !</p>';	////////DEBUG
						if ($debug) echo '<p>----> Error : '.error_get_last()['message'].'</p>';	////////DEBUG
					}
				}
			}
			$zip->close();
		} else {
			if ($debug) echo '<p>-> FAILED opening archive '.$toExtract[$h].' </p>';	////////DEBUG
			$failed++;
		}
	}
	if ($failed > 0){
		if ($debug) echo '<p> -> '.$failed.' files could not be extracted. </p>';	////////DEBUG
	}
	
	//LOC -->
	$failed = 0;
	if ($debug) echo '<p>-> Opening LOC Files... </p>';	////////DEBUG
	foreach($toExtractLoc as $locArch){
			
		$zip = new ZipArchive;
		
		if ($zip->open(''.($locArch).'') === TRUE) {
			
			if ($debug) echo '<p>-> Opened LOC archive '.$locArch.' and found '.($zip->numFiles).' files. </p>';	////////DEBUG
			
			for ($i=0; $i<$zip->numFiles;$i++) {
				$name = $zip->statIndex($i)['name'];
				if (strpos($name, '.lua') !== false){
					$zip->extractTo('DATA/_TEMP/'.$locArch.'/', $name);
				}
			}
			
			$zip->close();
		} 
		
		else {
			if ($debug) echo '<p>-> FAILED opening LOC archive '.$locArch.' </p>';	////////DEBUG
			$failed++;
		}
	}
	if ($failed > 0){
		if ($debug) echo '<p> ->'.$failed.' LOC files could not be extracted. </p>';	////////DEBUG
	}
	//endof
	
	//STEP 2 : MERGING FILES
	if ($debug) echo '<p>------------ </p>';	////////DEBUG
	if ($debug) echo '<p>STEP 2 ----- </p>';	////////DEBUG
	$idsUnitsList = [];
	$finalLangs = [];
	$dir = 'DATA/_TEMP/';
	if (is_dir($dir)){
		if ($debug) echo '<p>-> Directory '.$dir.' found </p>';	////////DEBUG
		foreach($toExtract as $fileFolder) { //For every PAK to use, like units.3599.scd or units.nx2
			$realPath = $dir.$fileFolder;
			if ($debug) echo '<p>-> Working on '.$realPath.'</p>';	////////DEBUG
			
			$skipping = false;
			if (!is_dir($realPath)){
				if ($debug) echo '<p>--> No directory, SKIPPING </p>';	////////DEBUG
				continue;
			}
			$dirs = scandirVisible($realPath);
			$thisPakUnitsList = [];
			$totalFound = 0;
			$notFoundAfterX = 0;
			
			foreach($dirs as $thisDirectory){	//For every subfolder of the PAK, like "/units" or "/projectiles"
				
				$unitList = scandirVisible($realPath.'/'.$thisDirectory);
				$thisSubfolderUnitsList = [];
				$units = 0;
				
				foreach($unitList as $thisUnit){ // For every unit inside this folder.
					
					$thisUnitDirectory = $realPath.'/'.$thisDirectory.'/'.$thisUnit;
					
					$thisMissileFile = $thisUnitDirectory.'/'.$thisUnit.'_proj.bp';
					
					$thisUnit = strtoupper($thisUnit);
					$thisUnitFile = $thisUnitDirectory.'/'.$thisUnit.'_unit.bp';
					
					$proj = false;
			
					
					if (file_exists($thisMissileFile)){
						$proj = true;
						$file = $thisMissileFile;
					}
					else{
						$file = $thisUnitFile;
					}
					
					echo '--> Adding unit '.$thisUnit.' from '.$file.'...<br>';
					if (file_exists($file)){
						$blueprint = file_get_contents($file);
						$blueprint = makePhpArray(prepareForConversion($blueprint));
						//var_dump("3");
						$blueprint['Id'] = ($thisUnit);
						// var_dump("4");
						if ($proj){
							$blueprint['BlueprintType'] = 'ProjectileBlueprint';
						}
						else{
							$blueprint['BlueprintType'] = 'UnitBlueprint';
						}
						$thisSubfolderUnitsList[$thisUnit]= $blueprint;	//Key is ID
						$units++;
					}
					else{
						if ($debug) echo '---> File not found!<br>';
						$notFoundAfterX++;
					}
					
				}
				if ($debug) echo '<p>--> Found '.$units.' units in directory '.$thisDirectory.'</p>';	////////DEBUG
				if ($debug) echo '<p>--> Could not find '.$notFoundAfterX.' units</p>';	////////DEBUG
				$totalFound += $units;
				$thisPakUnitsList = array_merge($thisPakUnitsList, $thisSubfolderUnitsList);
			}
			
			//$o = $idsUnitsList;
			if ($debug) echo '<p>-> Total units found for pak '.$realPath.' : '.$totalFound.' </p>';	////////DEBUG
			$idsUnitsList = array_merge($idsUnitsList, $thisPakUnitsList);
			
		}
		
		//LOC
		$totalLines = 0;
		foreach($toExtractLoc as $locFolder){
			$realPath = $dir.$locFolder;
			
			if ($debug) echo '<p>-> Working on LOC '.$realPath.'</p>';	////////DEBUG
			
			if (!is_dir($realPath)){
				if ($debug) echo '<p>--> No directory, SKIPPING </p>';	////////DEBUG
				continue;
			}
			
			$dirs = scandirVisible($realPath);
			$thisPakLangs = [];
			
			foreach($dirs as $thisDirectory){	//For every subfolder of the PAK, like "/units" or "/projectiles"
				
				$langs = scandirVisible($realPath.'/'.$thisDirectory);
				$thisSubfolderLocList = [];
				$foundLines = 0;
				
				foreach($langs as $thisLang){ // For every LANG inside the folder
					$thisLang = strtoupper($thisLang);
					
					$thisLangDirectory = $realPath.'/'.$thisDirectory.'/'.$thisLang;
					$file = $thisLangDirectory.'/'.'strings_db.lua';
					
					if (file_exists($file)){
						$lines = file_get_contents($file);
						$lines = locfileToPhp($lines);
						$thisSubfolderLocList[$thisLang]= $lines;
						$foundLines++;
						//echo '--> Found lang '.$thisLang.'<br>';
					}
					
				}
				if ($debug) echo '<p>--> Found '.$foundLines.' locfiles in directory '.$thisDirectory.'</p>';	////////DEBUG
				$totalLines += $foundLines;
				$thisPakLangs = array_merge($thisPakLangs, $thisSubfolderLocList);
			}
			
			if ($debug) echo '<p>-> Total files found for LOC '.$realPath.' : '.$totalLines.' </p>';	////////DEBUG
			$finalLangs = array_merge($finalLangs, $thisPakLangs);
			
		}
	//ENDOF
	}
	else{
		if ($debug) echo '<p>'.$dir.' not found. EXITING !</p>';	////////DEBUG
		exit;
	}
					
	
	//STEP 3 : MAKING JSON
	if ($debug) echo '<p>------------ </p>';	////////DEBUG
	if ($debug) echo '<p>STEP 3 ----- </p>';	////////DEBUG
	
	$finalUnitList = [];
	foreach($idsUnitsList as $thisUnit){
		$finalUnitList[]= $thisUnit;
	}
	file_put_contents('DATA/FALLBACK.JSON', json_encode($finalUnitList));
	file_put_contents('DATA/LANG.JSON', json_encode($finalLangs));
	
	//STEP 4 : CLEANING UP
	
	if ($debug) echo '<p>------------ </p>';	////////DEBUG
	if ($debug) echo '<p>STEP 4 ----- </p>';	////////DEBUG
	
	if ($debug) echo '<p>-> Beginning '.$dir.' cleanup </p>';
	
	if (is_dir($dir)){
		$files = scandirVisible($dir);
		foreach($files as $unit) {
			if ($debug) echo '<p>-> Removing '.$dir.$unit.' </p>';	////////DEBUG
			rrmdir($dir.$unit);
		};
	}
	
	//exit;
	
	unlink("CONFIG/UPDATE.TMP");
	
	if ($debug) echo '<p>Unliked UPDATE.TMP - all operations complete.</p>';	////////DEBUG
	
	if ($debug) echo '</div>';	////////DEBUG
	
	
	?>

	<script>

		function hideUpdateMenu(){
			document.getElementById('updateMenu').style.display = "none";
		}

</script>
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
