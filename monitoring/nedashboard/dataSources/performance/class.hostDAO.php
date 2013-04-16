<?php

include_once 'class.abstractDAO.php';
include_once 'class.configHostDAO.php';

class hostDAO extends abstractDAO {
    var $dash_id;
    //get path from config.NetEye.php.inc
    //const root="/var/log/nagios/perfdata/";
    const root= configureDAO::perfdataPath;
    
    
    function getConfigFile() {
        $rc = new ReflectionClass(get_class($this));
        return dirname($rc->getFileName())."/config.xml";
    }
    function validate($hostName) {
        $chostDAO=new configHostDAO();
        $this->log("validate ".$hostName);
        return (array_search($hostName,$chostDAO->elements)!==false);
    }

    function hostDAO($dash_id) {
        $this->dash_id=$dash_id;
        $this->loadFile($dash_id);
    }

    function getId($hostName) {
        return $this->dash_id.".".$hostName;
    }

    function getName($hostId) {
        if (strpos($hostId, $this->dash_id)===false) {
            throw new Exception("wrong host id");
        }

        $start=strlen($this->dash_id)+1;
        $length=strpos($hostId, ".", $start);
        if ($length===false) {
            return substr($hostId, $start);
        }
        $length=$length-$start;
        return substr($hostId, $start, $length);
    }

    function addhost($host_id) {
        $element=$this->xmlDom->createElement("host");
        $element->setAttribute("xml:id",$this->dash_id.".".$host_id);
        $this->xmlDom->getElementById($this->dash_id)->appendChild($element);
        return $host_id;
    }

    function removehost($host_id) {
        $element=$this->xmlDom->getElementById($this->dash_id);
        $element->removeChild($this->xmlDom->getElementById($this->dash_id.".".$host_id));
    }

    function gethosts() {
        $host_id=array();
        foreach ($this->xmlDom->getElementById($this->dash_id)->getElementsByTagName("host") as $host) {
            $host_id[] = $host->attributes->getNamedItem("id")->nodeValue;
        }
        return $host_id;
    }

    function contains($hostId) {
        return in_array($this->dash_id.".".$hostId, $this->gethosts());
    }


}
?>
