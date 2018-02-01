<?php 

	///////////////////////////////////////
	///									///
	///				UNITDB - v2			///
	///									///
	///////////////////////////////////////

	//Cookie management must be done before everything else for security reasons....html5
	

	//SPECIFICS SETTINGS LOAD
	$cookieName = str_replace(':','-',$_SERVER['REMOTE_ADDR']).'_'.md5( $_SERVER['HTTP_USER_AGENT']);
	$cookieName = "unitDB-settings";

	$defaultSettings = array(
		"showArmies"=>['Aeon','UEF','Cybran','Seraphim'],
		"previewCorner"=>"bottom left",
		"autoExpand"=>"0",
		"spookyMode"=>"0",
		"lang"=>"US"
	);
	$userSettings = $defaultSettings;

	if (isset($_GET["settings64"])){
		$userSettings = json_decode(base64_decode($_GET["settings64"]), true);
		if (is_array($userSettings)){
			$userSettings = array_replace($defaultSettings, $userSettings);
		}
	}
	if (isset($_COOKIE[$cookieName]) && (!isset($_GET["nocookies"]) || $_GET["nocookies"] != "1")){
		$userSettings = json_decode($_COOKIE[$cookieName], true);
		if (is_array($userSettings)){
			$userSettings = array_replace($defaultSettings, $userSettings);
		}
	}
	if (isset($_POST['settingsMod'])){
		foreach($defaultSettings as $key=>$thisSetting){
			if (!isset($_POST[$key])){
				$userSettings[$key] = $defaultSettings[$key];
			}
			else{
				switch ($key){
					default:
						$thisSetting = $_POST[$key];
						break;
					
					case "autoExpand":
					case "spookyMode":
						if (array_key_exists($key, $_POST) && $_POST[$key] == "on"){
							$thisSetting = "1";
						}
						else{
							$thisSetting = "0";
						}
						break;
						
					case "showArmies":
						$thisSetting = explode(",",$_POST[$key]);
						break;
				}
			}
			
			if ($key != "settingsMod"){
				$userSettings[$key] = $thisSetting;
			}
		}
		setcookie($cookieName, json_encode($userSettings), time()+86400*90);
	}
	
	//END OF
?>




<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
	<LINK href="STYLE.CSS?version=3" rel="stylesheet" type="text/css">
	<link rel="icon" href="favicon.ico" />
	<title>SCFA Unit list</title>

</head>
	<?php
		//clean url
		$strArgs = $_SERVER['QUERY_STRING'];
		
		if ($strArgs != ""){
			
			$argList = explode("&", $strArgs);
			
			$arguments = [];
			
			foreach($argList as $thisArgument){
				if ($thisArgument == ''){
					continue;
				}
				$thisExploded = explode("=", $thisArgument);
				$argName = $thisExploded[0];
				$argVal = $thisExploded[1];
				$arguments[$argName] = $argVal;
			}
			
			$finalArgString = '?';
			$iterator = 0;
			foreach($arguments as $key=>$value){
				if ($iterator > 0){
					$finalArgString .= "&";
				}
				$finalArgString .= $key."=".$value;
				$iterator++;
			}
			
			$cleanURL = $_SERVER['SCRIPT_NAME'].$finalArgString;
			
		}
		
		else{
			$cleanURL = $_SERVER['SCRIPT_NAME'];
		}
		//End of
		
		
		//url
		$url = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://".$_SERVER['HTTP_HOST'].$cleanURL."";
		
		$s = "?";
		if (count($_GET)){
			$s = "&";
		}
		
	?>
  <BODY>
	<script>
		function hideUpdateMenu(){
			document.getElementById('updateMenu').style.display = "none";
		}
		function toggleSettingsMenu (){
			let setMenu = document.getElementById('settingsMenu');
			if (setMenu.style.display == "block"){
				setMenu.style.display="none";
			}
			else{
				setMenu.style.display ="block";
			}
		}
		function openAllBlueprintsSections(id){
			let bpList = document.getElementsByClassName('unitBlueprints');
			let mode = false;
			if (document.getElementById(id).open){
				mode = true;
			}
			for (i = 0; i < bpList.length; i++) {
				if (bpList[i].id != id){
					if (mode){
						bpList[i].removeAttribute('open');
					}
					else{
						bpList[i].setAttribute('open', 'open');
					}
				}
			}
		}
		function removeUnitFromComparator(id){
			const unitList = getUrlVars()['id'];
			let unitArr = unitList.split(',');
			const index = unitArr.indexOf(id);
			if (index > -1){
				unitArr.splice(index, 1);
			}
			if (unitArr.length < 1){
				window.location.href = "<?php echo $url;?>";
			}
			else{
				seeUnit(unitArr.join(','));
			}
		}
		function getUrlVars() {
			var vars = {};
			var parts = window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m,key,value) {
			vars[key] = value;
			});
			return vars;
		}
		function seeUnit(id=null){ //Used to enter the comparator on click
			const checkList = document.getElementsByClassName('unitMainDiv');
			let idList = [];
			for (i = 0; i < checkList.length; i++) {
				if (checkList[i].classList.contains('unitSelected')){
					idList.push(checkList[i].id);
				}
			}
			if (id != null){
				idList.push(id);
			}
			id = idList.join();
			if (id == "" || id == null){
				id = -1;
			}
			window.location.href = "<?php echo $url.$s;?>id="+id;
		}
		function toggleSelect(div_id){
			let line = document.getElementById(div_id);
			if (line.classList.contains('unitSelected')){
				line.classList.remove('unitSelected');
			}
			else{
				line.classList.add('unitSelected');
			}
			checkForUnitsToCompare();
		}
		function checkForUnitsToCompare(){
			let selected = document.getElementsByClassName('unitMainDiv');
			let amount = 0;
			for (i = 0; i < selected.length; i++) {
				if (selected[i].classList.contains('unitSelected')){
					amount++;
				}
			}
			if (amount > 1){
				document.getElementById("comparatorPopup").removeAttribute('hidden');
			}
			else{
				document.getElementById("comparatorPopup").setAttribute('hidden', 'hidden');
			}
		}
		
		function openTab(class_button, id_button, class_tabClass, id_tabToOpen) { //Used to open or close tabs in the comparator;
			let i;
			let x = document.getElementsByClassName(class_tabClass);
			for (i = 0; i < x.length; i++) {
				x[i].style.display = "none"; 
			}
			document.getElementById(id_tabToOpen).style.display = "block"; 
			
			let tabs = document.getElementsByClassName(class_button);
			for (i = 0; i < tabs.length; i++) {
				tabs[i].removeAttribute("selected");
			}
			document.getElementById(id_button).setAttribute("selected", "selected");
		}
	</script>

