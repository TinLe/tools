<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
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
include 'includes/version.php.inc';
require_once './includes/config.php.inc';
include_once("layout/class.space.element");
include_once("layout/class.title.element");
include_once("layout/class.image.element");
include_once("layout/class.tacho.element"); ?>
<html>
    <head>
        <title>NetEye Dashboard v. <?php echo $version; ?></title>
		<link rel="stylesheet" type="text/css" href="./css/main.css" />
        <style>
            .sortHelper
            {
                background: gray;
                float: left;
            }
            .itemContainer {
            width: <?php $DB_tachosInRow*200+$DB_tachosInRow*4?>px;
            height: 5000px;
            float:none;
        }
        .itemContainer .item {
            margin: 2px;
            width: 200px;
            height:65px;
            float: left;
        }
        .item .header {
            border: solid 1px gray;
            height:20px;
            background:url("images/itemHeader.png");
        }
        .item .body {
            height:40px;
            background:url("images/itemBody.png");
        }

        .droppable-active {
            opacity: 1.0;
        }
        .droppable-hover {
            outline: 1px dotted black;
        }
        div#drop {
            background: url('./images/trash.png') no-repeat top right;
            width: 48px;
            height: 50px;
            margin: 10px;
            opacity: 0.7;
            overflow:auto;
        }
        div#drop:hover {
            cursor: pointer;
        }
        </style>
        <script src="js/jQuery/jquery.js"></script>
        <script src="js/jQuery/jquery.ui.core.js"></script>
        <script src="js/jQuery/jquery.ui.sortable.js"></script>
        <script src="js/jQuery/jquery.ui.draggable.js"></script>
        <script src="js/jQuery/jquery.ui.droppable.js"></script>
        <script src="js/interface.js"></script>
        <script>
            $(document).ready(
            function () {
                jQuery('#drop').Droppable({
                    activeclass: 'droppable-active',
                    hoverclass: 'droppable-hover',
                    accept: "item",
                    tolerance:		'intersect',
                    ondrop: function(drag) {
                        var header=null;
                        var body=null;
                        for (i=0;i<drag.childNodes.length;i++) {
                            if (drag.childNodes[i] instanceof HTMLDivElement) {
                                if (header==null) {
                                    header=drag.childNodes[i];
                                } else if (body==null) {
                                    body=drag.childNodes[i];
                                    break;
                                }
                            }
                        }

                        if (header.childNodes[0].value.toLowerCase().indexOf("tacho")==-1) {
                            $(this).css("background-position","right -51px");
                            drag.style.display="none";
                            for (i=0;i<header.childNodes.length;i++) {
                                header.removeChild(header.childNodes[i]);
                            }

                            for (i=0;i<body.childNodes.length;i++) {
                                body.removeChild(body.childNodes[i]);
                            }
                            drag.parentNode.removeChild(drag);
                        }
                    }
                });
                jQuery('#haha').Sortable(
                {
                    accept: 'item',
                    helperclass: 'sortHelper',
                    activeclass : 	'sortableactive',
                    hoverclass : 	'sortablehover',
                    revert: true,
                    floats: true,
                    handle: 'div.header',
                    tolerance: 'pointer'
                });
            }
        );
<?php
$space=new space();
$title=new title($DB_tachosInRow * 200 + $DB_tachosInRow * 4 - 2 * 2);
$image=new image(2 * 200 + 2 * 4 - 2 * 2);
$tacho=new tacho();

echo $space->createJSFunction();
echo $space->createJSIdFunction();
echo $title->createJSFunction();
echo $title->createJSIdFunction();
echo $image->createJSFunction();
echo $image->createJSIdFunction();
?>

    var count=[];
    function addItem(type) {
        var id = count[type];
        if (id==null)
            id=100;

        var htmlString=null;
        var idString=null;
        switch(type) {
            case 0: {htmlString=createSpace(id);idString=createspaceId(id); break;}
            case 1: {htmlString=createHeader(id);idString=createtitleId(id); break;}
            case 2: {htmlString=createImage(id);idString=createimageId(id); break;}
            default: throw new Exception("type not supported");
        }

        $('#haha').append(htmlString).SortableAddItem(document.getElementById(idString));
        count[type]=++id;
    }

    function submitForm(name) {
        document.forms[name].submit();
    }
        </script>
    </head>
    <body bgcolor="#c3c7d3">
        <?php
        $whereIam="layout";
        $jQuery=false;
        include 'includes/header.php';
        include_once 'layout/class.elementDAO.php';

        $elementDAO = null;
        if ($selectedBoard!=null) {
            $elementDAO = new elementDAO($selectedBoard);
        }
        ?>
        <hr align="left" width="<?php $DB_tachosInRow * 200 + $DB_tachosInRow * 4 - 2 * 2?>px">
        <table width="<?php $DB_tachosInRow * 200 + $DB_tachosInRow * 4 - 2 * 2?>px" cellspacing="0" cellpadding="2">
            <tr>
            	<td align="left">
            	<?php if ((isset($_GET['msg'])) && ($_GET['msg'] != '')){
            		echo "<font style=\"color: white;\" face=\"Verdana\" size=\"3\"><b>".$_GET['msg']."</b></font>";
            	} ?>
            	</td>
                <td valign="top" align="right">
                    <select id="item" style="padding: 4px; width: 90px;">
                        <option id="space">Space</option>
                        <option id="title">Title</option>
                        <option id="image">Graph URL</option>
                    </select>
                </td><td width="160" align="left" valign="top">
                    <a class="button" onclick="addItem(document.getElementById('item').selectedIndex);"><span>Add layout Item</span></a>
                </td>
            </tr>
        </table>
        <table width="<?php=$DB_tachosInRow * 200 + $DB_tachosInRow * 4 - 2 * 2?>">
            <tr><td width="160" valign="top" align="right">
                    <a class="button" onclick="submitForm('layoutForm');"><span>Apply Settings</span></a>
                </td><td width="150" valign="top">
                    <a class="button" onclick="window.location.href=unescape(window.location.href);"><span>Undo All</span></a>
                </td>
                <td align="right" valign="top"><div id="drop"></div></td>
            </tr>
        </table>
        <form name="layoutForm" action="actions/action.layout.php">
            <input type="hidden" name="board" value="<?php= $selectedBoard ?>" />

            <div id="haha" class="itemContainer">
                <?php
                $count=array();
                if ($elementDAO!=null) {
                    foreach ($elementDAO->getElements() as $element) {
                        $type = $element["type"];
                        $type = strtolower($type);
                        if ($type=="tacho") {
                            echo $tacho->createHTML($element["id"],$element["name"]);
                        } else if ($type=="availability") {
                            echo $tacho->createHTML($element["id"],$element["name"]);
                        } else if ($type=="space") {
                            if (!isset($count[$type])) {
                                $count[$type]="space";
                            }
                            echo $space->createHTML($count[$type]);
                            $count[$type]=$count[$type]+1;
                        } else if ($type=="title") {
                            if (!isset($count[$type])) {
                                $count[$type]=0;
                            }
                            echo $title->createHTML($count[$type],$element["name"]);
                            $count[$type]=$count[$type]+1;
                        } else if ($type=="image") {
                            if (!isset($count[$type])) {
                                $count[$type]=0;
                            }
                            echo $image->createHTML($count[$type],$element["name"]);
                            $count[$type]=$count[$type]+1;
                        }
                    }
                }
                ?>
            </div>
        </form>
    </body>
</html>
