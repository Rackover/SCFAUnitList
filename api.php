<?php
	header('Content-Type: application/json');
	
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
	
	
	if (isset($_GET['id']) || isset($_GET['searchunit'])){
		
		$requested = $_GET["id"];
		$searchName = $_GET["searchunit"];
		$uOI = null;
		
		for ($i = 0; $i < sizeOf($dataUnits); $i++){
			$element = $dataUnits[$i];
			$id = $element->Id;
			$name = "";
			
			if (property_exists($element->General, 'UnitName')){
				$name = '"'.($element->General->UnitName).'" ';
			}
			if (property_exists($element, 'Description')){
				$name .= ($element->Description);
			}
			
			if ($id == $requested || strpos(strtolower($name), strtolower($searchName)) !== false){
				$uOI = $element;
			}
		}
		
		echo json_encode($uOI);
		exit;
		
	}
	
	
	
?>