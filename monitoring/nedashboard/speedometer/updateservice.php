<?php
    include 'class.dataProviderFactory.php';
    include_once 'class.urlCleaner.php';
	
    
    session_start();
    
    // send no-cache header
    header("Expires: Sat, 05 Aug 2000 22:27:00 GMT");
    header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
    header("Cache-Control: no-cache, must-revalidate");
    header("Pragma: no-cache");
    header("Cache-Control: post-check=0, pre-check=0");
    header("Cache-Control: private");
                
    $dataProvider=dataProviderFactory::createDataProvider($_GET["source"]);
    $val=$dataProvider->fetch(urlCleaner::decodeUrl($_GET["u"]));
    
	echo "current=".round(floatval($val), 3);
    //echo "current=".round($val,6);
?>