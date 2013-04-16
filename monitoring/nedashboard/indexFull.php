<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
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
require_once './includes/config.php.inc';
include 'dataSources/availability/class.boardDAO.php';
include 'dataSources/performance/class.boardDAO.php';
?>
<html>
    <head>
        <link rel="stylesheet" type="text/css" href="./css/dynButtons.css" />
        <link rel="stylesheet" type="text/css" href="./css/main.css" />
        <title>The NetEye Dashboard Monitor</title>
        <style>
            div.titleheader {
                font-size:22px;
                font-weight: bold;
                font-family: Verdana;
                color: #4A4A7B;
                font-weight: 500;
                background-image:url(images/header.png);
                background-color:white;
                background-repeat:repeat-x;
            }
            div.title {
                margin-left:30px;
            }
        </style>
        <script>
            function zeit() {
                var board=document.boardSelection.board;
                board.selectedIndex=(board.selectedIndex+1)%board.options.length;
                document.boardSelection.submit();
                setTimeout('zeit();', <?php $DB_diashowRefreshTime*1000?>);
            }
        </script>
    </head>
    <body bgcolor="#c3c7d3" onload="setTimeout('zeit();', <?php $DB_diashowRefreshTime*1000?>);" ondblclick="window.close();">
        <?php
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
        <table>
        <tr><td>
<table style="background-image:url(images/header.png); height: 25px;" height="10" border="0" width="<?php echo $DB_formwidth; ?>px" cellspacing="0" cellpadding="2">
	<tr>
        <td style="width: 250px">
           <?php
            echo "<form name='boardSelection'>\n";
            echo "<font size=\"2px\">Select Board: <select id='board' name='board' onchange='document.boardSelection.submit();' style=\"padding: 2px;\">\n</font>";
            $boards=$board->getBoards();
            foreach ($boards as $board_id) {
                $select = $selectedBoard==$board_id?"selected":"";
                echo "<option value='".$board_id."' ".$select.">".$board_id."</option>\n";
            }
            $boards=$boardAvailable->getBoards();
            foreach ($boards as $board_id) {
                $select = $selectedBoard==$board_id?"selected":"";
                echo "<option value='".$board_id."' ".$select.">".$board_id."</option>\n";
            }
            echo "</select>\n";
            echo "</form>\n";
            ?>
        </td>
        <td valign="top">
            <?php if ((isset($selectedBoard)) && ($selectedBoard != "")){ 
            ?><font size="3px">Type:&nbsp;<b><?php $board->contains($selectedBoard) ? "performance" : "availability" ?></b></font><?php } ?>
        </td>
        <?php if ($board->contains($selectedBoard)){
        	echo "<td align=\"right\">";
        } else {
        	echo "<td align=\"right\" title=\"Availability Report Timeperiod: <Timeperiod host> | < Timeperiod Service>\">
        	<font size=\"2px\">Avail.Period: <b> $DB_timeperiodHost</b> | <b> $DB_timeperiodService  </b></font>";
        } 
        ?>
        <img width="18px" src="images/close2.gif" onclick="window.close();"/>
        </td>
    </tr>
</table>
</td><td>
<img width="180px" src="images/neteye_480.jpg">
</td></tr>
</table>
<?php
        if ($board->contains($selectedBoard)) {
            include 'dashBoard.php';
        } else {
            include 'dashBoardAvailable.php';
        }
?>
    </body>
</html>
