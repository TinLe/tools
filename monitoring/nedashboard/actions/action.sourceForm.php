<?php
include_once '../ajax/configForm/class.configFormFactory.php';
include_once "../dataSources/performance/class.sourceDTO.php";
include "../dataSources/performance/class.sourceDAO.php";
include "../layout/class.elementDAO.php";

$source = new sourceDAO($_GET["board"].".".$_GET["host"].".".$_GET["service"]);
$element = new elementDAO($_GET["board"]);

if ($_GET["action"]==0) {
    foreach($_GET["aSelection"] as $aSelection) {
        $source->addSource($aSelection);
        $element->addElement($_GET["host"].".".$_GET["service"].".".$aSelection);

        //load default values from xml
        $sourceDetails = new sourceDTO();
        $sourceDetails->loadDefaults(sourceDAO::root."/".$_GET["host"]."/".$_GET["service"].".xml", $aSelection);
        $source->setDetails($aSelection, $sourceDetails);
    }
} else if ($_GET["action"]==1) {
    foreach($_GET["rSelection"] as $rSelection) {
        $source->removeSource($rSelection);
        $element->removeElement($_GET["host"].".".$_GET["service"].".".$rSelection);
    }
} else if ($_GET["action"]==2) {
	//$val=$_GET["rSelection"];
	if (isset ($_GET["rSelection"])) {
		$val=$_GET["rSelection"];
	} else {
		$val=$_GET["aSelection"];
	}
}

$source->persist();
$element->persist();

//if ($_GET["action"] != -1) {

//print selection for html select filled with AJAX
$avaiability = new configSourceDAO($_GET["host"]."/".$_GET["service"]);
$sSourceNames=array();
$sSourceLabels=array();
$aSourceNames=array();
$aSourceLabels=array();
foreach ($source->getSources() as $sourceId) {
    //get all sourcenames already selected
    $sSourceNames[]=$source->getName($sourceId);
    $sSourceLabels[]=$source->getDetails($source->getName($sourceId))->getLabel();
}
foreach ($avaiability->getElements() as $sourceName) {
    //get all servicenames avaiable
    if (!$source->contains($sourceName)) {
        //if they are not contained in selected sourceNames
        $aSourceNames[]=$sourceName;
        $details = new sourceDTO();
        $details->loadDefaults(sourceDAO::root."/".$_GET["host"]."/".$_GET["service"].".xml",$sourceName);
        $aSourceLabels[]=$details->getLabel();
    }
}

//print option values
/*$configForm = configFormFactory::getForm("sourceForm",$sSourceNames,$aSourceNames);
echo $configForm->createOptions1($sSourceLabels);
echo "?";
echo $configForm->createOptions2($aSourceLabels);
} else {
	echo "?";
*/
//}
$configForm = configFormFactory::getForm("sourceForm",$sSourceNames,$aSourceNames);
echo $configForm->createOptions1($sSourceLabels);
echo "?";
echo $configForm->createOptions2($aSourceLabels);
?>
