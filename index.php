<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
<LINK href="STYLE.CSS" rel="stylesheet" type="text/css">
<title>SCFA UNIT LIST</title>
</head>
  <BODY>
	<script>
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
				window.location.href = "<?php echo basename(__FILE__);?>";
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
			if (id == null){
				const checkList = document.getElementsByClassName('unitSelector');
				let idList = [];
				for (i = 0; i < checkList.length; i++) {
					if (checkList[i].checked){
						idList.push(checkList[i].id);
					}
				}
				id = idList.join();
			}
			window.location.href = "<?php echo basename(__FILE__);?>?id="+id;
		}
		
		function checkForUnitsToCompare(){
			let selected = document.getElementsByClassName('unitSelector');
			let amount = 0;
			for (i = 0; i < selected.length; i++) {
				if (selected[i].checked){
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
	
	$dataString = file_get_contents("DATA/UNITS_COMPLETE.JSON");
	$dataJS = json_decode($dataString);
	
	/////////////////////////////////
	///
	///		UNIT COMPARATOR MODE
	///
	/////////////////////////////////
	
	if (isset($_GET['id']) && $_GET['id'] != "-1"){
		$list = explode(',', $_GET['id']);
		
		$toCompare = array();
		
		for ($i = 0; $i < sizeOf($dataJS); $i++){
			$element = $dataJS[$i];
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
			
				$inf['Description'] = $thisUnit->Description;
				$inf['Health'] = $thisUnit->Defense->Health;
				$inf['Regen'] = $thisUnit->Defense->RegenRate;
				$inf['Economy'] = $thisUnit->Economy;
				$inf['Faction'] = $thisUnit->General->FactionName;
				$inf['Id'] = $thisUnit->Id;
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
						echo '<div style="position:relative;">';
						echo getUnitTitle($thisUnit);
						echo '<button style="font-weight:bold;
											color:red;
											background-color:black;
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
						
						echo '<div class="sheetSection">
								<div class="smallTitle"  style="color:'.getFactionColor($inf['Faction'], 'bright').';">
									Build costs
									</div>
									
									<div class="flexColumns" style="padding-bottom:4px;">
										<div class="energyCost">
											<img alt="nrg" style="vertical-align:top;" src="IMG/ICONS/energy.png"> '.format($inf['Economy']->BuildCostEnergy).'
										</div>
										<div class="massCost">
											<img alt="mss" style="vertical-align:top;" src="IMG/ICONS/mass.png"> '.format($inf['Economy']->BuildCostMass).'
										</div>
										<div class="buildTimeCost">
											<img alt="tim" style="vertical-align:top;" src="IMG/ICONS/time.png"> '.format($inf['Economy']->BuildTime).'
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
												'.$thisAb.'
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
											Fuel refill rate: '.round(($physics->FuelRechargeRate)*100).'%
										</div>';
									if (property_exists($physics, 'FuelUseTime') && $physics->FuelUseTime > 0) echo '
										<div class ="info" 
											style="
											color:'.getFactionColor($inf['Faction'], 'bright').';">
											Fuel use time : '.($physics->FuelUseTime).'s
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
				else if ($thisComponent == "Veteran" && property_exists($thisUnit, 'Weapon') && sizeOf($thisUnit->Weapon) > 0){
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
								
								echo '<details class="weaponList">
										<summary  class="weaponCategory">
											'.($thisWeapon->DisplayName).'
										</summary>
										<div class="flexRows">';
								if (property_exists($thisWeapon, 'Damage') &&
									$thisWeapon->Damage > 0){ 
									echo '<div class="flexColumns">
											<div class="littleInfo" >
												Damage
											</div>
											<div class="littleInfoVar" >
												'.format($thisWeapon->Damage).'
											</div>
											';
									if (property_exists($thisWeapon, 'RateOfFire')){
										echo '<div class="littleInfo" style="text-align:right;border-left:1px dotted grey;">
												DPS
											</div>';
										if (property_exists($thisWeapon,'ContinuousBeam') &&
											$thisWeapon->ContinuousBeam){
											echo '	<div class="littleInfoVar" >
												'.format(($thisWeapon->Damage)*($thisWeapon->RateOfFire)).'
											</div>';
										}
										else{
											echo '	<div class="littleInfoVar" >
												'.format(($thisWeapon->Damage)/($thisWeapon->RateOfFire)).'
											</div>';
										}
									}
									echo '
										</div>';
								}		
								if (property_exists($thisWeapon, 'DamageRadius') &&
									$thisWeapon->DamageRadius > 0){
									echo '<div class="flexColumns">
											<div class="littleInfo" style="border-left:1px dotted grey;">
												Damage radius
											</div>
											<div class="littleInfoVar" >
												'.format($thisWeapon->DamageRadius).'
											</div>
										</div>';
								}
								if (property_exists($thisWeapon,'ContinuousBeam') &&
									$thisWeapon->ContinuousBeam){
										echo '<div class="flexColumns">
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
									echo '<div class="flexColumns">
											<div class="littleInfo" >
												Fire cycle
											</div>
											<div class="littleInfoVar" >
												'.($thisWeapon->ProjectilesPerOnFire).' shots / '.($thisWeapon->RateOfFire).'s
											</div>
										</div>';
								}				
								if (property_exists($thisWeapon, 'DamageType') &&
									$thisWeapon->DamageType != "Normal"){ 
									echo '<div class="flexColumns">
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
										echo '<div class="flexColumns">
												<div class="littleInfo" >
													Nuke radius
												</div>
												<div class="littleInfoVar" >
													'.format($thisWeapon->NukeInnerRingRadius).'-'.format($thisWeapon->NukeOuterRingRadius).'
												</div>
											</div>';
										echo '<div class="flexColumns">
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
									echo '<div class="flexColumns">
											<div class="littleInfo" style="width:50%;">
												WeaponCategory
											</div>
											<div '.$moreStyle.'width:50%;text-align:left;">
												'.($thisWeapon->WeaponCategory).'
											</div>
										</div>';
								}
	
								if (property_exists($thisWeapon, 'MuzzleVelocity') &&
									$thisWeapon->MuzzleVelocity > 0){ 
									echo '<div class="flexColumns">
											<div class="littleInfo" >
												MuzzleVelocity
											</div>
											<div class="littleInfoVar" >
												'.($thisWeapon->MuzzleVelocity).'
											</div>
										</div>';
								}	
	
								if (property_exists($thisWeapon, 'MaxRadius') &&
									$thisWeapon->MaxRadius > 0){ 
									echo '<div class="flexColumns">
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
	
								if (property_exists($thisWeapon, 'FiringTolerance') &&
									$thisWeapon->FiringTolerance > 0){ 
									echo '<div class="flexColumns">
											<div class="littleInfo" >
												FiringTolerance
											</div>
											<div class="littleInfoVar" >
												'.($thisWeapon->FiringTolerance).'°
											</div>
										</div>';
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
								&& property_exists($thisEnhancement, "Slot")){
								echo '		<details class="weaponList">
												
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
												'ShieldRegenStartTime'];
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
							(in_array("FACTORY", $thisUnit->Categories) ||
							in_array("ENGINEER", $thisUnit->Categories)) &&
							property_exists($thisUnit->Economy, 'BuildableCategory')){
					$buildable = [];
					foreach($dataJS as $unit){
						foreach($thisUnit->Economy->BuildableCategory as $buildableCategory){
							$buildableRequirements = explode(' ', $buildableCategory);
							$canBuild = true;
							foreach($buildableRequirements as $requirement){
								if (!in_array($requirement, $unit->Categories)){
									$canBuild = false;
								}
							}
							if ($canBuild){
								$buildable[] = $unit;
							}
						}
					}
					
					$buildable = array_unique ($buildable, SORT_REGULAR);
					
					if (sizeOf($buildable > 0)){
						
						echo '
						<details class="sheetSection unitBlueprints" id="blueprintsSection'.($thisUnit->Id).'" onClick="openAllBlueprintsSections(\'blueprintsSection'.($thisUnit->Id).'\')">
							<summary class="smallTitle"  style="color:'.getFactionColor($inf['Faction'], 'bright').';">
								Blueprints
							</summary>';
							
						foreach($buildable as $buildableUnit){
							
							if (property_exists($buildableUnit, 'StrategicIconName')){
								$icon = ($buildableUnit->General->FactionName).'_'.($buildableUnit->StrategicIconName);
							}
							echo '
							<div class="flexRows">
								<div class="flexColumns unit unitMainDiv">
									<div
										onclick="seeUnit(\''.($buildableUnit->Id).'\')">
										<img src="IMG/STRATEGIC/'.$icon.'.png" style="width:17px; height:17px;" alt="[x]">
									</div>';
							
							
							$description = (getTech($buildableUnit)).($buildableUnit->Description);
							$name = '';
							if (property_exists($buildableUnit->General, 'UnitName')){
								$name = ($buildableUnit->General->UnitName);
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
			<div style="position:fixed;left:50%;bottom:10px;">
				<button id="comparatorPopup" hidden	
						onClick="seeUnit()">
					Open comparator...
				</button>
			</div>';

		$armies = ['UEF', 'Cybran', 'Aeon', 'Seraphim'];

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
							'Support'=>null);
		
		//END OF 

		for ($i = 0; $i < sizeOf($dataJS); $i++){
			
			$item = $dataJS[$i];
			
			$faction = $item->General->FactionName;
			
			
			//CATEGORIZING
			
			$itemCat = $item->Categories;
			
			$tech = getTech($item);
			
			///////////////
			//	COMMANDER & SACU
			//////////////
			if (in_array ('COMMAND', $itemCat)
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
						(in_array ('INDIRECTFIRE', $itemCat) ||
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
			else if (in_array('STRUCTURE', $itemCat) && 
						(in_array ('SORTINTEL', $itemCat))){
				$finalData['Building - Sensor'][$tech][$faction][] = $item;
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

		
		
		///DISPLAY THE BIG HEADER / TITLE
		
		echo '<div style="
			display: flex;
			border:1px solid black;
			justify-content:space-between;
			flex-direction: row;" >';
			
		foreach($armies as $armyName){
			// #
			echo '
				<div class="flexColumns title" style="
					background-repeat: no-repeat;
					background-image:url(\'IMG/FACTIONAL/'.strtolower($armyName).'_load.jpg\');
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
			
			echo '
			<details open>
				<summary class="categoryName">
					'.$catName.'
				</summary>';
			
			echo '<div style="
				display: flex;
				justify-content:space-between;
				flex-direction: column;" >';
				
			foreach($categories as $techName => $techLevel){
				
				echo '<div style="
					display: flex;
					justify-content:flex-start;
					flex-direction: row;
					width:100%;">';
				
				
				foreach($armies as $armyName){
					//echo '<span style="color:red;">'.$armyName.'</span>';
					
					$border = '';
					
					if ($techName != "T1 "){
						$border = 'border-top: 1px dotted grey;';
					}				
					
					echo '<div style="
						display: flex;
						justify-content:flex-start;
						flex-direction: column;
						width:25%;
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
							
							echo '<div class="unitMainDiv tooltip" style="display:flex;flex-direction:row-reverse;">
									<div class="tooltiptext" style="opacity:0.85; background-color:'.getFactionColor($thisUnit->General->FactionName, "dark").';">
										'.getUnitTitle($thisUnit).'
									</div>
									<input class="unitSelector" 
									type="checkbox"  
											id="'.($thisUnit->Id).'" 
											onClick="checkForUnitsToCompare()" />';
							echo '
									<div class="unit" 
										style="
											display: flex;
											width:100%;
											justify-content:flex-start;
											flex-direction: row;">';
						
							$icon = '';
							if (property_exists($thisUnit, 'StrategicIconName')){
								$icon = $armyName.'_'.($thisUnit->StrategicIconName);
							}
							echo '
									<div 
										onclick="seeUnit(\''.$id.'\')">
										<img src="IMG/STRATEGIC/'.$icon.'.png" style="width:17px; height:17px;" alt="[x]">
									</div>';
							
							
							$description = $techName.($thisUnit->Description);
							$name = '';
							if (property_exists($thisUnit->General, 'UnitName')){
								$name = ($thisUnit->General->UnitName);
							}
							echo '
									<div class="unitName" style="width:100%;"
										onclick="seeUnit(\''.$id.'\')">
										'.($description).'
										<span style="
											font-weight:bold; 
											font-style:italic;
											color:'.getFactionColor($armyName, "dark").'">'.
											($name).'
										</span>
									</div>';
							
							echo '</div>
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
		<div style="height:64px;">
		</div>';
	}
	
	///////////////////////////////////////
	///									///
	///	Display functions follow.       ///
	///									///
	///////////////////////////////////////

	function getFactionColor($faction, $tint="normal"){
		switch($faction){
			default:
				$color['normal'] = "#C3272B";
				$color['bright'] = "#E68364";
				$color['dark'] = "#8F1D21";
				break;
		
			case "Cybran" :
				$color['normal'] = "#C3272B";
				$color['bright'] = "#F1A9A0";
				$color['dark'] = "#6A0C0C";
				break;
				
			case "Seraphim" :
				$color['normal'] = "#F4B350";
				$color['bright'] = "#FDE3A7";
				$color['dark'] = "#664d00";
				break;
				
			case "UEF" :
				$color['normal'] = "#2C4770";
				$color['bright'] = "#ADB6C4";
				$color['dark'] = "#13294C";
				break;
				
			case "Aeon" :
				$color['normal'] = "#87D37C";
				$color['bright'] = "#C8F7C5";
				$color['dark'] = "#004d00";
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
	function getUnitTitle($unit){
		$nickname = '';
		if (property_exists($unit->General, 'UnitName')){
			$nickname = '"'.($unit->General->UnitName).'" ';
		}
		return '<div class="unitTitleBar" style="border-top:1px solid white;">
					<div class="previewImg">
						<img alt="preview" class="backgroundIconOverlap" src="IMG/PREVIEW_BACKGROUND/'.($unit->General->Icon).'_up.png">
						<img alt="preview"	class="strategicIcon" src="IMG/PREVIEW/'.($unit->Id).'.png">
						<img alt="strategic" class="strategicIconOverlap" src="IMG/STRATEGIC/'.($unit->General->FactionName)."_".($unit->StrategicIconName).'.png">
					</div>
					<div style="color:'.getFactionColor($unit->General->FactionName, 'bright').';" >
						<p  class="previewTitle">
						'.$nickname.($unit->Description).'
						</p>
						<p style="
							padding-left:15px;
							margin:0px;">
						'.($unit->Id).'
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
	
	?>
	<?php echo file_get_contents("LICENSE"); ?>
	</BODY>
</html>