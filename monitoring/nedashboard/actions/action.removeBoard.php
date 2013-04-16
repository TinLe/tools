<?php
include_once '../dataSources/performance/class.boardDAO.php';
include_once '../dataSources/avaiability/class.boardDAO.php';
include_once '../layout/class.layoutDAO.php';

require_once '../includes/config.php.inc';
require_once(dirname(__FILE__).'/../includes/config.NetEye.php.inc');

$boardToDel=$_GET["board"];
$board = new boardDAO();
$boardAvaiable=new boardDAOAvaiable();

//If the board to delete is the default Board, this setting will be deleted from global settings.
if ($DB_defaultBoard==$boardToDel){
	
	require_once '../dataSources/performance/class.globalSettingsDAO.php';
	$gSDAO=new globalSettingsDAO();
	$gSDAO->load();
	$gSDAO->setValue("DB_defaultBoard", "\"\"");
	
	$gSDAO->persist();
}

//Delete board from layout configuration.
$layoutDAO=new layoutDAO();
$layoutDAO->removeBoard($boardToDel);

//Delete board from config file: Availability or Performance
if ($boardToDel != ""){
	//if board to del is within performance Boards
	if ($board->contains($boardToDel)) {
		logManager::writeToLog("OK: Deleting of performance obard: ".$boardToDel);
		$boardDAO = new boardDAO();
	} else if ($boardAvaiable->contains($boardToDel)) {
		logManager::writeToLog("OK: Deleting of availability obard: ".$boardToDel);
		$boardDAO = new boardDAOAvaiable();
	} else {
		logManager::writeToLog("CRITICAL: Error deleting board ".$boardToDel." from configure File after delete of layout.");
		die ("Error deleting board ".$boardToDel." from configure File after delete of layout.");
	}
}
//$allBoards = $boardDAO->getBoards();
//echo ("Debug: ". count($allBoards));
//if (count($allBoards)==0) {
//	$boardDAO=new boardDAOAvaiable();
//}

$boardDAO->removeBoard($boardToDel);
$boardDAO->persist();
$layoutDAO->persist();
    
header("Location: ../configDash.php");
?>
