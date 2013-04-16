<?php
/*
-------------------------------------------------------------------------
Dashboard - Nagios Tachos Dashboard
Copyright (C) 2009 by WUERTHPHOENIX Srl.

http://www.wuerth-phoenix.com
--------------------------------------------------------------------------

 LICENSE

 This file is part of WuerthPhoenix NetEye Dashboard.

 The dashboard is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation version 3 of the License.

 NetEye Dashboard is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You can get a copy of the GNU General Public License included to the Dashboard or
 at http://www.gnu.org/licenses/gpl-3.0.txt. Otherwise write to the Free Software
 Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 --------------------------------------------------------------------------
*/ 
include_once 'dataSources/availability/class.hostDAO.php';
include_once 'dataSources/availability/class.serviceDAO.php';
include_once 'dataSources/availability/class.configHostDAO.php';
include_once 'dataSources/availability/class.configServiceDAO.php';
include_once './ajax/configForm/class.configFormFactory.php';
include_once(dirname(__FILE__)."/includes/config.php.inc");

session_start();
$selected_host=null;
$selected_service=null;

if ($selectedBoard!=null) {
    $hostDAO = new hostDAOAvailable($selectedBoard);
    $host = new configHostDAOAvailable(".");
    $selected_host=null;
    if (isset($_GET["host"])) {
        $selected_host = $_GET["host"];

        $serviceDAO = new serviceDAO($selectedBoard,$selected_host);
        $service = new configServiceDAO($selected_host);
        $service->setTimeperiodService($DB_timeperiodService);
        $selected_service=null;
        if (isset($_GET["service"])) {
            $selected_service = $_GET["service"];
        }
    }
}

$sHostNames=array();
$aHostNames=array();
if ($selectedBoard!=null) {
    foreach ($hostDAO->gethosts() as $hostId) {
        //get all hostnames already selected
        $sHostNames[]=$hostDAO->getName($hostId);
    }
    foreach ($host->getElements() as $hostName) {
        //get all hostnames available
        if (!$hostDAO->contains($hostName)) {
            //if they are not contained in selected hostNames
            $aHostNames[]=$hostName;
        }
    }
}

//print host selection form
$configForm = configFormFactory::getForm("hostFormAvailable",$sHostNames,$aHostNames);
echo $configForm->createJS("callBackHost", "callBackHost");

$info=array();
$info["board"]=$selectedBoard;

echo $configForm->createFormTable($info, $selected_host, "select Host");
?>

<?php
$sServiceNames=array();
$aServiceNames=array();
if ($selected_host!=null) {
    foreach ($serviceDAO->getServices() as $serviceId) {
        //get all servicenames already selected
        $sServiceNames[]=$serviceDAO->getName($serviceId);
    }
    foreach ($service->getElements() as $serviceName) {
        //get all servicenames available
        //serviceName is the name of the xmlFile containing service data
        //therefore we must split the . to get service name
        //ex split(ping.xml) => [ping][xml]
        $h=preg_split("/\./",$serviceName);
        $serviceName=$h[0];
        if (!in_array($serviceName,$aServiceNames) && !$serviceDAO->contains($serviceName)) {
            //if this is not a duplicate and
            //if they are not contained in selected serviceNames
            $aServiceNames[]=$serviceName;
        }
    }
}

//print service selection form
$configForm = configFormFactory::getForm("serviceFormAvailable",$sServiceNames,$aServiceNames);
echo $configForm->createJS(null, "callBackService");

$info=array();
$info["board"]=$selectedBoard;
$info["host"]=$selected_host;
echo $configForm->createFormTable($info, $selected_service, "select Service");
?>
<script type="text/javaScript">
    function callBackHost(host) {
        document.serviceFormAvailable["host"].value=host.value;
        submitserviceFormAvailable(-1);
    }

    function callBackService(service) {
    }

</script>
