<?php
include_once 'class.dataDAO.php';
include_once(dirname(__FILE__)."/../../includes/config.NetEye.php.inc");

class serviceDAO extends dataDAO {
    var $host_name;
    var $dash_id;

    function serviceDAO($dash_id,$host_name) {
        $this->dash_id=$dash_id;
        $this->host_name=$host_name;
        $this->loadFile($host_name);
    }

    function getId($serviceName) {
        return $this->dash_id.".".$this->host_name.".".$serviceName;
    }

    function getName($serviceId) {
    	
        if (strpos($serviceId, $this->dash_id.".".$this->host_name)===false) {
            throw new Exception("wrong host id");
        }

        $start=strlen($this->dash_id.".".$this->host_name)+1;
        $length=strpos($serviceId, ".", $start);
        if ($length===false) {
            return substr($serviceId, $start);
        }
        $length=$length-$start;
        logManager::writeToLog("GET NAME: ".$serviceId." Start ".$start." length ".$length);
        return substr($serviceId, $start, $length);
    }

    function addservice($service_id) {
        $element=$this->xmlDom->createElement("service");
        $element->setAttribute("xml:id",parent::bringInForm($this->dash_id.".".$this->host_name.".".$service_id));
        //$s = xmlCoding::xmlEncoding(parent::bringInForm($this->dash_id.".".$this->host_name.".".$service_id));
        //$element->setAttribute("xml:id",$s);
        //logManager::writeToLog("Write: xml:id".xmlCoding::xmlEncoding(parent::bringInForm($this->dash_id.".".$this->host_name.".".$service_id)));
        
        $this->xmlDom->getElementById($this->dash_id)->appendChild($element); 
        parent::setName($this->dash_id.".".$this->host_name.".".$service_id,$this->host_name.".".$service_id);
        return $service_id;
    }

    function removeservice($service_id) {
        $element=$this->xmlDom->getElementById($this->dash_id);
        $element->removeChild($this->xmlDom->getElementById(parent::bringInForm($this->dash_id.".".$this->host_name.".".$service_id)));
        //$element->removeChild($this->xmlDom->getElementById(xmlCoding::xmlEncoding(parent::bringInForm($this->dash_id.".".$this->host_name.".".$service_id))));
    }

    function getServices() {
        $service_id=array();
        $board = $this->xmlDom->getElementById($this->dash_id);
        if ($board) {
            foreach ($board->getElementsByTagName("service") as $service) {
                $id=parent::bringOutForm($service->attributes->getNamedItem("id")->nodeValue);
                if (stristr($id, $this->host_name)!==false) {
                    $service_id[] = $id;
                }
            }
        }
        return $service_id;
    }

    function contains($service_name) {
        return in_array($this->dash_id.".".$this->host_name.".".$service_name, $this->getServices());
    }

    function isEmpty() {
        return count($this->getServices())==0;
    }

}
?>
