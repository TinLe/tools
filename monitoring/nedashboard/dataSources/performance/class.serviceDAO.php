<?php
include_once 'class.configServiceDAO.php';
include_once 'class.abstractDAO.php';
include_once(dirname(__FILE__)."/../../includes/config.NetEye.php.inc");

class serviceDAO extends abstractDAO {
    var $host_id;
    //const root="/var/log/nagios/perfdata";
    const root=configureDAO::perfdataPath;

    function validate($serviceName) {
        $this->log("validate ".$this->host_id.".".$serviceName);
        $folder=split("\.",$this->host_id);
        if (count($folder)!=2) {
            throw new Exception("wrong hostId");
        }

        $cserviceDAO=new configServiceDAO($folder[1]);
        return (array_search($serviceName.".xml",$cserviceDAO->elements)!==false);

    }
    function getConfigFile() {
        $rc = new ReflectionClass(get_class($this));
        return dirname($rc->getFileName())."/config.xml";
    }
    function serviceDAO($host_id) {
        $this->host_id=$host_id;
        $this->loadFile($host_id);
    }

    function getId($serviceName) {
        return $this->host_id.".".$serviceName;
    }

    function getName($serviceId) {
        if (strpos($serviceId, $this->host_id)===false) {
            throw new Exception("wrong host id");
        }

        $start=strlen($this->host_id)+1;
        $length=strpos($serviceId, ".", $start);
        if ($length===false) {
            return substr($serviceId, $start);
        }
        $length=$length-$start;
        return substr($serviceId, $start, $length);
    }

    function addservice($service_id) {
        $element=$this->xmlDom->createElement("service");
        $element->setAttribute("xml:id",$this->host_id.".".$service_id);
        $this->xmlDom->getElementById($this->host_id)->appendChild($element);

        return $service_id;
    }

    function removeservice($service_id) {
        $element=$this->xmlDom->getElementById($this->host_id);
        $element->removeChild($this->xmlDom->getElementById($this->host_id.".".$service_id));
    }

    function getServices() {
        $service_id=array();

        $host = $this->xmlDom->getElementById($this->host_id);
        if ($host) {
            foreach ($host->getElementsByTagName("service") as $service) {
                $service_id[] = $service->attributes->getNamedItem("id")->nodeValue;
            }
        }
        return $service_id;
    }

    function contains($service_id) {
        return in_array($this->host_id.".".$service_id, $this->getServices());
    }

    function isEmpty() {
        return count($this->getServices())==0;
    }

}
?>
