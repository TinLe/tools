<?php
include_once '../../dataSources/performance/class.sourceDAO.php';
include_once '../../dataSources/performance/class.sourceDTO.php';
?>

<?php

/*
 * Formular to edit an existing datasource of the system.
 */
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
if (empty($action)) {
    $selected_board=$_REQUEST["board"];
    $selectedHost=$_REQUEST["host"];
    $selectedService=$_REQUEST["service"];
    $selected_source=$_REQUEST["source"];

    $source = new sourceDAO($selected_board.".".$selectedHost.".".$selectedService);
    $sourceDetails = $source->getDetails($selected_source);

    // Send back the contact form HTML
    echo "<div style='display:none'>
        <a href='#' title='Close' class='modalCloseX modalClose'>x</a>
        <div class='contact-top'></div>
        <div class='contact-content'>
                <h1 class='contact-title'>Insert details:</h1>
                <div class='contact-loading' style='display:none'></div>
                <div class='contact-message' style='display:none'></div>
                <form name='configForm'>
                <input type='hidden' name='board-name' id='boardId' value='".$selected_board."'/>
                <input type='hidden' name='sourceId' value='".$selected_source."'/>

                <table align='center' style='color: #ffffff;'>
                <tr>
                <td><br></td>
                <td><br></td>
                </tr>
                <tr>
                <td align='left'>hostName:</td>
                <td><input type='text' name='hostId' value='".$selectedHost."' 'readonly'/></td>
                </tr>
                <tr>
                <td align='left'>serviceName:</td>
                <td><input type='text' name='serviceId' value='".$selectedService."' 'readonly'/></td>
                </tr>
                <tr>
                <td align='left'>min:</td>
                <td><input id='input1' type='text' name='min' value='".$sourceDetails->getMin()."' /></td>
                </tr>
                <tr>
                <td align='left'>max:</td>
                <td><input id='input2' type='text' name='max' value='".$sourceDetails->getMax()."' /></td>
                </tr>
                <tr>
                <td align='left'>unit:</td>
                <td><input id='input3' type='text' name='unit' value='".$sourceDetails->getUnit()."' /></td>
                </tr>
                <tr align='left'>
                <td>label:</td>
                <td><input id='input4' type='text' name='label' value='".$sourceDetails->getLabel()."' /></td>
                </tr>
                <tr align='left'>
                <td>step:</td>
                <td><input id='input5' type='text' name='step' value='".$sourceDetails->getStep()."' /></td>
                </tr>
                <tr>
                <td align='left'>warning:</td>
                <td><input id='input6' type='text' name='warning' value='".$sourceDetails->getGreen()."' /></td>
                </tr>
                <tr>
                <td align='left'>critical:</td>
                <td><input id='input7' type='text' name='critical' value='".$sourceDetails->getYellow()."' /></td>
                </tr>
                <tr>
                <tr>
                <td align='left'>criticalMin:</td>
                <td><input id='input8' type='text' name='criticalMin' value='".$sourceDetails->getRed()."' /></td>
                </tr>
                <td><br></td>
                <td><br></td>
                </tr>
                <tr>
                <td align='right'><button type='submit' class='contact-send contact-button' tabindex='1004'>Send</button></td>
                <td align='right'><button type='submit' class='contact-cancel contact-button modalClose' tabindex='1005'>Cancel</button></td>
                </tr>
                </table>
                </form>
        </div>
        <div class='contact-bottom'></div>
        </div>";
}
else if ($action == 'send') {
    $source = new sourceDAO($_REQUEST["board-name"].".".$_REQUEST["hostId"].".".$_REQUEST["serviceId"]);

    if (!$source->contains($_REQUEST["sourceId"])) {
        $source->addSource($_REQUEST["sourceId"]);
    }

    $details = new sourceDTO();
    $details->setDetails($_REQUEST["min"],$_REQUEST["max"],$_REQUEST["label"],$_REQUEST["unit"],$_REQUEST["warning"],$_REQUEST["critical"],$_REQUEST["criticalMin"],$_REQUEST["step"]);
    $source->setDetails($_REQUEST["sourceId"], $details);

    $source->persist();
    echo "Board successfully sent.";
}

exit;
?>