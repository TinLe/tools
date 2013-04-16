<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

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

include_once './includes/version.php.inc';
require_once './includes/config.php.inc';
include_once './dataSources/performance/class.globalSettingsDAO.php';
?>

<html>
    <head>
    <title>NetEye Dashboard v. <? echo $version; ?></title>
		<link rel="stylesheet" type="text/css" href="./css/main.css" />
		
    </head>
    <body bgcolor="#c3c7d3">
        <?php
        $whereIam="global";
        $jQuery=true;
        //$timeperiods= array("today", "last24hours", "yesterday", "thisweek", "last7days", "lastweek", "last31days", "thismonth", "lastmonth", "thisyear", "lastyear");
        $timeperiods= array("today", "last24hours", "yesterday", "last7days", "last31days", "lastmonth", "thisyear", "lastyear");

        include 'includes/header.php';

        include_once("dataSources/performance/class.boardDAO.php");
        include_once("dataSources/avaiability/class.boardDAO.php");
        include_once("layout/class.elementDAO.php");

        $board = new boardDAO();
        $avaiabilityDAO = new boardDAOAvaiable();
        $allBoards = array_merge($board->getBoards(),$avaiabilityDAO->getBoards());

        $globalSettings = new globalSettingsDAO();
        $globalSettings->load();

        //print warning message after uncorrect submit
        if (isset($_GET['msg']) && $_GET['msg'] != ""){
            echo "<br><font style=\"color: white;\" face=\"Verdana\" size=\"3\"><b>".$_GET['msg']."</b></font><br>";
        } else { echo "<br><br>"; }
        ?>
        <form name="globalSettingsForm" action="./actions/action.globalSettings.php">
            <table>
            <tr><td colspan="2"><font face="Verdana" size="3"><b>General settings:</b></font></td></tr>
                <tr>
                    <td>Select default board: </td>
                    <td><select name="fDefault" style="padding: 4px;">
                            <?php
                            foreach($allBoards as $currentBoard) {
                                echo "<option value=".$currentBoard." ".(("\"".$currentBoard."\""==$globalSettings->getValue("DB_defaultBoard"))?"selected":"").">".$currentBoard."</option>";
                            }
                            ?>
                    </select></td>
                </tr>
                <tr>
                    <td>Set num of tachos columns: </td>
                    <td><input type="text" name="fMax" maxlength="2" style="width: 20px; padding: 1px;" value="<?=$globalSettings->getValue("DB_tachosInRow")?>"/> &nbsp;tachos per row</td>
                </tr>
                <tr>
                    <td>Set time interval for rotation:<br> (in seconds) </td>
                    <td><input type="text" name="fTime" maxlength="4" style="width: 40px; padding: 1px;" value="<?=$globalSettings->getValue("DB_diashowRefreshTime")?>"/> &nbsp;seconds</td>
                </tr>
                <tr><td height="12"></td></tr>
                <tr><td colspan="2"><font face="Verdana" size="3"><b>Availability board settings:</b></font></td></tr>
                <tr>
                    <td>Avaiability critical min boundary: </td>
                    <td><input type="text" name="fAvaiability" maxlength="2" style="width: 20px; padding: 1px;" value="<?=$globalSettings->getValue("DB_avaiability")?>"/>&nbsp;%</td>
                </tr>
                <tr>
                    <td>Host report period: </td>
                    <td><select name="fTimeperiodHost" style="padding: 4px;">
                            <?php
                            foreach($timeperiods as $timeperiod) {
                                echo "<option value=".$timeperiod." ".(("\"".$timeperiod."\""==$globalSettings->getValue("DB_timeperiodHost"))?"selected":"").">".$timeperiod."</option>";
                            }
                            ?>
                    </select>
                    </td>
                </tr>
                <tr>
                    <td>Service report period: </td>
                    <td><select name="fTimeperiodService" style="padding: 4px;">
                            <?php
                            foreach($timeperiods as $timeperiod) {
                                echo "<option value=".$timeperiod." ".(("\"".$timeperiod."\""==$globalSettings->getValue("DB_timeperiodService"))?"selected":"").">".$timeperiod."</option>";
                            }
                            ?>
                    </select>
                    </td>
                </tr>
                <tr><td height="10"></td></tr>
                <tr>
                    <td colspan="2">
                    <a class="button" onclick="document.forms['globalSettingsForm'].submit();"><span>submit</span></a></td>
                </tr>
            </table>
        </form>
    </body>
</html>