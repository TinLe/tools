<?php
include_once '../ajax/configForm/class.configFormFactory.php';
include "../dataSources/availability/class.configServiceDAO.php";
include "../dataSources/availability/class.serviceDAO.php";
include "../layout/class.elementDAO.php";

session_start();

$service = new serviceDAO($_GET["board"],$_GET["host"]);
$element = new elementDAO($_GET["board"]);

if ($_GET["action"]==0) {
    foreach($_GET["aSelection"] as $aSelection) {
        $service->addService($aSelection);
        $element->addElement($_GET["host"].".".$aSelection,"Availability");
    }
} else if ($_GET["action"]==1) {
    foreach($_GET["rSelection"] as $rSelection) {
        $service->removeService($rSelection);
        $element->removeElement($_GET["host"].".".$rSelection,"Availability");
    }
} else if ($_GET["action"]==2) {
    if (isset ($_GET["rSelection"])) {
        $val=$_GET["rSelection"];
    } else {
        $val=$_GET["aSelection"];
    }
}
$service->persist();
$element->persist();

//print selection for html select filled with AJAX
$available = new configServiceDAO($_GET["host"]);
$sServiceNames=array();
$aServiceNames=array();

foreach ($service->getServices() as $serviceId) {
    //get all servicenames already selected
    $sServiceNames[]=$service->getName($serviceId);
}
foreach ($available->getElements() as $serviceName) {
    //get all servicenames available
    if (!in_array($serviceName,$aServiceNames) && !$service->contains($serviceName)) {
        //if this is not a duplicate and
        //if they are not contained in selected serviceNames
        $aServiceNames[]=$serviceName;
    }
}

//print option values
$configForm = configFormFactory::getForm("serviceFormAvailable",$sServiceNames,$aServiceNames);
echo $configForm->createOptions1($sServiceNames);
echo "?";
echo $configForm->createOptions2($aServiceNames);
?>
