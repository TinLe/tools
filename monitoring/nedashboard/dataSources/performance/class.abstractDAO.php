<?php

abstract class abstractDAO {
    var $xmlDom;

    abstract function getConfigFile();

    function log($str) {
        //echo $str."<br>";
    }

    /**
     * if this function returns false,
     * validation failed and object will be removed.
     * 
     * @param <type> $childName name of the child to validate
     */
    abstract function validate($childName);

    abstract function getName($id);

    //Write XML
    function bringInForm($id) {
    	$this->log("Bring in form: $id");
        $id = str_replace(" ", "_32", $id);
        $id = str_replace("+", "_43", $id);
        return str_replace(":", "_58", $id);
    }

    //Read XML search '_' and put <space>
    function bringOutForm($id) {
    	 $this->log("Bring out form: $id");
         $id = str_replace("_32", " ", $id);
         $id = str_replace("_43", "+", $id);
         return str_replace("_58", ":", $id);
    }

    function loadFile($parentName) {
        $this->log("loadFile");
        $removed=false;
        $this->xmlDom=new DOMDocument();
        $this->xmlDom->formatOutput=true;
        $this->xmlDom->load($this->getConfigFile());

        $parent=$this->xmlDom->getElementById($parentName);
        if ($parent) {
            $i=0;
            while ($i<$parent->childNodes->length) {
                $child=$parent->childNodes->item($i);
                $this->log($child);
                $childName=$this->getName($child->attributes->getNamedItem("id")->nodeValue);
                $this->log("check ".$childName);
                if (!$this->validate($childName)) {
                    $this->log("validation failed!! REMOVE!! ");
                    $removed=true;
                    $parent->removeChild($child);
                } else {
                    $i++;
                }
            }
        }
        if ($removed) {
            $this->persist();
        }
    }

    function persist() {
        return $this->xmlDom->save($this->getConfigFile());
    }
}
?>
