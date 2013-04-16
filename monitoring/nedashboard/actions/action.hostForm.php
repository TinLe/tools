<?php
include_once '../ajax/configForm/class.configFormFactory.php';
include "../dataSources/performance/class.hostDAO.php";
include "../layout/class.elementDAO.php";

$host = new hostDAO($_GET["board"]);
$element = new elementDAO($_GET["board"]);
if ($_GET["action"]==0) {
    //add selected host(s)

    $first=true;
    foreach($_GET["aSelection"] as $aSelection) {
        $host->addhost($aSelection);
        if ($first) {
            $first=false;
        }
    }
} else if ($_GET["action"]==1) {
    //remove selected host(s)
    foreach($_GET["rSelection"] as $rSelection) {
        $host->removehost($rSelection);
        $element->removeHostElement($rSelection);
    }
} else if ($_GET["action"]==2) {
    //do no action, only selection of one single host
    $val=$_GET["rSelection"];
}

//save data
$host->persist();
$element->persist();

//print selection for html select filled with AJAX
$available = new configHostDAO(".");
$sHostNames=array();
$aHostNames=array();

foreach ($host->gethosts() as $hostId) {
    //get all hostnames already selected
    $sHostNames[]=$host->getName($hostId);
}
foreach ($available->getElements() as $hostName) {
    //get all hostnames available
    if (!$host->contains($hostName)) {
        //if they are not contained in selected hostNames
        $aHostNames[]=$hostName;
    }
}

//print option values
$configForm = configFormFactory::getForm("hostForm",$sHostNames,$aHostNames);
echo $configForm->createOptions1($sHostNames);
echo "?";
echo $configForm->createOptions2($aHostNames);
?>
