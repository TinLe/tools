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
include_once('./layout/class.layoutDAO.php');
include_once('./layout/class.elementDAO.php');
include_once('./dataSources/performance/class.hostDAO.php');
include_once('./dataSources/performance/class.serviceDAO.php');

include_once 'speedometer/class.speedometer.php';

if ($selectedBoard!=null) {
    $element= new elementDAO($selectedBoard);
    $speedometer=new speedometer();
    $speedometer->includeScript();

    echo("<table width=$DB_formwidth px>");
    if ($DB_tachosInRow != 0) {
        $width = $DB_formwidth/$DB_tachosInRow;
    } else {
        $width = 0;
    }
    echo "<tr><td width=".$width."px></td><td width=".$width."px></td><td width=".$width."px></td><td width=".$width."px></td><td width=".$width."px></td></tr>";
    $pos=0;
    //Define tachos per row from config File
    if (isset($DB_tachosInRow) && $DB_tachosInRow > 0){ $max = $DB_tachosInRow; } else {$max=3; }
    foreach($element->getElements() as $e) {
  
        if ($pos==0) {
            echo "<tr>";
        }
        if (stripos($e["type"],"tacho")!==false) {
            $pos++;
            echo "<td>";
            $tachoId=$selectedBoard.".".$e["name"];

            $hostDAO = new hostDAO($selectedBoard);
            $hostName = $hostDAO->getName($tachoId);
            $serviceDAO = new serviceDAO($hostDAO->getId($hostName));
            $serviceName=$serviceDAO->getName($tachoId);
            $sourceDAO = new sourceDAO($serviceDAO->getId($serviceName));

            $sId=$sourceDAO->getId($sourceDAO->getName($tachoId));
            $s=$sourceDAO->getDetails($sourceDAO->getName($tachoId));
            $meter = new meter($sourceDAO->getName($tachoId), $s->getMin(),$s->getMax(),substr($s->getLabel(),0,13),$s->getUnit(),$s->getGreen(),$s->getYellow(),$s->getRed(),$s->getStep(),"/var/log/nagios/perfdata/".$hostName."/".$serviceName);

            echo $speedometer->createMeter($meter);
            echo "<table align=\"center\"><tr><td><a style=\"padding-left:10px;\" href=\"".$statusURL."?host=".$hostName."\" class=\"button\">";
            echo "<span>";
            for($x=0;$x<25;$x++) {
                echo "&nbsp;";
                if ($x==2){
                echo substr($hostName."       ",0, 20);
                $x=24;   }
            }
        	
            echo "</span>";
            echo "</a></td></tr></table>";
            echo "</td>";
        //} else if ($e["type"]==1) {
        } else if ($e["type"]=="title") {
            if ($pos!=0) {
                while ($pos<$max) {
                    $pos++;
                    echo "<td><br></td>";
                }
                echo "</tr><tr>";
                $pos=0;
            }
            echo "<td colspan=".$max."><div class='header'><div class='titleheader'>".$e["name"]."</div></div></td>";
        //} else if ($e["type"]==0) {
        } else if ($e["type"]=="space") {
            $pos++;
            echo "<td><img src=\"images/spacer.gif\" width=\"200px\"/></td>";
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
