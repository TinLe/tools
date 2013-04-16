<?php
include_once("class.sourceDTO.php");
include_once 'class.abstractDAO.php';
include_once 'class.configSourceDAO.php';
include_once(dirname(__FILE__)."/../../includes/config.NetEye.php.inc");

class sourceDAO extends abstractDAO {
    //const root="/var/log/nagios/perfdata/";
    const root=configureDAO::perfdataPath;
    var $service_id;

    function validate($sourceName) {
        $this->log("validate ".$this->service_id.".".$sourceName);
        $folder=split("\.",$this->service_id);
        if (count($folder)!=3) {
            throw new Exception("wrong serviceId");
        }

        $csourceDAO=new configSourceDAO($folder[1]."/".$folder[2]);
        return (array_search($sourceName,$csourceDAO->elements)!==false);
    }
    function getConfigFile() {
        $rc = new ReflectionClass(get_class($this));
        return dirname($rc->getFileName())."/config.xml";
    }
    function sourceDAO($service_id) {
        $this->service_id=$service_id;
        $this->loadFile($service_id);
    }

    function getId($sourceName) {
        return $this->service_id.".".$sourceName;
    }

    function getName($sourceId) {
        if (strpos($sourceId, $this->service_id)===false) {
            throw new Exception("wrong host id");
        }

        $start=strlen($this->service_id)+1;
        $length=strpos($sourceId, ".", $start);
        if ($length===false) {
            return substr($sourceId, $start);
        }
        $length=$length-$start;
        return substr($sourceId, $start, $length);
    }

    function addSource($source_id) {
        $element=$this->xmlDom->createElement("source");
        $element->setAttribute("xml:id",$this->service_id.".".$source_id);
        $this->xmlDom->getElementById($this->service_id)->appendChild($element);

        return $source_id;
    }

    function removeSource($source_id) {
        $element=$this->xmlDom->getElementById($this->service_id);
        $source = $this->xmlDom->getElementById($this->service_id.".".$source_id);
        if ($source!=null) {
            $element->removeChild($source);
        }
    }

    function getSources() {
        $source_id=array();

        $service = $this->xmlDom->getElementById($this->service_id);

        if ($service) {
            foreach ($service->getElementsByTagName("source") as $source) {
                $source_id[] = $source->attributes->getNamedItem("id")->nodeValue;
            }
        }
        return $source_id;
    }

    function contains($sourceName) {
        return in_array($this->service_id.".".$sourceName, $this->getsources());
    }

    function isEmpty() {
        return count($this->getsources())==0;
    }

    function setDetails($source_id, $sourceDetails) {
        $source = $this->xmlDom->getElementById($this->service_id.".".$source_id);

        $source->setAttribute("min",$sourceDetails->getMin());
        $source->setAttribute("max",$sourceDetails->getMax());
        $source->setAttribute("label",$sourceDetails->getLabel());
        $source->setAttribute("unit",$sourceDetails->getUnit());
        $source->setAttribute("green",$sourceDetails->getGreen());
        $source->setAttribute("yellow",$sourceDetails->getYellow());
        $source->setAttribute("red",$sourceDetails->getRed());
        $source->setAttribute("step",$sourceDetails->getstep());
    }

    function getDetails($source_id) {
        $source = $this->xmlDom->getElementById($this->service_id.".".$source_id);

        $sourceDTO = new sourceDTO();
        $sourceDTO->setDetails(
            $source->getAttribute("min"),
            $source->getAttribute("max"),
            $source->getAttribute("label"),
            $source->getAttribute("unit"),
            $source->getAttribute("green"),
            $source->getAttribute("yellow"),
            $source->getAttribute("red"),
            $source->getAttribute("step")
        );

        return $sourceDTO;
    }

    function getValue($source_id) {
        $source = $this->xmlDom->getElementById($this->service_id.".".$source_id);
        $return = $source->getAttribute("act");
        return $return;
    }
}
?>
