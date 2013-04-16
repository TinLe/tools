<?php
include_once dirname(__FILE__).'/../dataSources/availability/scanner/class.scannerFactory.php';
include_once dirname(__FILE__).'/../dataSources/availability/scanner/class.dataScanner.php';
include_once dirname(__FILE__).'/../dataSources/availability/parser/class.parserFactory.php';
include_once(dirname(__FILE__)."/../includes/config.NetEye.php.inc");
include_once(dirname(__FILE__)."/../dataSources/performance/class.globalSettingsDAO.php");
include_once 'class.abstractDataProvider.php';

class parsedHost extends abstractDataProvider {
	
	var $DB_timeperiodHost = "last7days";
	var $DB_availCGIUser = "guest:guest";
    var $db;
    function parsedHost($db) {
        $this->db=$db;
    }
    //get timeperiod for availability validation
	function setTimeperiod(){
		$globalSettings = new globalSettingsDAO();
        $globalSettings->load();
		$this->DB_timeperiodHost=str_replace('"','',$globalSettings->getValue("DB_timeperiodHost"));
	}

    function status() {
        return 5;
    }

    function fetch($ds) {

    	/*
		 * Parse for host only if not parsed from avail.cgi yet
		 */
    	if (!isset($_SESSION['avail_host'])){
    		
    		$this->setTimeperiod();
    		
    		//setup timeperiods
    		$sDate = time();
    		$eDate = time();
    		    		
    		$this->calculateReportStartDate($this->DB_timeperiodHost, $sDate, $eDate);
			
    		$curl = curl_init();
    		$opt=array();
    		$opt[CURLOPT_USERPWD]=$this->DB_availCGIUser;
    		//$opt[CURLOPT_URL]="http://".$_SERVER["SERVER_NAME"]."/neteye/cgi-bin/avail.cgi?show_log_entries=&host=all&timeperiod=last7days&smon=1&sday=1&syear=2009&shour=0&smin=0&ssec=0&emon=1&eday=27&eyear=2009&ehour=24&emin=0&esec=0&rpttimeperiod=&assumeinitialstates=yes&assumestateretention=yes&assumestatesduringnotrunning=yes&includesoftstates=no&initialassumedhoststate=0&initialassumedservicestate=0&backtrack=4";
    		//$opt[CURLOPT_URL]="http://".$_SERVER["SERVER_NAME"]."/neteye/cgi-bin/avail.cgi?show_log_entries=&host=all&timeperiod=".$this->DB_timeperiodHost."&smon=".$sDate[1]."&sday=".$sDate[0]."&syear=".$sDate[3]."&shour=0&smin=0&ssec=0&emon=".$eMonth."&eday=".$eDay."&eyear=".$eYear."&ehour=24&emin=0&esec=0&rpttimeperiod=&assumeinitialstates=yes&assumestateretention=yes&assumestatesduringnotrunning=yes&includesoftstates=no&initialassumedhoststate=0&initialassumedservicestate=0&backtrack=4";
    		$opt[CURLOPT_URL]="http://".$_SERVER["SERVER_NAME"]."/neteye/cgi-bin/avail.cgi?t1=".$sDate."&t2=".$eDate."&show_log_entries=&host=all&assumeinitialstates=yes&assumestateretention=yes&assumestatesduringnotrunning=yes&includesoftstates=no&initialassumedhoststate=3&initialassumedservicestate=6&timeperiod=".$this->DB_timeperiodService."&backtrack=4";

    		$opt[CURLOPT_RETURNTRANSFER] =1;
    		$opt[CURLOPT_HTTPAUTH] = CURLAUTH_BASIC | CURLAUTH_DIGEST | CURLAUTH_GSSNEGOTIATE | CURLAUTH_NTLM;
    		curl_setopt_array ( $curl , $opt );
    		$_SESSION['avail_host'] = utf8_encode(curl_exec($curl));
    		logManager::writeToLog("INFO: Parsing Host for Tacho: ".$opt[CURLOPT_URL]);
    	}
    	$str = $_SESSION['avail_host'];

    	$htmlParser=parserFactory::createParser($str);
    	$tableParser=new dataScanner($str,$this->db);
    	$tableParser->filterData($ds);
    	$values=$tableParser->getCol("Time Up");
    	return $values[0];
    }

    function isActive() {
        include 'includes/config.php.inc';

        return true;
    }
    
    function calculateReportStartDate(&$DB_timeperiodHost, &$sDate, &$eDate){
    	//"today", "last24hours", "yesterday", "last7days", "last31days", "lastmonth", "thisyear", "lastyear");
    	$oneDay = 86400;
    	
    	switch ($DB_timeperiodHost) {
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
