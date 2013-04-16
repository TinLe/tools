<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
include_once 'class.xmlWrapper.php';
include_once 'class.parsedHost.php';
include_once 'class.parsedService.php';

class dataProviderFactory {
    static function createDataProvider($source) {
        if ($source=="host") {
            return new parsedHost($source);
        } else if ($source=="service") {
            return new parsedService("host");
        } else return new xmlWrapper($source.".xml");
        //return new rrdToolWrapper($source.".rrd");
    }
}
?>
