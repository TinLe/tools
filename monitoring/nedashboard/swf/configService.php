<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
    <title></title>
</head>
<body bgcolor="#c3c7d3">
<?php
    include 'class.configuration.php';
?>
<link rel="stylesheet" type="text/css" href="./css/dynTabs.css" />
<link rel="stylesheet" type="text/css" href="./css/dynButtons.css" />
<table width="650px" cellspacing="0" cellpadding="2">
<tr>
<th float="left">
<div style="z-index:10;" width="100%" id="links" style="text-decoration:none;">
<a href="./index.php" class="tab"><span>View</span></a>
<a class="tab_pressed"><span>Configuration</span></a>
</div>
<div align="right">
    <input type="image" src="./images/info.gif" style="border: 0px; height: 29px;" title="Documentation & Help" onclick="location='../../wiki/doku.php?id=neteye:nesmstool'">
</div>
<div align="left">
    <img class="line" src="./images/open.png">
</div>
</th>
</tr>
</table>

<form name="hostForm" method="get">
    <input type="hidden" name="board_id" value="<?=$_GET["board_id"]?>"/>
    <label>select Host:</label>
    <select name="selected_host" onchange="document.hostForm.submit();">
    <?php
    $board_id = $_GET["board_id"];
    if (!isset($_GET["selected_host"])) {
        $selected_host="new";
    } else {
        $selected_host=$_GET["selected_host"];
    }
                                                
    $config = new configuration();
    $board= $config->getBoard($board_id);
                                                        
    foreach ($board->services as $host) {
        $selected="";
                                                    
        if ($selected_host==$host["host"]) {
            $selected="selected";
            $currentHost=$host;
        }
        echo "<option value='".$host["host"]."' ".$selected.">--".$host["host"]."--</option>\n";
    }
            
    $selected="";
    if (!isset($currentHost)) {
        $currentHost=array();
        $currentHost["host"]="new";
        $selected="selected";
    }
    ?>
    <option value="new" <?=$selected?> >--new Host--</option>
    </select>
</form>

<form name="serviceForm" method="get">
    <input type="hidden" name="board_id" value="<?=$board_id?>"/>
    <input type="hidden" name="selected_host" value="<?=$selected_host?>"/>
    <label>select Service:</label>
    <select name="selected_service" onchange="document.serviceForm.submit();">
    <?php      
                
    if (!isset($_GET["selected_service"])) {
    $selected_service="new";
    } else {
    $selected_service=$_GET["selected_service"];
    }
                                                    
    $config = new configuration();
                                                                
    foreach ($currentHost->service as $service) {
    $selected="";
                                                    
    if ($selected_service==$service["name"]) {
    $selected="selected";
    $currentService=$service;
    }
                
    echo "<option value='".$service["name"]."' ".$selected.">--".$service["name"]."--</option>\n";
    }
            
    $selected="";
    if (!isset($currentService)) {
    $currentService=array();
    $currentService["name"]="new";
    $currentService["source"]="new";
    $currentService["min"]="new";
    $currentService["max"]="new";
    $currentService["unit"]="new";
    $currentService["step"]="new";
    $currentService["ds"]="new";
    $currentService["critical"]="new";
    $currentService["warning"]="new";
        
    $selected="selected";
    }
    ?>
    <option value="new" <?=$selected?> >--new Service--</option>
    </select>
</form>

<form name="configForm" action="configAction.php">
<input type="hidden" name="board_id" value="<?=$board_id?>"/>
<label>hostName:</label>
<input type="text" name="host" value="<?=$currentHost["host"]?>" />
<p>
<label>serviceName:</label>
<input type="text" name="service" value="<?=$currentService["name"]?>" />
<p>
<label>source:</label>
<input type="text" name="source" value="<?=$currentService["source"]?>" />
<p>
<label>min:</label>
<input type="text" name="min" value="<?=$currentService["min"]?>" />
<p>
<label>max:</label>
<input type="text" name="max" value="<?=$currentService["max"]?>" />
<p>
<label>unit:</label>
<input type="text" name="unit" value="<?=$currentService["unit"]?>" />
<p>
<label>step:</label>
<input type="text" name="step" value="<?=$currentService["step"]?>" />
<p>
<label>ds:</label>
<input type="text" name="ds" value="<?=$currentService["ds"]?>" />
<p>
<label>critical:</label>
<input type="text" name="critical" value="<?=$currentService["critical"]?>" />
<p>
<label>warning:</label>
<input type="text" name="warning" value="<?=$currentService["warning"]?>" />
<p>

<a href="javascript:document.configForm.submit();" class="button" style="width:100;"><span>Submit</span></a>
<a href="javascript:document.configForm.reset();" class="button" style="width:100;"><span>Reset</span></a>
</form>


</body>
</html>
