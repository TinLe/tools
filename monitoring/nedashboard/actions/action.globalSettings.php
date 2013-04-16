<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
require_once '../dataSources/performance/class.globalSettingsDAO.php';

if ($_GET["fMax"] != "" && is_numeric($_GET["fMax"]) && $_GET["fTime"] != "" && is_numeric($_GET["fTime"]) && $_GET["fDefault"] != "" && $_GET["fAvaiability"] != "" && is_numeric($_GET["fAvaiability"])){

	$gSDAO=new globalSettingsDAO();
	$gSDAO->load();
	$gSDAO->setValue("DB_tachosInRow", $_GET["fMax"]);
	$gSDAO->setValue("DB_diashowRefreshTime", $_GET["fTime"]);
	$gSDAO->setValue("DB_defaultBoard", "\"".$_GET["fDefault"]."\"");
    $gSDAO->setValue("DB_avaiability", $_GET["fAvaiability"]);
    $gSDAO->setValue("DB_timeperiodHost", "\"".$_GET["fTimeperiodHost"]."\"");
    $gSDAO->setValue("DB_timeperiodService", "\"".$_GET["fTimeperiodService"]."\"");

    logManager::writeToLog("OK: Edit Global settings to: NumOfTachos: ".$_GET["fMax"]."; Diashow Refresh secs: ".$_GET["fTime"]."; Default board: ".$_GET["fDefault"]."; Availability min critical: ".$_GET["fAvaiability"]);
	
    $gSDAO->persist();

	header("Location: ../configGlobal.php?msg=Configuration applied successfully.");
} else {
	header("Location: ../configGlobal.php?board=".$_GET['board']."&msg=Advice: All fields are required to hold a value.");	
}
?>
