<?php
class configureDAO {
	//Location of RRD perfdata
	const perfdataPath="/var/log/nagios/perfdata/";
}

class logManager {
	const logFilePath = "../log/dashboard.log";
	const enableLog = true;
	
	static function writeToLog($logText){
		if (self::enableLog == true) {
			$fh = fopen(dirname(__FILE__)."/".self::logFilePath, 'a') or print("<script>alert(\"Error writing to log file. Please check file ".self::logFilePath." write permissions.\");</script>");
			$logText = date("Y-m-d G:i:s")." ".$logText;
			fwrite($fh, $logText."\n");
			fclose($fh);
			return true;
		}
	}
}
?>
