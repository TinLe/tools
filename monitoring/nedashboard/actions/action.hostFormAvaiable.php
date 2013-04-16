<?php
include_once '../ajax/configForm/class.configFormFactory.php';
include "../dataSources/availability/class.configHostDAO.php";
include "../dataSources/availability/class.hostDAO.php";
include "../layout/class.elementDAO.php";

session_start();
$host = new hostDAOAvailable($_GET["board"]);
$element = new elementDAO($_GET["board"]);
if ($_GET["action"]==0) {
    //add selected host(s)

    foreach($_GET["aSelection"] as $aSelection) {
        $host->addhost($aSelection);
        $element->addElement($aSelection,"Availability");
    }
} else if ($_GET["action"]==1) {
    //remove selected host(s)

    foreach($_GET["rSelection"] as $rSelection) {
        $host->removehost($rSelection);
        $element->removeElement($rSelection,"Availability");
    }
} else if ($_GET["action"]==2) {
    //do no action, only selection of one single host
    if ($_GET["focus"]==0) {
        $val=$_GET["rSelection"];
    } else {
        $val=$_GET["aSelection"];
    }
    $selHost="&host=".$val[0];
}

//save data
$host->persist();
$element->persist();

//print selection for html select filled with AJAX
$available = new configHostDAOAvailable(".");
$sHostNames=array();
$aHostNames=array();

foreach ($host->gethosts() as $hostId) {
    //get all hostnames already selected
    $sHostNames[]=$host->getName($hostId);
}
foreach ($available->getElements() as $hostName) {
    //get all hostnames already selected
    if (!$host->contains($hostName)) {
        //if they are not contained in selected hostNames
        $aHostNames[]=$hostName;
    }
}

//print option values
$configForm = configFormFactory::getForm("hostFormAvailable",$sHostNames,$aHostNames);
echo $configForm->createOptions1($sHostNames);
echo "?";
echo $configForm->createOptions2($aHostNames);
?>
