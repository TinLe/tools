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
include_once('./dataSources/availability/class.boardDAO.php');
include_once('./dataSources/availability/class.dataDAO.php');
include_once('./layout/class.layoutDAO.php');
include_once('./layout/class.elementDAO.php');

include_once 'speedometer/class.speedometer.php';

if ($selectedBoard!=null) {
    $element= new elementDAO($selectedBoard);
    $speedometer=new speedometer();
    $speedometer->includeScript();
    
    echo("<table width=$DB_formwidth px>");
    $width=$DB_formwidth/$DB_tachosInRow;
    echo "<tr><td width=".$width."px></td><td width=".$width."px></td><td width=".$width."px></td><td width=".$width."px></td><td width=".$width."px></td></tr>";
    $pos=0;
    //Define tachos per row from config File
    if (isset($DB_tachosInRow) && $DB_tachosInRow > 0){ $max = $DB_tachosInRow; } else {$max=3; }
    foreach($element->getElements() as $e) {

        if ($pos==0) {
            echo "<tr>";
        }
        if (stripos($e["type"],"Availability")!==false) {
            $pos++;
            echo "<td>";
            $tachoId=$selectedBoard.".".$e["name"];

            $dataDAO = new dataDAO($selectedBoard);
            $dataDAO->loadFile($selectedBoard);
            $hostName = $dataDAO->getName($tachoId);
            //$meter = new meter($hostName, 0,100,$dataDAO->getName($tachoId),"",150,151,$DB_availability,"",$dataDAO->getType($tachoId));
            $meter = new meter($hostName, 0,100,substr($dataDAO->getName($tachoId),0,13),"",150,151,$DB_availability,"",$dataDAO->getType($tachoId));
          
            echo $speedometer->createMeter($meter);
            echo "<table align=\"center\" cellpadding=\"0\"><tr><td height=\"5\"><a style=\"padding-left:4px;\" href=\"#\" class=\"button_disabled\">";
            echo "<span>";
            //for($x=0;$x<25;$x++) {
            //    echo "&nbsp;";
            //    if ($x==1){
            echo substr($hostName."        ",0, 20);
            //    $x=24;   }
            //}
            echo "</span>";
            echo "</a>";
            echo "</td></tr></table>";
            echo "</td>";
        //} else if ($e["type"]==1) {
        } else if (stripos($e["type"],"title")!==false){
            if ($pos!=0) {
                while ($pos<$max) {
                    $pos++;
                    echo "<td><br></td>";
                }
                echo "</tr><tr>";
                $pos=0;
            }
            echo "<td colspan=".$max."><div class='header'><div class='titleheader'>".$e["name"]."</div></div></td>";
        } else if ($e["type"]=="space") {
            $pos++;
            echo "<td><img src=\"images/spacer.gif\" height=\"10\" width=\"200px\"/></td>";
        //} else if ($e["type"]==2) {
        } else if (stripos($e["type"],"image")!==false) {
            if ($pos+1>=$max) {
                while ($pos<$max) {
                    $pos++;
                    echo "<td><br></td>";
                }
                echo "</tr><tr>";
                $pos=0;
            }
            echo "<td colspan=2><img width=400px src='".$e["name"]."&end=".time()."'/></td>";
            $pos=$pos+2;
        }

        if ($pos==$max) {
            $pos=0;
            echo "</tr>";
        }
    }
    echo("</table>");
}

?>
