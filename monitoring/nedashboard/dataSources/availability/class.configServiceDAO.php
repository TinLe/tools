<?php
include_once(dirname(__FILE__)."/../performance/class.configDAO.php");
include_once(dirname(__FILE__)."/scanner/class.dataScanner.php");
include_once(dirname(__FILE__)."/../../includes/config.NetEye.php.inc");
include_once(dirname(__FILE__)."/../performance/class.globalSettingsDAO.php");

class configServiceDAO extends configDAO {
	
	var $DB_timeperiodService = "last7days";
	var $DB_availCGIUserPass = "guest:guest";

	function setTimeperiod(){
		$globalSettings = new globalSettingsDAO();
		$globalSettings->load();
		//$this->DB_availCGIUserPass=$globalSettings->getValue("DB_availCGIUserPass");
		$this->DB_timeperiodService=$globalSettings->getValue("DB_timeperiodService");
	}

	function loadElements($base) {
		$this->setTimeperiod();
		$curl = curl_init();
		$opt=array();
		//$opt[CURLOPT_USERPWD]="root:admin";
		$opt[CURLOPT_USERPWD]=$this->DB_availCGIUserPass;
		//$opt[CURLOPT_URL]="http://".$_SERVER["SERVER_NAME"]."/neteye/cgi-bin/avail.cgi?show_log_entries=&host=DemoLinuxRedHat&service=all&timeperiod=last7days&smon=1&sday=1&syear=2009&shour=0&smin=0&ssec=0&emon=1&eday=22&eyear=2009&ehour=24&emin=0&esec=0&rpttimeperiod=&assumeinitialstates=yes&assumestateretention=yes&assumestatesduringnotrunning=yes&includesoftstates=no&initialassumedservicestate=0&backtrack=4";
		$opt[CURLOPT_URL]="http://".$_SERVER["SERVER_NAME"]."/neteye/cgi-bin/avail.cgi?show_log_entries=&host=DemoLinuxRedHat&service=all&timeperiod=".$this->DB_timeperiodService."&smon=1&sday=1&syear=2009&shour=0&smin=0&ssec=0&emon=1&eday=22&eyear=2009&ehour=24&emin=0&esec=0&rpttimeperiod=&assumeinitialstates=yes&assumestateretention=yes&assumestatesduringnotrunning=yes&includesoftstates=no&initialassumedservicestate=0&backtrack=4";
		$opt[CURLOPT_RETURNTRANSFER] =1;
		$opt[CURLOPT_HTTPAUTH] = CURLAUTH_BASIC | CURLAUTH_DIGEST | CURLAUTH_GSSNEGOTIATE | CURLAUTH_NTLM;
		logManager::writeToLog("OK: Parsing Configuration Service report: ".$opt[CURLOPT_URL]);
		curl_setopt_array ( $curl , $opt );
		$str = curl_exec($curl);
		$str = utf8_encode($str);

		$htmlParser=parserFactory::createParser($str);
		$tableParser=new dataScanner($str,"host");
		$tableParser->filterData($base);
        $this->elements=$tableParser->getCol("service");
    }
}
?>
