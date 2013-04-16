<?php
include_once(dirname(__FILE__)."/../../includes/config.NetEye.php.inc");

abstract class configDAO {
	//get path from config.NetEye.php.inc
    //const root="/var/log/nagios/perfdata/";
    const root= configureDAO::perfdataPath;
    var $elements;

	function configDAO($base=".") {
        $this->elements=array();
        $this->loadElements($base);
    }
    
    function getFirstElement() {
        return $this->elements[0];
    }

    function getElements() {
        return $this->elements;
    }

    abstract function loadElements($base);
}

?>
