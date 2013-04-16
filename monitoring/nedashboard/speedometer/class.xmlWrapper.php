<?php
include_once dirname(__FILE__).'/../dataSources/performance/class.sourceDTO.php';
include_once 'class.abstractDataProvider.php';

class xmlWrapper extends abstractDataProvider {
    var $db;
    function xmlWrapper($db) {
        $this->db=$db;
    }

    function status() {
        return 5;
    }

    function fetch($ds) {
        $sourceDTO = new sourceDTO();
        $sourceDTO->loadDefaults($this->db, $ds);
        return $sourceDTO->getCurrent();
    }

    function isActive() {
        include 'includes/config.php.inc';
        return time()-filemtime($this->db) < $DB_minutesToBeInactive*60;
    }
}
?>
