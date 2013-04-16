<?php
include_once("../../dataSources/performance/class.boardDAO.php");
include_once("../../dataSources/availability/class.boardDAO.php");
include_once("../../layout/class.layoutDAO.php");
require_once(dirname(__FILE__).'/../../includes/config.NetEye.php.inc');
?>

<?php

/*
 * Formular to add a new Board to the system.
 */

// Process
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
if (empty($action)) {
    // Send back the contact form HTML
    echo "<div style='display:none'>
        <a href='#' title='Close' class='modalCloseX modalClose'>x</a>
        <div class='contact-top'></div>
        <div class='contact-content'>
                <h1 class='contact-title'>Add new board:</h1>
                <div class='contact-loading' style='display:none'></div>
                <div class='contact-message' style='display:none'></div>
                  <form action='#' style='display:none'>
                  <table align='center' style='color: #ffffff;'>
                  <tr>
                  <td><br/></td>
                  </tr>
                  <tr>
                  <td>Name:</td>
                  <td><input type='text' id='boardId' class='contact-input' name='name' tabindex='1001' /></td>
                  </tr>
                  <tr>
                  <tr>
                  <td>Type:</td>
                  <td>
                    <select name='type'>
                        <option value='tService'>performance</option>
                        <option value='tAvailability'>availability</option>
                    </select>
                  </td>
                  </tr>
                  <td><br/></td>
                  </tr>
                  <tr>
                  <td><button type='submit' class='contact-send contact-button' tabindex='1004'>Send</button></td>
                  <td><button type='submit' class='contact-cancel contact-button modalClose' tabindex='1005'>Cancel</button></td>
                  </tr>
                  </table>
                </form>
        </div>
        <div class='contact-bottom'></div>
        </div>";
}
else if ($action == 'send') {
    if (isset($_REQUEST['name'])) {
    	logManager::writeToLog("OK: Adding new board with name: ".$_REQUEST['name']." of type: ".$_REQUEST['type']);
        if (isset($_REQUEST['type']) && $_REQUEST['type']=="tService") {
            $board = new boardDAO();
        } else {
            $board = new boardDAOAvailable();
        }
        $board->addBoard($_REQUEST['name']);
        $board->persist();

        $layout = new layoutDAO();
        $layout->addBoard($_REQUEST['name']);
        $layout->persist();
        echo "Board successfully sent.";
        logManager::writeToLog("OK: Board successfully added to configuration.");
    } else {
    	logManager::writeToLog("INFO: Board add creation cancelled by user.");
        echo "Board not sent.";
    }
}

exit;

?>
