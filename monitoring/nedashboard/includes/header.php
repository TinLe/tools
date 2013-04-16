<!--
Header file which creates the dynamic tab menue and some input boxes for board management:
selection of board,
add of new board,
remove of current board

Author: Marmsoler Diego
-->
<?php require_once './includes/config.php.inc'; ?>
<!-- Import jQuery and SimpleModal source files -->
<?php if (!isset($jQuery) || $jQuery==true) {
    echo "<script src='js/jQuery/jquery.js' type='text/javascript'></script>";
}?>
<script>
	function fullscreenEmpty() {
        window.open('indexFull.php','DashBoard','fullscreen=yes, location=no, scrollbars=yes');
    }
    function fullscreen(selBoard) {
        window.open('indexFull.php?board='+selBoard,'DashBoard','fullscreen=yes, location=no, scrollbars=yes');
    }
    function removeBoard(boardDel) {
        if (confirm ("Do you want to delete the board: "+boardDel+" ?")){
            window.location='actions/action.removeBoard.php?board='+boardDel;
        }
    }
</script>
<script src='js/jQuery/jquery.simplemodal.js' type='text/javascript'></script>
<!-- Contact Form JS and CSS files -->
<script src='ajax/boardForm/dynamicForm.js' type='text/javascript'></script>
<link type='text/css' href='css/contact.css' rel='stylesheet' media='screen' />
<link rel="stylesheet" type="text/css" href="./css/dynTabs.css" />
<link rel="stylesheet" type="text/css" href="./css/dynButtons.css" />
<?php
include 'dataSources/performance/class.boardDAO.php';
include 'dataSources/availability/class.boardDAO.php';
include('./includes/logo.php.inc');

$board = new boardDAO();
$boardAvailable=new boardDAOAvailable();

$selectedBoard=null;
if ((isset($_GET["board"]))&&($_GET['board'] != "")) {
	$selectedBoard = $_GET["board"];
	//Patrick: If the default board is deleted, the search is done for a non existing board!
} else if (isset($DB_defaultBoard)) {
	$selectedBoard = $DB_defaultBoard;
} else {
	$allBoards = $board->getBoards();
	if (count($allBoards)>0) {
		$selectedBoard = $allBoards[0];
	} else {
		$allBoards =$boardAvailable->getBoards();
		if (count($allBoards)>0) {
			$selectedBoard = $allBoards[0];
		}
	}
}
?>
<table style="margin-top: 5px;" <?php echo $DB_formwidth; ?>px" cellspacing="0" cellpadding="0">
    <tr>
        <td>
            <div style="z-index: 10;" id="links" style="text-decoration:none;">
                <?php
                if ($whereIam=="index") {
                    echo '<a class="tab_pressed"><span>View</span></a>';
                } else {
                    echo '<a href="./index.php?board='.$selectedBoard.'" class="tab"><span>View</span></a>';
                } if ($whereIam=="configuration") {
                    echo '<a class="tab_pressed"><span>Configuration</span></a>';
                } else {
                    echo '<a href="./configDash.php?board='.$selectedBoard.'" class="tab"><span>Configuration</span></a>';
                } if ($whereIam=="layout") {
                    echo '<a class="tab_pressed"><span>Layout</span></a>';
                } else {
                    echo '<a href="./configLayout.php?board='.$selectedBoard.'" class="tab"><span>Layout</span></a>';
                } if ($whereIam=="global") {
                    echo '<a class="tab_pressed"><span>Global</span></a>';
                } else {
                    echo '<a href="./configGlobal.php?board='.$selectedBoard.'" class="tab"><span>Global</span></a>';
                }
                ?>
            </div>
            <table align="right" cellspacing="0" cellpadding="0">
                <tr>
                    <td><img width="20px" height="23px" src="images/fullscreen_small.jpg" onclick="fullscreen('<?php echo $selectedBoard;?>');"/></td>
                    <?php if (file_exists($nedashDocPath)) { ?>
                    <td><div align="right" ><input type="image" src="./images/info.gif" style="border: 0px; width: 25px;"
                                                           title="Documentation & Help"
                                                   onclick="location='<?php echo $dashWikiURL; ?>'"></div>
                    </td><?php } ?>
                </tr>
            </table>
            </td>
            </tr>
            <tr><td>       
        	<div align="left"><img class="line" src="./images/open.png"></div>
        </td>
    </tr>
</table>

<table width="<?php echo $DB_formwidth; ?>px" cellspacing="0" cellpadding="2">
    <tr>
        <td width="300">
        <table border="1"><tr><td valign="middle" align="left">
            <?php
            echo "<form name='boardSelection'>\n";
            echo "<font size=\"2px\">Sel. Board:<select id='board' name='board' onchange='document.boardSelection.submit();' style=\"padding: 2px; width: 180px;\">\n</font>";
            
            //Hack to fix the onChange action problem: If only one board is available the board is not shown after creation since 
            //no onChange() action is recognized. For this an empty <option> is included
            $numPerfBoards=$board->getBoards();
            $numAvailBoards=$boardAvailable->getBoards();
            if ((intval(count($numPerfBoards))+intval(count($numAvailBoards)))== 1){
            	echo "<option value=\"\"></option>";
            }
          
            foreach ($numPerfBoards as $board_id) {
                $select = $selectedBoard==$board_id?"selected":"";
                echo "<option value='".$board_id."' ".$select.">".$board_id."</option>\n";
            }
            foreach ($numAvailBoards as $board_id) {
                $select = $selectedBoard==$board_id?"selected":"";
                echo "<option value='".$board_id."' ".$select.">".$board_id."</option>\n";
            }
            echo "</select>\n";
            echo "</form>\n";
            ?>
            </td></tr>
        </table>
        </td>
        <td style="vertical-align:middle;" title="Availability Report Timeperiod: Host: '<?php echo $DB_timeperiodHost; ?>' | Service: '<?php echo $DB_timeperiodService; ?>'">
            <?php if ((isset($selectedBoard)) && ($selectedBoard != "")){ ?><font size="3px">Type:&nbsp;<b><?php $board->contains($selectedBoard) ? "performance" : "availability" ?></b></font> <?php } ?>
        </td>
        <td align="right">
            <table width="300" align="right"><tr><td align="right">
                        <a href="#" class="button" onclick="openDialogForm(event,'ajax/boardForm/addBoard.php',true);"><span>Add new board</span></a>
                    </td><td align="right">
                    	<a href="#" class="button" onclick="removeBoard(document.getElementById('board')[document.getElementById('board').selectedIndex].value);"><span>Remove Board</span></a>
                        <!-- <a href="javascript:removeBoard(document.getElementById('board')[document.getElementById('board').selectedIndex].value);" class="button"><span>Remove Board</span></a> -->
            </td></tr></table>
        </td>
    </tr>
</table>
