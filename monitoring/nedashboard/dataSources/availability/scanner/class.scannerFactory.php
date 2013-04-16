<?php
include_once dirname(__FILE__).'/../parser/class.parserFactory.php';
require_once dirname(__FILE__).'/../../../includes/config.NetEye.php.inc';
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class scannerFactory {
    static function createScanner($text) {
        if (stripos($text, "Service Availability Report")) {
            if (!isset($_SESSION["service_scanner"])) {
            	logManager::writeToLog("INFO: New SERVICE scanner session initialized.");
                $scanner = new tableScanner($text);
                $htmlParser=parserFactory::createParser($text);
                $scanner->scan($htmlParser);
                $_SESSION["service_scanner"]=$scanner;
            }
            return $_SESSION["service_scanner"];
        } else {
            if (!isset($_SESSION["host_scanner"])) {
            	logManager::writeToLog("INFO: New HOST scanner session initialized.");
                $scanner = new tableScanner($text);
                $htmlParser=parserFactory::createParser($text);
                $scanner->scan($htmlParser);
                $_SESSION["host_scanner"]=$scanner;
            }
            return $_SESSION["host_scanner"];
        }
    }
}
?>
