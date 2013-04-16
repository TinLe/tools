<?php
include_once '../ajax/configForm/class.configFormFactory.php';
include "../dataSources/avaiability/class.configHostDAO.php";
include "../dataSources/avaiability/class.hostDAO.php";
include "../layout/class.elementDAO.php";

session_start();
$host = new hostDAOAvaiable($_GET["board"]);
$element = new elementDAO($_GET["board"]);
if ($_GET["action"]==0) {
    //add selected host(s)

    foreach($_GET["aSelection"] as $aSelection) {
        $host->addhost($aSelection);
        $element->addElement($aSelection,"Avaiability");
    }
} else if ($_GET["action"]==1) {
    //remove selected host(s)

    foreach($_GET["rSelection"] as $rSelection) {
        $host->removehost($rSelection);
        $element->removeElement($rSelection,"Avaiability");
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
$avaiable = new configHostDAOAvaiable(".");
$sHostNames=array();
$aHostNames=array();

foreach ($host->gethosts() as $hostId) {
    //get all hostnames already selected
    $sHostNames[]=$host->getName($hostId);
}
foreach ($avaiable->getElements() as $hostName) {
    //get all hostnames already selected
    if (!$host->contains($hostName)) {
        //if they are not contained in selected hostNames
        $aHostNames[]=$hostName;
    }
}

//print option values
$configForm = configFormFactory::getForm("hostFormAvaiable",$sHostNames,$aHostNames);
echo $configForm->createOptions1($sHostNames);
echo "?";
echo $configForm->createOptions2($aHostNames);
?>
