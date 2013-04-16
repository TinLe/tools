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
include 'includes/version.php.inc'; ?>
<html>
    <head>
        <title>NetEye Dashboard v. <?php echo $version; ?></title>
        <link rel="stylesheet" type="text/css" href="./css/main.css" />
    </head>
    <body bgcolor="#c3c7d3">
    <?php
        $whereIam="configuration";
        include 'includes/header.php';

        if ($selectedBoard != ""){
        	if ($board->contains($selectedBoard)) {
        		include 'configService.php';
        	} else {
        		include 'configAvailability.php';
        	}
        }
    ?>
    </body>
</html>