<?php
	
	
	//SETTINGS LOAD
	
	$settingsString = "CONFIG/SETTINGS.JSON";
	$settings = array(
		"lastUpdate"=>time(),
		"every"=>86400
	);
	if (file_exists($settingsString)){
		$settings = json_decode(file_get_contents($settingsString), true);
	}

	if (($settings["lastUpdate"] + $settings["every"]) < time() ){
		if (!file_exists("CONFIG/UPDATE.TMP")){
			include("update.php");
			$settings["lastUpdate"] = time();
		}
		else{
			echo '
		<div style="width:100%;margin-bottom:8px;text-align:center;">
			<span style="color:cyan;">
				Database seems to be updating. This should take a few minutes and meanwhile, displayed information may be inaccurate. Report to the developpers if this message stays for long.
			</span>
		</div>';
		}
	}
	file_put_contents($settingsString, json_encode($settings));	
	//END OF
	
	
	$dataString = file_get_contents("DATA/FALLBACK.JSON");
	$dataFull = json_decode($dataString);
	$dataUnits = [];
	$dataMissiles = [];
	
	$locData = json_decode(file_get_contents("DATA/LANG.JSON"), true);
	
	foreach($dataFull as $thisUnit){
		if ($thisUnit->BlueprintType == "UnitBlueprint"){
			$dataUnits[]=$thisUnit;
		}
		else if ($thisUnit->BlueprintType == "ProjectileBlueprint"){
			$dataMissiles[]=$thisUnit;
		}
	}
	
	/////////////////////////////////
	///
	///		UNIT COMPARATOR MODE
	///
	/////////////////////////////////
	
	if (isset($_GET['id']) && $_GET['id'] != "-1"){
		$list = array_unique(explode(',', $_GET['id']));
		
		$toCompare = array();
		
		for ($i = 0; $i < sizeOf($dataUnits); $i++){
			$element = $dataUnits[$i];
			$id = $element->Id;
			
			foreach($list as $unit){
				if ($unit == $id){
					$toCompare[] = $element;
				}
			}
			//endof
			
		}
		
		
		$categories = ["Section_start", "Display", "Physics", "Intel", "Wreckage", "FACTORY", "Veteran", "Weapon", "Enhancements"];
		
		$components = [];
		
		
		foreach($toCompare as $thisUnit){
			foreach ($categories as $thisProperty){
				if (property_exists($thisUnit, $thisProperty) &&
					!in_array($thisProperty, $components)){
					$components [] = $thisProperty;
				}
			}
		}
		$components = array_slice (array_merge(($categories), $components), 0, sizeOf($categories));
		
		
		$components [] = "Section_end";	//Production is added to check if the unit is a factory, then to display the content it can build.
		
		echo '
			<div style="position:fixed;left:50%;bottom:10px;">
				<button id="comparatorPopup"						
						onClick="seeUnit(-1)">
					<< Back
				</button>
			</div>';
		
		echo '<div class="comparisonBoard">';
		
		foreach ($components as $thisComponent){
			
			echo '<div class="boardLane">';
			
			foreach($toCompare as $thisUnit){
			
				$description = "unit";
				if (property_exists($thisUnit, 'Description')){
					$description = ($thisUnit->Description);
					$matches = [];
					if (preg_match ('/(<LOC.*>+)/', $description, $matches)){
						$line = $matches[0];
						$line = str_replace('<LOC ', '', $line);
						$line = str_replace('>', '', $line);
						$thisLang = $locData[$userSettings['lang']];
						if (array_key_exists($line, $thisLang)){
							$description = $thisLang[$line];
						}
					}
				}
				$inf['Description'] = $description;
				$inf['Health'] = $thisUnit->Defense->Health;
				$inf['Regen'] = $thisUnit->Defense->RegenRate;
				$inf['Economy'] = $thisUnit->Economy;
				$inf['Faction'] = $thisUnit->General->FactionName;
				$inf['Id'] = strtoupper($thisUnit->Id);
				$inf['Strategic'] = $thisUnit->StrategicIconName;
				$inf['Display'] = $thisUnit->Display;
				$inf['Categories'] = $thisUnit->Categories;
					
				echo '<div class="unitCompared"
						style="background-color:'.getFactionColor($inf['Faction'], 'dark').';">';
				
				//ECONOMIC & NAME
				if ($thisComponent == "Section_start"){
					$nickname = '';
					if (property_exists($thisUnit->General, 'UnitName')){
						$nickname = '"'.($thisUnit->General->UnitName).'" ';
					}					
					
					//TITLE BAND
						echo '<div style="position:relative;">
								<div style="margin-right:27px;">
									'.getUnitTitle($thisUnit, $locData, $userSettings['lang']).'
								</div>
								<button style="
											color:red;
											background-color:rgba(0,0,0,0.25);
											position:absolute;
											right:0;
											top:0;" 
										onClick="removeUnitFromComparator(\''.($thisUnit->Id).'\')">
								X
								</button>';
						echo '</div>';
					//ENDOF
					
					
					//HEALTH BAR
						
						echo '<div class="healthBar" style="color:'.getFactionColor($inf['Faction'], 'bright').';">
								
								'.format($inf['Health']).'HP + '.$inf['Regen'].'/s
								
								</div>';
						
					//ENDOF
					
						
					//ECONOMY
					
						$nrgCost = 0;
						$mssCost = 0;
						$bldCost = 0;
						
						if (property_exists($inf['Economy'], 'BuildCostEnergy')) $nrgCost = $inf['Economy']->BuildCostEnergy;
						if (property_exists($inf['Economy'], 'BuildCostMass')) $mssCost = $inf['Economy']->BuildCostMass;
						if (property_exists($inf['Economy'], 'BuildTime')) $bldCost = $inf['Economy']->BuildTime;
						
						echo '<div class="sheetSection">
								<div class="smallTitle"  style="color:'.getFactionColor($inf['Faction'], 'bright').';">
									Build costs
									</div>
									
									<div class="flexColumns" style="padding-bottom:4px;">
										<div class="energyCost">
											<img alt="nrg" style="vertical-align:top;" src="IMG/ICONS/energy.png"> '.format($nrgCost).'
										</div>
										<div class="massCost">
											<img alt="mss" style="vertical-align:top;" src="IMG/ICONS/mass.png"> '.format($mssCost).'
										</div>
										<div class="buildTimeCost">
											<img alt="tim" style="vertical-align:top;" src="IMG/ICONS/time.png"> '.format($bldCost).'
										</div>
									</div>
								</div>';
								
						if (property_exists($inf['Economy'], 'ProductionPerSecondMass') ||
							property_exists($inf['Economy'], 'ProductionPerSecondEnergy') ||
							property_exists($inf['Economy'], 'MaintenanceConsumptionPerSecondEnergy') ||
							property_exists($inf['Economy'], 'MaintenanceConsumptionPerSecondMass') ||
							property_exists($inf['Economy'], 'BuildRate')){
								
								$build = 0;
								$nrg = 0;
								$mass = 0;
								
								if (property_exists($inf['Economy'], 'MaintenanceConsumptionPerSecondMass')) $mass -= $inf['Economy']->MaintenanceConsumptionPerSecondMass;
								if (property_exists($inf['Economy'], 'MaintenanceConsumptionPerSecondEnergy')) $nrg -= $inf['Economy']->MaintenanceConsumptionPerSecondEnergy;
								if (property_exists($inf['Economy'], 'BuildRate')) $build += $inf['Economy']->BuildRate;
								if (property_exists($inf['Economy'], 'ProductionPerSecondMass')) $mass += $inf['Economy']->ProductionPerSecondMass;
								if (property_exists($inf['Economy'], 'ProductionPerSecondEnergy')) $nrg += $inf['Economy']->ProductionPerSecondEnergy;
								
								if ($mass > 0){
									$mass = '<span class="positiveYield">'.$mass.'</span>';
								}
								else if ($mass < 0){
									$mass = '<span class="negativeYield">'.$mass.'</span>';
								}
								if ($nrg > 0){
									$nrg = '<span class="positiveYield">'.$nrg.'</span>';
								}
								else if ($nrg < 0){
									$nrg = '<span class="negativeYield">'.$nrg.'</span>';
								}
								if ($build > 0){
									$build = '<span class="positiveYield">'.$build.'</span>';
								}
								
								echo '<div class="sheetSection">
									<div class="smallTitle"  style="color:'.getFactionColor($inf['Faction'], 'bright').';">
									Yield / Drain
									</div>
									
									<div class="flexColumns" style="padding-bottom:4px;">
										<div class="energyCost">
											<img alt="nrg" style="vertical-align:top;" src="IMG/ICONS/energy.png"> '.$nrg.'
										</div>
										<div class="massCost">
											<img alt="mss" style="vertical-align:top;" src="IMG/ICONS/mass.png"> '.$mass.'
										</div>
										<div class="buildTimeCost">
											<img alt="tim" style="vertical-align:top;" src="IMG/ICONS/time.png"> '.$build.'
										</div>
									</div>
								</div>';
								
						}
								
						if (property_exists($inf['Economy'], 'StorageMass') ||
							property_exists($inf['Economy'], 'StorageEnergy')){
								
								$nrg = 0;
								$mass = 0;
								
								if (property_exists($inf['Economy'], 'StorageMass')) $mass += $inf['Economy']->StorageMass;
								if (property_exists($inf['Economy'], 'StorageEnergy')) $nrg += $inf['Economy']->StorageEnergy;
								
								echo '<div class="sheetSection">
									<div class="smallTitle"  style="color:'.getFactionColor($inf['Faction'], 'bright').';">
									Storage
									</div>
									
									<div class="flexColumns" style="padding-bottom:4px;">
										<div class="energyCost">
											<img alt="nrg" style="vertical-align:top;" src="IMG/ICONS/energy.png"> '.$nrg.'
										</div>
										<div class="massCost">
											<img alt="mss" style="vertical-align:top;" src="IMG/ICONS/mass.png"> '.$mass.'
										</div>
									</div>
								</div>';
								
						}
				}
				
				//ABILITIES
				else if ($thisComponent == "Display"){
					if (property_exists($inf['Display'], "Abilities")){
						
						$abilities = $inf['Display']->Abilities;
						
						echo '<div class="sheetSection">
								<div class="smallTitle"  style="color:'.getFactionColor($inf['Faction'], 'bright').';">
								Abilities
								</div>
								
								<div class="flexWrap">
									
									';
									
									foreach($abilities as $thisAb){
										
										echo '<div style="font-weight:bold;
															text-shadow: 1px 1px black;
															color:'.getFactionColor($inf['Faction'], 'bright').';"> 
											[
												'.attemptTranslation($thisAb, $locData, $userSettings['lang']).'
											]</div>';
										
									}
									
									echo '
									
								</div>
							</div>';
						
					}
				}
				
				//INTEL
				else if ($thisComponent == "Intel"){
					if (property_exists($thisUnit, $thisComponent)){
						
						$intel = $thisUnit->$thisComponent;
							
						echo '<div class="sheetSection">
								<div class="smallTitle"  style="color:'.getFactionColor($inf['Faction'], 'bright').';">
								'.$thisComponent.'
								</div>
								
								<div class="flexColumns">
									
									';
									if (property_exists($intel, 'VisionRadius')) echo '
										<div style="
											width:100%;
											font-weight:bold; 
											text-shadow: 1px 1px black;
											text-align:center;
											background-color:#660000;
											color:'.getFactionColor($inf['Faction'], 'bright').';">
											Vision : '.($intel->VisionRadius).'
										</div>';
									if (property_exists($intel, 'RadarRadius')) echo '
										<div style="
											width:100%;
											font-weight:bold; 
											text-shadow: 1px 1px black;
											text-align:center;
											background-color:#006666;
											color:'.getFactionColor($inf['Faction'], 'bright').';">
											Radar : '.($intel->RadarRadius).'
										</div>';
									if (property_exists($intel, 'SonarRadius')) echo '
										<div style="
											width:100%;
											font-weight:bold; 
											text-shadow: 1px 1px black;
											text-align:center;
											background-color:#003300;
											color:'.getFactionColor($inf['Faction'], 'bright').';">
											Sonar : '.($intel->SonarRadius).'
										</div>';
									echo '
									
								</div>
							</div>';
						
					}
				}
				
				//PHYSICS
				else if ($thisComponent == "Physics"){
					
					if (property_exists($thisUnit, 'Physics') &&
						(property_exists($thisUnit->Physics, 'TurnRate') && 
						$thisUnit->Physics->TurnRate > 0) ||
						(property_exists($thisUnit ,'Air'))){
							
							
						$physics = $thisUnit->Physics;
						$air = null;
						$titleString = "Physics";
						
						if (property_exists($thisUnit, 'Air')){
							
							$air = $thisUnit->Air;
							$titleString .= " / Air";
							
						}
						
						echo '<div class="sheetSection">
								<div class="smallTitle"  style="color:'.getFactionColor($inf['Faction'], 'bright').';">
								'.$titleString.'
								</div>';
								
							if ($air != null){
								echo '<div class="flexColumns">';
							}
						
							echo '
								<div class="flexRows">
									';
									if (property_exists($physics, 'TurnRate') && $physics->TurnRate > 0) echo '
										<div class ="info" style="color:'.getFactionColor($inf['Faction'], 'bright').';">
											Turn rate : '.($physics->TurnRate).' °/s
										</div>';
									if (property_exists($physics, 'MaxSpeed') && $physics->MaxSpeed > 0) echo '
										<div class ="info" style="color:'.getFactionColor($inf['Faction'], 'bright').';">
											Max speed : '.($physics->MaxSpeed).'
										</div>';
									if (property_exists($physics, 'FuelRechargeRate') && $physics->FuelRechargeRate > 0) echo '
										<div class ="info" style="color:'.getFactionColor($inf['Faction'], 'bright').';">
											Fuel refill rate: '.(($physics->FuelRechargeRate)).'
										</div>';
									if (property_exists($physics, 'FuelUseTime') && $physics->FuelUseTime > 0) echo '
										<div class ="info" 
											style="
											color:'.getFactionColor($inf['Faction'], 'bright').';">
											Fuel use time : '.($physics->FuelUseTime).'s
										</div>';
									if (property_exists($physics, 'LandSpeedMultiplier') && $physics->LandSpeedMultiplier != 1) echo '
										<div class ="info" style="color:'.getFactionColor($inf['Faction'], 'bright').';">
											LandSpeedMultiplier : '.floor(($physics->LandSpeedMultiplier)*100).'%
										</div>';
									echo '
									
								</div>';
								
								if ($air != null){
									echo '<div class="flexRows">
									';
									if (property_exists($air, 'TurnSpeed') && $air->TurnSpeed > 0) echo '
										<div class ="info" style="color:'.getFactionColor($inf['Faction'], 'bright').';">
											Turn speed : '.($air->TurnSpeed).'
										</div>';
									if (property_exists($air, 'MaxAirspeed') && $air->MaxAirspeed > 0) echo '
										<div class ="info" style="color:'.getFactionColor($inf['Faction'], 'bright').';">
											Max air speed : '.($air->MaxAirspeed).'
										</div>';
									if (property_exists($air, 'EngageDistance') && $air->EngageDistance > 0) echo '
										<div class ="info" style="color:'.getFactionColor($inf['Faction'], 'bright').';">
											Engage Distance: '.($air->EngageDistance).'
										</div>';
									if (property_exists($air, 'MinAirspeed') && $air->MinAirspeed > 0) echo '
										<div class ="info" style="color:'.getFactionColor($inf['Faction'], 'bright').';">
											Min air speed : '.($air->MinAirspeed).'
										</div>';
									if (property_exists($air, 'CombatTurnSpeed') && $air->CombatTurnSpeed > 0) echo '
										<div class ="info" style="color:'.getFactionColor($inf['Faction'], 'bright').';">
											CombatTurnSpeed : '.($air->CombatTurnSpeed).'
										</div>';
									if (property_exists($air, 'LayerTransitionDuration') && $air->LayerTransitionDuration > 0) echo '
										<div class ="info" style="color:'.getFactionColor($inf['Faction'], 'bright').';">
											LayerTransitionDuration : '.($air->LayerTransitionDuration).'s
										</div>';
									echo '
									</div>
								</div>';
								}
							
							echo '
							
							</div>';
						
					}
				}
				
				//WRECKAGE
				else if ($thisComponent == "Wreckage"){
					if (property_exists($thisUnit, "Wreckage")){
						echo '<div class="sheetSection">
							<div class="smallTitle"  style="color:'.getFactionColor($inf['Faction'], 'bright').';">
							'.$thisComponent.'
							</div>';
							
						echo ' 	<div class="flexColumns" style="color:'.getFactionColor($inf['Faction'], 'bright').';">';
							$hpMul = $thisUnit->Wreckage->HealthMult;
							$massMul = $thisUnit->Wreckage->MassMult;
							$hp = $inf['Health']*$hpMul;
							$mass = ($inf['Economy']->BuildCostMass) * $massMul;
							
							echo '	<div class="flexRows">
										<div class="info">
											HP
										</div>			
										<div class="info">
											Mass
										</div>								
									</div>
									<div class="flexRows">
										<div class="info">
											'.$hp.'
										</div>			
										<div class="info">
											'.$mass.'
										</div>								
									</div>
									<div class="flexRows">
										<div class="info">
											('.($hpMul*100).'%)
										</div>			
										<div class="info">
											('.($massMul*100).'%)
										</div>								
									</div>
							';
							
						echo '	</div>
							</div>';
					}
				}
				
				//VETERANCY
				else if (	$thisComponent == "Veteran" && 
							property_exists($thisUnit, 'Weapon') && 
							(sizeOf($thisUnit->Weapon) > 1 || (property_exists(array_values(get_object_vars ($thisUnit->Weapon))[0], "WeaponCategory") && 
																array_values(get_object_vars ($thisUnit->Weapon))[0]->WeaponCategory != "Death"))){
								
								
					///////////
					/// If this is a commander, display the custom veterancy system
					///////////
					echo '<div class="sheetSection">
					<div class="smallTitle"  style="color:'.getFactionColor($inf['Faction'], 'bright').';">
					Veterancy
					</div>';
					if (in_array("COMMAND", $inf['Categories']) ||		
						in_array("SUBCOMMANDER", $inf['Categories'])){
							
							$veterancies = ["T1"=>1, 
											"T2"=>0.5, 
											"T3"=>0.33333, 
											"T4"=>0.25, 
											"SACU"=>0.3, 
											"ACU"=>0.05];
											
							$firstSelection = "T1";
							
							echo '
								<div class="flexColumns tabZone">';
								
								
							foreach($veterancies as $vetName=>$vetFactor){
								
								$selected = "";
								if ($vetName == $firstSelection){
									$selected = 'selected=selected';
								}
								
								echo '<div 
										class="tabButton'.$inf['Id'].'" 
										id ="tabButton'.$vetName.$inf['Id'].'" 
										onClick="openTab(
												\'tabButton'.$inf['Id'].'\', 
												\'tabButton'.$vetName.$inf['Id'].'\',
												\''.$inf['Id'].'\',
												\''.$vetName.$inf['Id'].'\' )" 
												'.$selected.'>
													
											'.$vetName.'
											
										</div>';
							}
							
								
							echo '
								</div>';
							
							foreach($veterancies as $vetName=>$vetFactor){
								$display = '';
								if ($vetName != $firstSelection){
									$display = "style='display:none;'";
								}
								echo '
								<div class="'.$inf['Id'].' ACUVetZone" id="'.$vetName.$inf['Id'].'" '.$display.'>
									<div class="flexRows info" style="margin-left:8px; margin-right:8px; color:'.getFactionColor($inf['Faction'], "bright").';">';
									for ($i = 0; $i < 5; $i++){
										echo '
											<div class="flexColumns">		
												<div style="text-align:left;width:30%;">
													';
													
													for ($j = 0; $j < $i+1; $j++){
														echo '<img alt="X" src="IMG/ICONS/'.strtolower($inf['Faction']).'-veteran.png">';
													}
													
													echo'
												</div>		
												<div>
													<img alt="X" src="IMG/ICONS/mass.png" style="vertical-align:middle;">
													'.format(($i+1)*((($inf['Economy']->BuildCostMass)/2)/$vetFactor)).'
												</div>			
												<div style="font-weight:normal;">
													'.format($inf['Health'] +  $inf['Health']*(0.1*($i+1))).'HP+'.($inf['Regen'] + 3*($i+1)).'/s
												</div>
											</div>
											';
									}
								echo '</div>
								</div>
								';
							}
						}
						
					///////////
					/// Not a commander - also checks for existance of at least 1 weapon to display veterancy
					///////////
					else{
					echo '
							<div class="flexColumns tabZone">';
														
							echo '<div style="border-bottom:1px dotted grey;">
												
										(All units)
										
									</div>';
						
							
						echo '
							</div>';
						echo '
							<div class="ACUVetZone">
								<div class="flexRows info" style="margin-left:8px; margin-right:8px; color:'.getFactionColor($inf['Faction'], "bright").';">';
								for ($i = 0; $i < 5; $i++){
									echo '
										<div class="flexColumns">		
											<div style="text-align:left;width:30%;">
												';
												
												for ($j = 0; $j < $i+1; $j++){
													echo '<img alt="X" src="IMG/ICONS/'.strtolower($inf['Faction']).'-veteran.png">';
												}
												
												echo'
											</div>		
											<div style="width:30%;">
												<img alt="X" src="IMG/ICONS/mass.png" style="vertical-align:middle;">
												'.format(($i+1)*((($inf['Economy']->BuildCostMass)))).'
											</div>			
											<div style="width:40%;font-weight:normal;">
												'.format($inf['Health'] +  $inf['Health']*(0.1*($i+1))).'HP+'.($inf['Regen'] + 3*($i+1)).'/s
											</div>
										</div>
										';
								}
							echo '</div>
							</div>';
						
					}
					echo '</div>';
				}
				
				//WEAPONS
				else if ($thisComponent == "Weapon"){
					
					if (property_exists($thisUnit, "Weapon")){
						echo '<div class="sheetSection">
								<div class="smallTitle"  style="color:'.getFactionColor($inf['Faction'], 'bright').';">
								Weapons
								</div>';
						echo '	<div class="flexColumn" style="width:100%;color:'.getFactionColor($inf['Faction'], 'bright').';">';
						$weapons = $thisUnit->Weapon;
						foreach($weapons as $thisWeapon){
							if (property_exists($thisWeapon, 'DisplayName')){
								
								$autoOpen = '';
								if ($userSettings['autoExpand']){
									$autoOpen = 'open';
								}
								
								echo '<details class="weaponList" '.$autoOpen.'> 
										<summary  class="weaponCategory">
											'.($thisWeapon->DisplayName).'
										</summary>
										<div class="flexRows">';
										
								////GENERIC PROPERTIES TO DISPLAY	
									
								$propertiesToDisplay = ['SlavedToBodyArcRange', 'FiringRandomnessWhileMoving', 'FiringRandomness', 'MaxProjectileStorage'];
								$propertiesToDisplayGreaterThanZero = ['DamageRadius', 'MuzzleVelocity', 'FiringTolerance']; //DISPLAY ONLY IF GREATER THAN 0
									
								//END OF
								
								
								//SPECIFICS :
								if (property_exists($thisWeapon, 'Damage') &&
									$thisWeapon->Damage > 0){ 
									echo '<div class="flexColumns weaponLine">
											<div class="littleInfo" >
												Damage
											</div>
											<div class="littleInfoVar" >
												'.format($thisWeapon->Damage).'
											</div>
											';
									if (property_exists($thisWeapon, 'RateOfFire')){
										echo '<div class="littleInfo" style="text-align:right;border-left:1px dotted grey;">
												Firerate
											</div>
											<div class="littleInfoVar" >
												'.number_format (($thisWeapon->RateOfFire), 2).' /s 
											</div>';
									}
									echo '
										</div>';
								}	
								
								if (property_exists($thisWeapon,'ContinuousBeam') &&
									$thisWeapon->ContinuousBeam){
										echo '<div class="flexColumns weaponLine">
												<div class="littleInfo" >
													Fire cycle
												</div>
												<div class="littleInfoVar" >
													Continuous
												</div>
											</div>';
									}
								else if (property_exists($thisWeapon, 'RateOfFire') && 
									property_exists($thisWeapon, 'ProjectilesPerOnFire') &&
									$thisWeapon->ProjectilesPerOnFire > 1){ 
									echo '<div class="flexColumns weaponLine">
											<div class="littleInfo" >
												Fire cycle
											</div>
											<div class="littleInfoVar" >
												'.($thisWeapon->ProjectilesPerOnFire).' projectiles / shot
											</div>
										</div>';
								}		
								
								if (property_exists($thisWeapon, 'DamageType') &&
									$thisWeapon->DamageType != "Normal"){ 
									echo '<div class="flexColumns weaponLine">
											<div class="littleInfo" >
												DamageType
											</div>
											<div class="littleInfoVar" >
												'.($thisWeapon->DamageType).'
											</div>
										</div>';
									if ($thisWeapon->DamageType == 'DeathNuke' ||(
										property_exists($thisWeapon, 'NukeOuterRingRadius') &&
										property_exists($thisWeapon, 'NukeInnerRingDamage') &&
										property_exists($thisWeapon, 'NukeOuterRingDamage'))){
										echo '<div class="flexColumns weaponLine">
												<div class="littleInfo" >
													Nuke radius
												</div>
												<div class="littleInfoVar" >
													'.format($thisWeapon->NukeInnerRingRadius).'-'.format($thisWeapon->NukeOuterRingRadius).'
												</div>
											</div>';
										echo '<div class="flexColumns weaponLine">
												<div class="littleInfo" >
													Nuke damage
												</div>
												<div class="littleInfoVar" >
													'.format($thisWeapon->NukeOuterRingDamage).'-'.format($thisWeapon->NukeInnerRingDamage).'
												</div>
											</div>';
										
									}
								}		
								if (property_exists($thisWeapon, 'WeaponCategory')){ 
									$moreStyle = 'class="littleInfoVar" style="';
									switch($thisWeapon->WeaponCategory){
										case "Death":
											$moreStyle = 'class="littleInfoVar" style="color:grey;';
											break;
										case "Direct Fire Naval":
											$moreStyle = 'class="littleInfoVar" style="color:lightgreen;';
											break;
										case "Anti Navy":
											$moreStyle = 'class="littleInfoVar" style="color:green;';
											break;
										case "Defense":
											$moreStyle = 'class="littleInfoVar" style="color:DarkOrange ;';
											break;
										case "Teleport":
											$moreStyle = 'class="littleInfoVar" style="color:MediumPurple;';
											break;
										case "Anti Air":
											$moreStyle = 'class="littleInfoVar" style="color:Aqua;';
											break;
										case "Bomb":
											$moreStyle = 'class="littleInfoVar" style="color:DarkRed;';
											break;
										case "Death":
											$moreStyle = 'class="littleInfoVar" style="color:grey;';
											break;
										case "Artillery":
											$moreStyle = 'class="littleInfoVar" style="color:yellow;';
											break;
										case "Missile":
											$moreStyle = 'class="littleInfoVar" style="color:DarkKhaki ;';
											break;
										case "Kamikaze":
											$moreStyle = 'class="littleInfoVar" style="color:white ;';
											break;
										case "Direct Fire Experimental":
										case "Experimental":
											$moreStyle = 'class="littleInfoVar" style="color:red;';
											break;
									}
									echo '<div class="flexColumns weaponLine">
											<div class="littleInfo" style="width:50%;">
												WeaponCategory
											</div>
											<div '.$moreStyle.'width:50%;text-align:left;">
												'.($thisWeapon->WeaponCategory).'
											</div>
										</div>';
								}
									
								if (property_exists($thisWeapon, 'MaxRadius') &&
									$thisWeapon->MaxRadius > 0){ 
									echo '<div class="flexColumns weaponLine">
											<div class="littleInfo" >
												Range
											</div>
											<div class="littleInfoVar" >
											';
												if (property_exists($thisWeapon, 'MinRadius') &&
													$thisWeapon->MinRadius > 0){ 
														echo ($thisWeapon->MinRadius).'-';
												}
									echo '
												'.($thisWeapon->MaxRadius).'
											</div>
										</div>';
								}
									
								if (property_exists($thisWeapon, 'ProjectileId')){
									
									$foundArr = [];
									$found = preg_match('~(?<=projectiles\/).*(?=\/)~', $thisWeapon->ProjectileId, $foundArr);
									
									if ($found){
										
										$projectileId = $foundArr[0];
										
										if (strlen($projectileId) > 0){
											
											foreach($dataMissiles as $thisMissile){
												if ($thisMissile->Id == strtoupper($projectileId)){
													if (property_exists($thisMissile, 'Economy')){
														$eco = $thisMissile->Economy;
														echo '
													<div class="flexRows weaponLine" style="margin-bottom:4px;">
														<div class="littleInfo" style="text-align:center;" >
															Missile Cost
														</div>
														<div class="littleInfoVar"  style="text-align:center;margin:0px;"  >
														';
														
														echo '
														
														<div class="flexColumns" style="color:black;" style="text-align:center;" >
															<div class="energyCost">
																<img alt="nrg" style="vertical-align:top;" src="IMG/ICONS/energy.png"> '.format($eco->BuildCostEnergy).'
															</div>
															<div class="massCost">
																<img alt="mss" style="vertical-align:top;" src="IMG/ICONS/mass.png"> '.format($eco->BuildCostMass).'
															</div>
															<div class="buildTimeCost">
																<img alt="tim" style="vertical-align:top;" src="IMG/ICONS/time.png"> '.format($eco->BuildTime).'
															</div>
														</div>';
														
														echo '
													</div>
												</div>';
													}
													break;
												}
											}
											
										}
									}
								}
								
								
								
								//END OF
								
								//GENERICS :
								foreach($propertiesToDisplayGreaterThanZero as $thisProp){
									if (property_exists($thisWeapon, $thisProp) &&
										$thisWeapon->$thisProp > 0){
										echo '<div class="flexColumns weaponLine">
												<div class="littleInfo" style="border-left:1px dotted grey;">
													'.caseFormat($thisProp).'
												</div>
												<div class="littleInfoVar" >
													'.format($thisWeapon->$thisProp).'
												</div>
											</div>';
									}
								}	
								foreach($propertiesToDisplay as $thisProp){
									if (property_exists($thisWeapon, $thisProp)){
										echo '<div class="flexColumns weaponLine">
												<div class="littleInfo" style="border-left:1px dotted grey;">
													'.caseFormat($thisProp).'
												</div>
												<div class="littleInfoVar" >
													'.format($thisWeapon->$thisProp).'
												</div>
											</div>';
									}
								}	
								
								
								echo '	</div>
									</details>';
									
							}
						}
						echo '	</div>';
					echo '	</div>';
					}
				}
				
				//ENHANCMENTS
				else if ($thisComponent == "Enhancements"){
					if (property_exists($thisUnit, "Enhancements")){
						
						$enhancements = $thisUnit->Enhancements;
						$blacklist = ["Slots"];
						
						echo '<div class="sheetSection">
								<div class="smallTitle"  style="color:'.getFactionColor($inf['Faction'], 'bright').';">
								Enhancements
								</div>
								
								<div class="flexColumn" style="width:100%;color:'.getFactionColor($inf['Faction'], 'bright').';">
									
									';
									
						foreach($enhancements as $thisEnhancement){
							if (!in_array($thisEnhancement, $blacklist)
								&& property_exists($thisEnhancement, "Slot")
								&&!property_exists($thisEnhancement, 'RemoveEnhancements')){
									
								$autoOpen = '';
								if ($userSettings['autoExpand']){
									$autoOpen = 'open';
								}
								
								echo '		<details class="weaponList" '.$autoOpen.'>
												
												<summary  class="weaponCategory">
													<img 
													alt="X" 
													src="IMG/ENHANCEMENTS/'.$inf['Faction'].'/'.($thisEnhancement->Icon).'_btn_up.png"
													style="width:16px; 
															height:16px;
															vertical-align:middle;">
															
													['.($thisEnhancement->Slot).'] '.($thisEnhancement->Name).'
												</summary>
											<div class="flexRows">
											
												
												<div class="info"  style="font-weight:normal;color:'.getFactionColor($inf['Faction'], 'bright').';">
													Build costs
												</div>
												
												<div class="flexColumns" style="padding-bottom:4px;color:black;">
													<div class="energyCost">
														<img alt="nrg" style="vertical-align:top;" src="IMG/ICONS/energy.png"> '.format($thisEnhancement->BuildCostEnergy).'
													</div>
													<div class="massCost">
														<img alt="mss" style="vertical-align:top;" src="IMG/ICONS/mass.png"> '.format($thisEnhancement->BuildCostMass).'
													</div>
													<div class="buildTimeCost">
														<img alt="tim" style="vertical-align:top;" src="IMG/ICONS/time.png"> '.format($thisEnhancement->BuildTime).'
													</div>
												</div>';
												
								if (property_exists($thisEnhancement, 'ProductionPerSecondMass') ||
									property_exists($thisEnhancement, 'ProductionPerSecondEnergy') ||
									property_exists($thisEnhancement, 'MaintenanceConsumptionPerSecondEnergy') ||
									property_exists($thisEnhancement, 'MaintenanceConsumptionPerSecondMass') ||
									property_exists($thisEnhancement, 'BuildRate')){
										
										$build = 0;
										$nrg = 0;
										$mass = 0;
										
										if (property_exists($thisEnhancement, 'MaintenanceConsumptionPerSecondMass')) $mass -= $thisEnhancement->MaintenanceConsumptionPerSecondMass;
										if (property_exists($thisEnhancement, 'MaintenanceConsumptionPerSecondEnergy')) $nrg -= $thisEnhancement->MaintenanceConsumptionPerSecondEnergy;
										if (property_exists($thisEnhancement, 'BuildRate')) $build += $thisEnhancement->BuildRate;
										if (property_exists($thisEnhancement, 'ProductionPerSecondMass')) $mass += $thisEnhancement->ProductionPerSecondMass;
										if (property_exists($thisEnhancement, 'BuildRate')) $nrg += $thisEnhancement->BuildRate;
										
										echo '<div class="sheetSection">
											<div class="info"  style="font-weight:normal;color:'.getFactionColor($inf['Faction'], 'bright').';">
												Production / Consumption
											</div>
											
											<div class="flexColumns" style="padding-bottom:4px;color:black;">
												<div class="energyCost">
													<img alt="nrg" style="vertical-align:top;" src="IMG/ICONS/energy.png"> '.$nrg.'
												</div>
												<div class="massCost">
													<img alt="mss" style="vertical-align:top;" src="IMG/ICONS/mass.png"> '.$mass.'
												</div>
												<div class="buildTimeCost">
													<img alt="tim" style="vertical-align:top;" src="IMG/ICONS/time.png"> '.$build.'
												</div>
											</div>
										</div>';
										
								}
								
								//Now let's dump every enhancement info 
								$blacklist = ['Slot', 
												'BuildCostEnergy', 
												'BuildCostMass', 
												'MaintenanceConsumptionPerSecondEnergy', 
												'MaintenanceConsumptionPerSecondMass', 
												'ProductionPerSecondEnergy',
												'ProductionPerSecondMass',
												'BuildTime', 
												'Name', 
												'Icon',
												'ShieldRegenStartTime',
												'ShowBones',
												'UpgradeEffectBones',
												'UpgradeUnitAmbientBones',
												'HideBones',
												'BuildableCategoryAdds',
												'Prerequisite',
												'OwnerShieldMesh',
												'ImpactEffects',
												'ShieldEnergyDrainRechargeTime'];
								foreach($thisEnhancement as $propName=>$property){
									if (!in_array($propName, $blacklist)){
										echo '
									<div class="flexColumns" style="padding-bottom:6px;">';
										//NEW HEALTH
										if ($propName == "NewHealth"){
											echo '
										<div class="littleInfo">
											'.$propName.'
										</div>
										<div class="healthBar" style="height:auto;vertical-align:middle;margin:0px;">
											'.($inf['Health']+$property).'
										</div>';
										}
										//SHIELD Health
										else if ($propName == "ShieldMaxHealth"){
												echo '
										<div class="littleInfo">
											'.$propName.'
										</div>
										<div class="shieldBar">
											'.($property).'
										</div>';
										}
										//VARIOUS RATES
										else if ($propName == "ShieldRegenRate" ||
												$propName == "NewRateOfFire"){
												echo '
											<div class="littleInfo">
												'.$propName.'
											</div>
											<div class="littleInfoVar">
												'.$property.'/s
											</div>';
										}
										//HEALTH REGEN RATE
										else if ($propName == "NewRegenRate"){
												echo '
											<div class="littleInfo">
												'.$propName.'
											</div>
											<div class="littleInfoVar">
												+'.($inf['Regen']+$property).'/s
											</div>';
										}
										//Else
										else{
											echo '
										<div class="littleInfo">
											'.$propName.'
										</div>
										<div class="littleInfoVar">
											'.$property.'
										</div>';
										}
										echo '
									</div>';
									}
								}
								
								echo '
											
											</div>
										</details>';
							}
						}
							
							echo '
										
									</div>
								</div>';
								
					}
				}
				
				//ENDER
				else if ($thisComponent == "Section_end"){
					echo '<div class="sheetSection" style="border-bottom:1px solid white;height:8px;">
					</div>';
				}
				
				//FACTORY
				else if ($thisComponent == "FACTORY" && 
							/*
							(in_array("FACTORY", $thisUnit->Categories) ||
							in_array("ENGINEER", $thisUnit->Categories) ||
							in_array("ENGINEERSTATION", $thisUnit->Categories)) &&
							*/
							property_exists($thisUnit->Economy, 'BuildableCategory')){
					$buildable = [];
					foreach($dataUnits as $unit){
						foreach($thisUnit->Economy->BuildableCategory as $buildableCategory){
							$buildableRequirements = explode(' ', $buildableCategory);
							$canBuild = true;
							foreach($buildableRequirements as $requirement){
								if (!in_array($requirement, $unit->Categories) && strtoupper($requirement) != strtoupper($unit->Id)){
									$canBuild = false;
								}
							}
							if ($canBuild){
								$buildable[] = $unit;
							}
						}
					}
					
					$buildable = array_unique ($buildable, SORT_REGULAR);
					
					if (sizeOf($buildable) > 0){
						
						$autoOpen = '';
						if ($userSettings['autoExpand']){
							$autoOpen = 'open';
						}
								
						echo '
						<details class="sheetSection unitBlueprints" id="blueprintsSection'.($thisUnit->Id).'" onClick="openAllBlueprintsSections(\'blueprintsSection'.($thisUnit->Id).'\')" '.$autoOpen.'>
							<summary class="smallTitle"  style="color:'.getFactionColor($inf['Faction'], 'bright').';">
								Blueprints
							</summary>';
						foreach($buildable as $buildableUnit){
							
							if (property_exists($buildableUnit, 'StrategicIconName')){
								$icon = ($buildableUnit->StrategicIconName)."_rest";
							}
							echo '
							<div class="flexRows">
								<div class="flexColumns unit unitMainDiv">
									<div
										onclick="seeUnit(\''.($buildableUnit->Id).'\')">
										<img src="IMG/STRATEGIC/'.$icon.'.png" style="filter: contrast(70%) sepia(300%) brightness(150%) hue-rotate('.(getFactionColor($buildableUnit->General->FactionName, 'hue')-63).'deg) saturate(300%) brightness(70%) contrast(200%) brightness(130%);" alt="[x]">
									</div>';
							
							
							$description = (getTech($buildableUnit)).(attemptTranslation($buildableUnit->Description, $locData, $userSettings['lang']));
							$name = '';
							if (property_exists($buildableUnit->General, 'UnitName')){
								$name = attemptTranslation($buildableUnit->General->UnitName, $locData,$userSettings['lang']);
							}
							echo '
									<div class="unitName" style="color:'.getFactionColor(($buildableUnit->General->FactionName), "bright").'; width:100%;"
										onclick="seeUnit(\''.($buildableUnit->Id).'\')">
										'.($description).'
										<span style="
											font-weight:bold; 
											font-style:italic;
											color:'.getFactionColor(($buildableUnit->General->FactionName), "normal").'">'.
											($name).'
										</span>
									</div>';
							
							echo '</div>
							</div>';
						}
						echo '
						</details>';
					}
				}
				
				echo '</div>';
			}
			echo '</div>
			';
		}
		
		echo '</div>
			<div style="height:48px;">
			</div>';
	}
	
	
	
	
	/////////////////////////////////
	///
	///		UNIT LIST MODE (no GET data)
	///
	/////////////////////////////////
	
	else{
			
		echo '
			<div style="position:fixed;left:50%;
	z-index:5;bottom:10px;">
				<button id="comparatorPopup" hidden	
						onClick="seeUnit()">
					Compare units...
				</button>
			</div>';

		$armies = [];

		$finalData = [];
		
		
		//PRE-SORT TO HAVE THE CORRECT CATEGORY ORDER
			
		$categoriesOrder = array('Command'=>null, 
							'Engineer'=>null, 
							'Building - Factory'=>null, 
							'Building - Economy'=>null, 
							'Building - Weapon'=>null, 
							'Building - Defense'=>null, 
							'Building - Sensor'=>null, 
							'Aircraft'=>null, 
							'Vehicle'=>null, 
							'Naval'=>null, 
							'Support'=>null,
							'Civilian & Miscellanous'=>null);
		
		//END OF 

		for ($i = 0; $i < sizeOf($dataUnits); $i++){
			
			$item = $dataUnits[$i];
			
			/*
			if (!property_exists($item, 'StrategicIconName') ||
				!property_exists($item, 'General') ||
				!property_exists($item->General, 'Icon')){
				continue;
			}
			*/
			
			$faction = ucfirst(strtolower($item->General->FactionName));
			if (strtolower($faction) == "uef"){	//
				$faction = strtoupper($faction);// Exception
			}									//
			if (!in_array($faction, $armies) && in_array($faction, $userSettings['showArmies'])){
				$armies[] = ($faction);
			}
						
			//CATEGORIZING
			
			$itemCat = $item->Categories;
			
			$tech = getTech($item);
			
			//////////////
			//	CIVILIAN
			/////////////
			if (in_array('OPERATION', $item->Categories) || 
				in_array('CIVILIAN', $item->Categories) ||
				in_array('INSIGNIFICANTUNIT', $item->Categories)){
					
				$finalData['Civilian & Miscellanous'][""][$faction][] = $item;
			}
			
			///////////////
			//	COMMANDER & SACU
			//////////////
			else if (in_array ('COMMAND', $itemCat)
				|| in_array ('SUBCOMMANDER', $itemCat)){
				$finalData['Command'][$tech][$faction][] = $item;
			}
			///////////////
			//	ENGINEER
			//////////////
			else if (in_array ('ENGINEER', $itemCat) ||
						in_array('PODSTAGINGPLATFORM', $itemCat)){
				$finalData['Engineer'][$tech][$faction][] = $item;
			}
			///////////////
			//	AIRCRAFT
			//////////////
			else if (in_array('MOBILE', $itemCat) && 
						(in_array ('AIR', $itemCat))){
				$finalData['Aircraft'][$tech][$faction][] = $item;
			}
			///////////////
			//	VEHICLE
			//////////////
			else if (in_array('MOBILE', $itemCat) && 
						(in_array ('LAND', $itemCat))){
				$finalData['Vehicle'][$tech][$faction][] = $item;
			}
			///////////////
			//	SHIP
			//////////////
			else if (in_array('MOBILE', $itemCat) && 
						(in_array ('NAVAL', $itemCat))
						&& !in_array('MOBILESONAR', $itemCat)){
				$finalData['Naval'][$tech][$faction][] = $item;
			}
			///////////////
			//	SUPPORT
			//////////////
			else if (in_array ('WALL', $itemCat) ||
					 in_array ('AIRSTAGINGPLATFORM', $itemCat) ||
					 in_array ('ORBITALSYSTEM', $itemCat) 
					 ){
				$finalData['Support'][$tech][$faction][] = $item;
			}
			///////////////
			//	BUILDING - FACTORY
			//////////////
			else if (in_array('STRUCTURE', $itemCat) && 
					in_array ('FACTORY', $itemCat)){
				$finalData['Building - Factory'][$tech][$faction][] = $item;
			}
			///////////////
			//	BUILDING - ECONOMY
			//////////////
			else if (in_array('STRUCTURE', $itemCat) && 
					in_array ('ECONOMIC', $itemCat)){
				$finalData['Building - Economy'][$tech][$faction][] = $item;
			}
			///////////////
			//	BUILDING - WEAPON
			//////////////
			else if (in_array('STRUCTURE', $itemCat) && 
						(in_array('NUKE', $itemCat) ||
						in_array ('INDIRECTFIRE', $itemCat) ||
						in_array ('DIRECTFIRE', $itemCat))){
				$finalData['Building - Weapon'][$tech][$faction][] = $item;
			}
			///////////////
			//	BUILDING - DEFENSE
			//////////////
			else if (in_array('STRUCTURE', $itemCat) && 
						(in_array ('DEFENSE', $itemCat) ||
						in_array('COUNTERINTELLIGENCE', $itemCat) ||
						in_array('ANTINAVY', $itemCat) ||
						in_array('ANTIAIR', $itemCat))
						){

				$finalData['Building - Defense'][$tech][$faction][] = $item;
			}
			///////////////
			//	BUILDING - SENSOR
			//////////////
			else if ((in_array('STRUCTURE', $itemCat) || in_array('MOBILESONAR', $itemCat)) && 
						(in_array ('SORTINTEL', $itemCat) ||
						in_array('SONAR', $itemCat))){
				$finalData['Building - Sensor'][$tech][$faction][] = $item;
			}
			
			//Dumping all the rest in "misc"
			else{
				
				$finalData['Civilian & Miscellanous'][""][$faction][] = $item;
			}
			
			
			//print_r($item->Description);
			
			//ENDOF
			
		}

		//ORDERING DATA
		
		foreach($categoriesOrder as $categoryName => $nullData){
		
			foreach($finalData as $catName => $categories){
				
				if ($catName == $categoryName){
					$categoriesOrder[$categoryName] = $categories;
				}
				
			}
		
		}
		
		$finalData = $categoriesOrder;
		
		//ENDOF	
		

		///////////////////////////////////////
		///									///
		///	At this point, armies should be	///
		///	ready for display.              ///
		///									///
		///////////////////////////////////////

		
		$techOrder = ['', 'T1 ', 'T2 ', 'T3 ', 'Experimental '];
		
		
		///DISPLAY THE BIG HEADER / TITLE
		
		echo '<div style="
			display: flex;
			border:1px solid black;
			justify-content:space-between;
			flex-direction: row;" >';
			
		foreach($armies as $armyName){
			// #
			$loadFile = 'IMG/FACTIONAL/'.strtolower($armyName).'_load.jpg';
			if (!file_exists($loadFile)){
				$loadFile = 'IMG/FACTIONAL/default_load.jpg';
			}
			echo '
				<div class="flexColumns title" style="
					background-repeat: no-repeat;
					background-image:url(\''.$loadFile.'\');
					background-size: cover;
					background-position: center;
					color:'.getFactionColor($armyName, "bright").';
					width:100%;
					font-family:Zeroes;
					padding-left:8px;
					text-shadow: 2px 2px black;
					text-align:left;">
					
					<div style="width:40%;">
					'.$armyName.'
					</div>
					<div style="text-align:left;width:60%;">
					</div>
				</div>';
			
		}
			
		echo '</div>';
		//ENDOF
		
		
		//BEGINNING unit DRAW
		
		echo '<div style="
			display: flex;
			border:1px solid black;
			justify-content:space-between;
			flex-direction: column;" >';

			
		foreach($finalData as $catName => $categories){
			
			//$catName = strtoupper($catName[0]).strtolower(substr($catName, 1));
			
			$open = "open";
			if ($catName == "Civilian & Miscellanous"){
				$open = "";
			}
			
			echo '
			<details '.$open.'>
				<summary class="categoryName">
					'.$catName.'
				</summary>';
			
			echo '<div style="
				display: flex;
				justify-content:space-between;
				flex-direction: column;" >';
				
			$categories = array_merge(array_flip($techOrder), $categories);
				
			foreach($categories as $techName => $techLevel){
				
				if (!is_array($techLevel)){
					continue;
				}
				
				echo '<div style="
					display: flex;
					justify-content:flex-start;
					flex-direction: row;
					width:100%;">';
				
				
				foreach($armies as $armyName){
					//echo '<span style="color:red;">'.$armyName.'</span>';
					
					$border = '';
					
					if ($techName != "T1 " && $techName != ""){
						$border = 'border-top: 1px dotted grey;';
					}				
					
					
					$style = 'classicStyle';
					if ($userSettings['spookyMode']){
						$style = 'spookyStyle';
					}
					
					echo '<div class="'.$style.'"
						style="
						'.$border.'
						background-color:'.getFactionColor($armyName, "bright").'" >';
								
					
					if (array_key_exists($armyName, $techLevel)){
						$thisArmy = $techLevel[$armyName];			
					}
					else{
						echo '</div>';
						continue;
					}
					foreach($thisArmy as $thisUnit){
						
						if (property_exists($thisUnit, 'Id')){
							$id = $thisUnit->Id;
							
							$position = 'left:0px;bottom:0px;';
							switch ($userSettings['previewCorner']){
								case "Top left":
									$position = 'left:0px;top:0px;';
									break;
									
								case "Top right":
									$position = 'right:0px;top:0px;';
									break;
									
								case "Bottom right":
									$position = 'right:0px;bottom:0px;';
									break;
							}
							echo '<div class="unitMainDiv tooltip"
											id="'.($thisUnit->Id).'" 
											onClick="toggleSelect(\''.($thisUnit->Id).'\')">';
							if ($userSettings['previewCorner'] != "None"){
								echo '
									<div class="tooltiptext" style="'.$position.'opacity:0.85; background-color:'.getFactionColor($thisUnit->General->FactionName, "dark").';">
										'.getUnitTitle($thisUnit, $locData, $userSettings['lang']).'
									</div>';
							}
							if ($userSettings['spookyMode']){
								$terrain = 'none';
								$strategic = 'none';
								$description = 'unit';
								if (property_exists($thisUnit, 'StrategicIconName')){
									$strategic = $thisUnit->StrategicIconName;
								}
								if (property_exists($thisUnit->General, 'Icon')){
									$terrain = $thisUnit->General->Icon;
								}
								if (property_exists($thisUnit, 'Description')){
									$description = ($thisUnit->Description);
									$matches = [];
									if (preg_match ('/(<LOC.*>+)/', $description, $matches)){
										$line = $matches[0];
										$line = str_replace('<LOC ', '', $line);
										$line = str_replace('>', '', $line);
										$thisLang = $locData[$userSettings['lang']];
										if (array_key_exists($line, $thisLang)){
											$description = str_replace('"', '', $thisLang[$line]);
										}
									}
								}
								echo '
									<div>
										<div class="previewImg">
											<img alt="" class="backgroundIconOverlap" src="IMG/PREVIEW_BACKGROUND/'.($terrain).'_up.png">
											<img alt="?" class="strategicIcon" style= "width:64px;height:64px;" src="IMG/PREVIEW/'.strtoupper($thisUnit->Id).'.png">
											<img alt="[x]" class="strategicIconOverlap" src="IMG/STRATEGIC/'.($strategic).'_rest.png" style="filter: contrast(70%) sepia(300%) brightness(150%) hue-rotate('.(getFactionColor($thisUnit->General->FactionName, 'hue')-63).'deg) saturate(300%) brightness(70%) contrast(200%) brightness(130%);">
										</div>
									</div>';
							}
							else {
								echo '
										<div class="unit" 
											style="
												display: flex;
												width:100%;
												justify-content:flex-start;
												flex-direction: row;">';
							
								$icon = '';
								if (property_exists($thisUnit, 'StrategicIconName')){
									$icon = ($thisUnit->StrategicIconName).'_rest';
								}
								echo '
										<div style="width:20px; 
													height:20px;
													filter: contrast(70%) sepia(300%) brightness(150%) hue-rotate('.(getFactionColor($armyName, 'hue')-63).'deg) saturate(300%) brightness(70%) contrast(200%) brightness(130%);" >
											<img src="IMG/STRATEGIC/'.$icon.'.png" alt="[x]">
										</div>';
								
								
								$description = "unit";
								if (property_exists($thisUnit, 'Description')){
									$description = attemptTranslation($thisUnit->Description, $locData, $userSettings['lang']);
								}
								$description = $techName.($description);
								$name = '';
								if (property_exists($thisUnit->General, 'UnitName')){
									$name = attemptTranslation($thisUnit->General->UnitName, $locData, $userSettings['lang']);
								}
								echo '
										<div class="unitName" style="width:100%;">
											<span class="unitHotLink" onclick="seeUnit(\''.$id.'\')">
											'.($description).'
											</span>
											<span style="
												font-weight:bold; 
												font-style:italic;
												color:'.getFactionColor($armyName, "dark").'">'.
												($name).'
											</span>
											<span style="
												color:'.getFactionColor($armyName, "bright").';">
												Compare...
											</span>
										</div>';
								
								echo '</div>';
							}
							echo '
							</div>';
						}
					}
					
					echo '</div>';
					
				}
				
			
				echo '</div>';	
				
				
			}
			
			echo '</div>
				</details>';
		}

		echo '</div>
		<script>
			checkForUnitsToCompare();
		</script>
		<div style="height:64px;position:relative;width:100%;">
			<div style="position:absolute;right:5px;bottom:-10px;text-align:right;">
				<a href="LICENSE" style="color:white;font-size:10px;">
					Made by rackover@racknet.noip.me - 2018
				</a>
			</div>
		</div>';
	} ?>
	
	<button style="position:fixed;
				right:15px;
				bottom:-16px;
				color:#EEEEEE;
				background-color:#303030;
				font-family:Zeroes;
				height:48px;
				width:128px;
				padding:8px;
				border-radius:8px;
				cursor:pointer;"
			onClick="toggleSettingsMenu()">
		<div style="position:absolute;top:8px;left:0px;text-align:center;width:100%;">
			Settings
		</div>
		
	</button>
	
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
			display:none;" id="settingsMenu">
		<div style="font-family:Zeroes;text-align:center;width:100%;margin-top:8px;margin-bottom:16px;">
			Settings
		</div>
		<div class="flexRows" style="width:100%;text-align:center;margin-bottom:32px;">
			<form action="<?php echo $url;?>" method="POST" name="settingsMod">
			<?php 
				foreach($defaultSettings as $settingName=>$settingValue){
					if (array_key_exists($settingName, $userSettings)){
						$settingValue = $userSettings[$settingName];
					}
					//Specific...
					if ($settingName == "previewCorner"){
						$options = ["Top left", "Top right", "Bottom right", "Bottom left", "None"];
						$settingValue = '
							<select style="width:100%;text-align:center;" name="'.$settingName.'">';
						foreach($options as $thisOpt){
							$select = '';
							if ($thisOpt == $userSettings[$settingName]){
								$select = "selected";
							}
							$settingValue.= '
								<option '.$select.' value="'.$thisOpt.'">'.$thisOpt.'</option>';
							
						}
								
						$settingValue .='	
							</select>';
					}
					else if ($settingName == "lang"){
						$options = $locData;
						$settingValue = '
							<select style="width:100%;text-align:center;" name="'.$settingName.'">';
						foreach($options as $locName=>$x){
							$select = '';
							if (array_key_exists($settingName, $userSettings) && $locName == $userSettings[$settingName]){
								$select = "selected";
							}
							$settingValue.= '
								<option '.$select.' value="'.$locName.'">'.$locName.'</option>';
							
						}
								
						$settingValue .='	
							</select>';
					}
					else if ($settingName == "autoExpand" || $settingName == "spookyMode"){
						$checked = "";

						if ($settingValue){
							$checked = "checked";
						}
						$settingValue = '<input style="width:100%;text-align:center;" name="'.$settingName.'" type="checkbox" '.$checked.' />'; 
					}	
					
					//Generic
					else if(is_array($settingValue)){
						$settingValue = '<input style="width:100%; type="text" name="'.$settingName.'" value="'.implode(",",$settingValue).'"/>';
					}	
					else{
						$settingValue = '<input style="width:100%;text-align:center;" name="'.$settingName.'" type="text" value=\''.($settingValue).'\'/>';
					}
					echo '
				<div class="flexColumns" style="margin:32px;margin-top:8px;margin-bottom:8px;">
					<div style="text-align:left;width:50%;margin:8px;">
						'.caseFormat($settingName).'
					</div>
					<div style="text-align:right;width:50%;margin:8px;">
						'.$settingValue.'
					</div>
				</div>';
				}
			?>
				<div>
					<input type="hidden" name="settingsMod" value=1>
					<input type="submit" value="Apply" style="
						font-family:Zeroes;
						color:#303030;
						background-color:#EEEEEE;
						width:30%;"/>
				</div>
			</form>
		</div>
	</div>
	
	<?php
	
	///////////////////////////////////////
	///									///
	///	Display functions follow.       ///
	///									///
	///////////////////////////////////////

	function getFactionColor($faction, $tint="normal"){
		switch($faction){
			default:
				$color['normal'] = "grey";
				$color['bright'] = "lightgrey";
				$color['dark'] = "#222222";
				$color['hue'] = 300;
				break;
		
			case "Cybran" :
				$color['normal'] = "#C3272B";
				$color['bright'] = "#F1A9A0";
				$color['dark'] = "#6A0C0C";
				$color['hue'] = 0;
				break;
				
			case "Seraphim" :
				$color['normal'] = "#F4B350";
				$color['bright'] = "#FDE3A7";
				$color['dark'] = "#664d00";
				$color['hue'] = 53;
				break;
				
			case "UEF" :
				$color['normal'] = "#2C4770";
				$color['bright'] = "#ADB6C4";
				$color['dark'] = "#13294C";
				$color['hue'] = 227;
				break;
				
			case "Aeon" :
				$color['normal'] = "#87D37C";
				$color['bright'] = "#C8F7C5";
				$color['dark'] = "#004d00";
				$color['hue'] = 150;
				break;
		}
		return $color[$tint];
	}
	
	function format($string){
		$string = number_format(round(floatval($string)), 0, ',', ' ');
		return $string;
	}
	
	function insertAtPosition($string, $insert, $position) {
		return implode($insert, str_split($string, $position));
	}
	function getUnitTitle($unit, $locData=null, $lang='US'){
		$nickname = '';
		if (property_exists($unit->General, 'UnitName')){
			$nickname = '"'.attemptTranslation($unit->General->UnitName, $locData, $lang).'" ';
		}
		$terrain = 'none';
		$strategic = 'none';
		$description = 'unit';
		if (property_exists($unit, 'StrategicIconName')){
			$strategic = $unit->StrategicIconName;
		}
		if (property_exists($unit->General, 'Icon')){
			$terrain = $unit->General->Icon;
		}
		if (property_exists($unit, 'Description')){
			$description = attemptTranslation($unit->Description, $locData, $lang);
		}
		return '<div class="unitTitleBar" style="border-top:1px solid white;">
					<div class="previewImg">
						<img alt="" class="backgroundIconOverlap" src="IMG/PREVIEW_BACKGROUND/'.($terrain).'_up.png">
						<img alt="?" class="strategicIcon" style= "width:64px;height:64px;" src="IMG/PREVIEW/'.strtoupper($unit->Id).'.png">
						<img alt="[x]" class="strategicIconOverlap" src="IMG/STRATEGIC/'.($strategic).'_rest.png" style="filter: contrast(70%) sepia(300%) brightness(150%) hue-rotate('.(getFactionColor($unit->General->FactionName, 'hue')-63).'deg) saturate(300%) brightness(70%) contrast(200%) brightness(130%);">
					</div>
					<div style="color:'.getFactionColor($unit->General->FactionName, 'bright').';" >
						<p  class="previewTitle">
						'.$nickname.$description.'
						</p>
						<p style="
								padding-left:15px;
								margin:0px;">
							<a href=" https://github.com/FAForever/fa/blob/develop/units/'.($unit->Id).'/'.($unit->Id).'_unit.bp"
								class="blueprintLink" style="color:'.(getFactionColor($unit->General->FactionName, 'bright')).';">
								'.strtoupper($unit->Id).'
							</a>
						</p>
					</div>
				</div>';
	}
	
	function getTech($unit){
			
		$unitTech = "";
		$unitCat = $unit->Categories;
			
		if (in_array ('TECH1', $unitCat)){
			$unitTech = "T1 ";
		}
		else if (in_array ('TECH2', $unitCat)){
			$unitTech = "T2 ";
		}
		else if (in_array ('TECH3', $unitCat)){
			$unitTech = "T3 ";
		}
		else if (in_array ('EXPERIMENTAL', $unitCat)){
			$unitTech = "Experimental ";
		}
		return $unitTech;
	}
	
	function is_JSON($args) {
		json_decode($args);
		return (json_last_error()===JSON_ERROR_NONE);
	}
	
	function caseFormat($string){
		$array = str_split($string);
		foreach($array as $key=>$letter){
			if ($key == 0){
				$letter = strtoupper($letter);
			}
			else if (ctype_lower($array[$key-1]) && ctype_upper($letter)){
				$letter = ' '.$letter;
			}
			$array[$key] = $letter;
		}
		return implode('', $array);
	}
	
	function attemptTranslation($string, $locData, $lang){
		$matches = [];
		if (preg_match ('/(<LOC.*>+)/', $string, $matches)){
			$line = $matches[0];
			$line = str_replace('<LOC ', '', $line);
			$line = str_replace('>', '', $line);
			$thisLang = $locData[$lang];
			if (array_key_exists($line, $thisLang)){
				$string = str_replace('"', '', $thisLang[$line]);
			}
		}	
		$string = preg_replace ('/(<LOC.*>+)/', '', $string);
		
		return $string;
	}
	
	?>
	<?php echo file_get_contents("LICENSE"); ?>
	</BODY>
</html>