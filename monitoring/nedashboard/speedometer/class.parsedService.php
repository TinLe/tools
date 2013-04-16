<?php
include_once dirname(__FILE__).'/../dataSources/avaiability/scanner/class.scannerFactory.php';
include_once dirname(__FILE__).'/../dataSources/avaiability/scanner/class.dataScanner.php';
include_once dirname(__FILE__).'/../dataSources/avaiability/parser/class.parserFactory.php';
include_once(dirname(__FILE__)."/../dataSources/performance/class.globalSettingsDAO.php");
include_once 'class.abstractDataProvider.php';

class parsedService extends abstractDataProvider {

	var $DB_timeperiodService = "last7days";
	var $DB_availCGIUserPass = "guest:guest";
	var $db;

	function parsedService($db) {
		$this->db=$db;
	}
	function setTimeperiod(){
		$globalSettings = new globalSettingsDAO();
		$globalSettings->load();
		//$this->DB_availCGIUserPass=$globalSettings->getValue("DB_availCGIUserPass");
		$this->DB_timeperiodService=str_replace('"','',$globalSettings->getValue("DB_timeperiodService"));
	}

	function status() {
		return 5;
	}

	function fetch($ds) {
		
		/*
		 * Parse for services only if not parsed from avail.cgi yet
		 */
		if (!isset($_SESSION['avail_service'])){
			$this->setTimeperiod();
			
			//setup timeperiods
    		$sDate = time();
    		$eDate = time();
    		    		
    		$this->calculateReportStartDate($this->DB_timeperiodService, $sDate, $eDate);
    		
			$curl = curl_init();
			$opt=array();
			$opt[CURLOPT_USERPWD]=$this->DB_availCGIUserPass;
			//$opt[CURLOPT_URL]="http://".$_SERVER["SERVER_NAME"]."/neteye/cgi-bin/avail.cgi?show_log_entries=&host=DemoLinuxRedHat&service=all&timeperiod=last7days&smon=1&sday=1&syear=2009&shour=0&smin=0&ssec=0&emon=1&eday=22&eyear=2009&ehour=24&emin=0&esec=0&rpttimeperiod=&assumeinitialstates=yes&assumestateretention=yes&assumestatesduringnotrunning=yes&includesoftstates=no&initialassumedservicestate=0&backtrack=4";
			//$opt[CURLOPT_URL]="http://".$_SERVER["SERVER_NAME"]."/neteye/cgi-bin/avail.cgi?show_log_entries=&host=DemoLinuxRedHat&service=all&timeperiod=".$this->DB_timeperiodService."&smon=1&sday=1&syear=2009&shour=0&smin=0&ssec=0&emon=1&eday=22&eyear=2009&ehour=24&emin=0&esec=0&rpttimeperiod=&assumeinitialstates=yes&assumestateretention=yes&assumestatesduringnotrunning=yes&includesoftstates=no&initialassumedservicestate=0&backtrack=4";
			$opt[CURLOPT_URL]="http://".$_SERVER["SERVER_NAME"]."/neteye/cgi-bin/avail.cgi?t1=".$sDate."&t2=".$eDate."&show_log_entries=&host=all&service=all&assumeinitialstates=yes&assumestateretention=yes&assumestatesduringnotrunning=yes&includesoftstates=no&initialassumedhoststate=3&initialassumedservicestate=6&timeperiod=".$this->DB_timeperiodService."&backtrack=4";

			$opt[CURLOPT_RETURNTRANSFER] =1;
			$opt[CURLOPT_HTTPAUTH] = CURLAUTH_BASIC | CURLAUTH_DIGEST | CURLAUTH_GSSNEGOTIATE | CURLAUTH_NTLM;
			curl_setopt_array ( $curl , $opt );
			$_SESSION['avail_service'] = utf8_encode(curl_exec($curl));
			logManager::writeToLog("INFO: Parsing Service for Tacho: ".$opt[CURLOPT_URL]);
		}
		$str = $_SESSION['avail_service'];

		$htmlParser=parserFactory::createParser($str);
		$tableParser=new dataScanner($str,$this->db);
		$splitted=split("\.", $ds);

		$tableParser->filterData($splitted[0]);
		$tableParser->filter("service",$splitted[1]);
		$values=$tableParser->getCol("Time OK");
		return $values[0];
	}

	function isActive() {
		include 'includes/config.php.inc';

		return true;
	}
	
function calculateReportStartDate(&$DB_timeperiodService, &$sDate, &$eDate){
    	//"today", "last24hours", "yesterday", "last7days", "last31days", "lastmonth", "thisyear", "lastyear");
    	$oneDay = 86400;
    	
    	switch ($DB_timeperiodService) {
    		case "today":
    			$secsToday = date('G')*3600;
    			$secsToday += date('i')*60;	
    			$sDate = time()-$secsToday;
    		;
    		break;
    	
    		case "last24hours":	
    			$sDate = time()-$oneDay;
    		;
    		break;
    		
    		case "yesterday":	
    			$secsToday = date('G')*3600;
    			$secsToday += date('i')*60;
    			
    			$eDate = time()-$secsToday;
    			$sDate = $eDate-$oneDay;
    		;
    		break;
    		
    		case "last24hours":	
    			$sDate = time()-$oneDay;
    		;
    		break;
    		
    		case "last31days":	
    			$sDate = time()-($oneDay*31);
    		;
    		break;
    		
    		case "lastmonth":	
    			$secsThisMonth = date('j')*$oneDay;
    			$eDate = time()-$secsThisMonth;
    			$sDate = $eDate-$oneDay*30;
    		;
    		break;
    		
    		case "thisyear":	
    			$secsThisYear = date('j')*$oneDay;
    			$secsThisYear += ((date('m')-1)*$oneDay*30);
    			$sDate = time()-$secsThisYear;
    		;
    		break;
    		
    		case "lastyear":
    			$secsThisYear = date('j')*$oneDay;
    			$secsThisYear += ((date('m')-1)*$oneDay*30);
    			$eDate = time()-$secsThisYear;
    			
    			$secsThisYear += $oneDay*365;
    			$sDate = time()-$secsThisYear;
    		;
    		break;    		
    		//return $sDate;
    	}
	}
}

?>
