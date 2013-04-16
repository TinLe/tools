<?php
include_once '../ajax/configForm/class.configFormFactory.php';
include "../dataSources/performance/class.serviceDAO.php";
include "../layout/class.elementDAO.php";

$service = new serviceDAO($_GET["board"].".".$_GET["host"]);
$element = new elementDAO($_GET["board"]);

if ($_GET["action"]==0) {
    foreach($_GET["aSelection"] as $aSelection) {
        $service->addService($aSelection);
    }
} else if ($_GET["action"]==1) {
    foreach($_GET["rSelection"] as $rSelection) {
        $service->removeService($rSelection);
        $element->removeServiceElement($rSelection);
    }
} else if ($_GET["action"]==2) {
	//$val=$_GET["rSelection"];
	if (isset ($_GET["rSelection"])) {
		$val=$_GET["rSelection"];
	} else {
		$val=$_GET["aSelection"];
	}
}
$service->persist();
$element->persist();

//print selection for html select filled with AJAX
$availability = new configServiceDAO($_GET["host"]);
$sServiceNames=array();
$aServiceNames=array();
foreach ($service->getServices() as $serviceId) {
    //get all servicenames already selected
    $sServiceNames[]=$service->getName($serviceId);
}
foreach ($availability->getElements() as $serviceName) {
    //get all servicenames available
    //serviceName is the name of the xmlFile containing service data
    //therefore we must split the . to get service name
    //ex split(ping.xml) => [ping][xml]
    $h=preg_split("/\./",$serviceName);
    $serviceName=$h[0];
    if (!in_array($serviceName,$aServiceNames) && !$service->contains($serviceName)) {
        //if this is not a duplicate and
        //if they are not contained in selected serviceNames
        $aServiceNames[]=$serviceName;
    }
}

//print option values
$configForm = configFormFactory::getForm("serviceForm",$sServiceNames,$aServiceNames);
echo $configForm->createOptions1($sServiceNames);
echo "?";
echo $configForm->createOptions2($aServiceNames);
?>
