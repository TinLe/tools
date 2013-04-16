<?php
include_once(dirname(__FILE__)."/../performance/class.configDAO.php");
include_once(dirname(__FILE__)."/scanner/class.headerScanner.php");
include_once(dirname(__FILE__)."/../../includes/config.NetEye.php.inc");
include_once(dirname(__FILE__)."/../performance/class.globalSettingsDAO.php");


class configHostDAOAvailable extends configDAO {
	
	var $DB_timeperiodHost = "last7days";
	var $DB_availCGIUser = "guest:guest";
	
	function setTimeperiod(){
		$globalSettings = new globalSettingsDAO();
        $globalSettings->load();
		$this->DB_timeperiodHost=$globalSettings->getValue("DB_timeperiodHost");
	}
	function setLoginCredentials(){
		$globalSettings = new globalSettingsDAO();
        $globalSettings->load();
		$this->DB_availCGIUser=$globalSettings->getValue("DB_availCGIUserPass");
        logManager::writeToLog("Scanner Debug: ".$this->DB_availCGIUser);
	}
	
    function loadElements($base) {
    	$this->setTimeperiod();
    	//$this->setLoginCredentials();
        $curl = curl_init();
        $opt=array();
        $opt[CURLOPT_USERPWD]=$this->DB_availCGIUser;
        //$opt[CURLOPT_URL]="http://".$_SERVER["SERVER_NAME"]."/neteye/cgi-bin/avail.cgi?show_log_entries=&host=DemoLinuxRedHat&service=all&timeperiod=last7days&smon=1&sday=1&syear=2009&shour=0&smin=0&ssec=0&emon=1&eday=22&eyear=2009&ehour=24&emin=0&esec=0&rpttimeperiod=&assumeinitialstates=yes&assumestateretention=yes&assumestatesduringnotrunning=yes&includesoftstates=no&initialassumedservicestate=0&backtrack=4";
        $opt[CURLOPT_URL]="http://".$_SERVER["SERVER_NAME"]."/neteye/cgi-bin/avail.cgi?show_log_entries=&host=DemoLinuxRedHat&service=all&timeperiod=".$this->DB_timeperiodHost."&smon=1&sday=1&syear=2009&shour=0&smin=0&ssec=0&emon=1&eday=22&eyear=2009&ehour=24&emin=0&esec=0&rpttimeperiod=&assumeinitialstates=yes&assumestateretention=yes&assumestatesduringnotrunning=yes&includesoftstates=no&initialassumedservicestate=0&backtrack=4";
        $opt[CURLOPT_RETURNTRANSFER] =1;
        $opt[CURLOPT_HTTPAUTH] = CURLAUTH_BASIC | CURLAUTH_DIGEST | CURLAUTH_GSSNEGOTIATE | CURLAUTH_NTLM;
        logManager::writeToLog("OK: Parsing Configuration Host report: ".$opt[CURLOPT_URL]);
        curl_setopt_array ( $curl , $opt );
        $str = curl_exec($curl);
        $str = utf8_encode($str);

        $htmlParser=parserFactory::createParser($str);
        $tableParser=new headerScanner($str,"host");
        $tableParser->filterData();
        $this->elements=$tableParser->getData();
    }
}
?>
