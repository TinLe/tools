<?php /*
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
?>
<script type="text/javaScript">
    var gHost;
    var gService;
    var gSource;

    function callBackHost(host) {
        if (host!='null') {
            gHost=host.value;
            gService=null;
            document.serviceForm["host"].value=host.value;
            document.sourceForm["host"].value=host.value;
        }
        submitserviceForm(-1);
    }

    function callBackService(service) {
        if (service!='null') {
            gService=service.value;
            document.sourceForm["service"].value=service.value;
        }
		if (gService==null) {
	        submitsourceForm(-1);
        } else {
			submitsourceForm(100);
        }
    }

    function callBackSource(source) {
        if (source!='null') {
            gSource=source.value;
            document.getElementById("btnEdit").className="button";
        }
    }

    function edit(event, board) {
        if (gHost!==undefined && gService!==undefined && gSource!==undefined) {
            openDialogForm(event,'ajax/boardForm/configuration.php?board='+board+'&host='+gHost+'&service='+gService+'&source='+gSource, false);
        }
    }

</script>

<?php
include 'dataSources/performance/class.hostDAO.php';
include 'dataSources/performance/class.serviceDAO.php';
include 'dataSources/performance/class.sourceDAO.php';
include_once 'dataSources/performance/class.sourceDTO.php';
include_once './ajax/configForm/class.configFormFactory.php';

$selected_host=null;
$selected_service=null;
$selected_source=null;

if ($selectedBoard!=null) {
    $hostDAO = new hostDAO($selectedBoard);
    $host = new configHostDAO(".");
    $selected_host=null;
    if (isset($_GET["host"])) {
        $selected_host = $_GET["host"];

        $serviceDAO = new serviceDAO($selectedBoard.".".$selected_host);
        $service = new configServiceDAO($selected_host);
        $selected_service=null;
        if (isset($_GET["service"])) {

            $selected_service = $_GET["service"];
            $sourceDAO = new sourceDAO($selectedBoard.".".$selected_host.".".$selected_service);
            $source = new configSourceDAO($selected_host."/".$selected_service);
            $selected_source=null;
            if (isset($_GET["source"])) {
                $selected_source = $_GET["source"];
            }
        }
    }
}

//print incompatibility warning for internet explorer
$browser_agent = configFormFactory::getBrowser($_SERVER['HTTP_USER_AGENT']); 
if ($browser_agent!="Mozilla Firefox"){
	echo "<table><tr valign=\"top\"><td>
	<img src=\"images/info.png\"><font size=\"2px\"><b>ADVICE:</b> It is not suggested to use ".$browser_agent." with the configuration editor. Firefox 3.x would be recommended.</font>
	</td></tr></table>";
} else if (($browser_agent=="Internet Explorer 6") || ($browser_agent=="Internet Explorer 5")){
	echo "<table><tr valign=\"middle\"><td>
	<img src=\"images/warning.png\"><font size=\"2px\"><b>ADVICE:</b> Please use a more recent browser like Firefox 3.x.</font>
	</td></tr></table>";
}

$sHostNames=array();
$aHostNames=array();
if ($selectedBoard!=null) {
    foreach ($hostDAO->gethosts() as $hostId) {
        //get all hostnames already selected
        $sHostNames[]=$hostDAO->getName($hostId);
    }
    foreach ($host->getElements() as $hostName) {
        //get all hostnames avaiable
        if (!$hostDAO->contains($hostName)) {
            //if they are not contained in selected hostNames
            $aHostNames[]=$hostName;
        }
    }
}

//print host selection form
$configForm = configFormFactory::getForm("hostForm",$sHostNames,$aHostNames);
echo $configForm->createJS("callBackHost", null);

$info=array();
$info["board"]=$selectedBoard;

echo $configForm->createFormTable($info, $selected_host."Select Host");
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
        //get all servicenames avaiable
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
$configForm = configFormFactory::getForm("serviceForm",$sServiceNames,$aServiceNames);
echo $configForm->createJS("callBackService", null);

$info=array();
$info["board"]=$selectedBoard;
$info["host"]=$selected_host;
echo $configForm->createFormTable($info, $selected_service."Select Service");
?>

<?php
$sSourceNames=array();
$sSourceLabels=array();
$aSourceNames=array();
$aSourceLabels=array();
if ($selected_service!=null) {
    foreach ($sourceDAO->getSources() as $sourceId) {
        //get all sourcenames already selected
        $sSourceNames[]=$sourceDAO->getName($sourceId);
        $sSourceLabels[]=$sourceDAO->getDetails($sourceDAO->getName($sourceId))->getLabel();
    }
    foreach ($source->getElements() as $sourceName) {
        //get all sources avaiable
        if (!$sourceDAO->contains($sourceName)) {
            //if they are not contained in selected sourceNames
            $aSourceNames[]=$sourceName;
            $details = new sourceDTO();
            $details->loadDefaults(sourceDAO::root."/".$selected_host."/".$selected_service.".xml",$sourceName);
            $aSourceLabels[]=$details->getLabel();
        }
    }
}

//print source selection form
$configForm = configFormFactory::getForm("sourceForm",$sSourceNames,$aSourceNames);
echo $configForm->createJS("callBackSource", null);

$info=array();
$info["board"]=$selectedBoard;
$info["host"]=$selected_host;
$info["service"]=$selected_service;
echo $configForm->createFormTableCustom($info, $sSourceLabels, $aSourceLabels, "Select Source");
?>

<table width="150px">
    <tr>
        <td width="40"></td>
        <td><a id="btnEdit" class="button_disabled" onclick="javascript:edit(event, '<?=$selectedBoard?>');"><span>Edit</span></a></td>
    </tr>
</table>


