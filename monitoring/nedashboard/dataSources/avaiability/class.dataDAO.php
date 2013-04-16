<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
include_once dirname(__FILE__).'/../performance/class.abstractDAO.php';

class dataDAO extends abstractDAO {
    var $dash_id;

    /*function bringInForm($id) {
       return str_replace(" ", "_", $id);
    }
    function bringOutForm($id) {
        return str_replace("_", " ", $id);
    }*/

    function getElements() {
        $host_id=array();
        if ($this->xmlDom->getElementById($this->dash_id)->hasChildNodes()) {
            foreach ($this->xmlDom->getElementById($this->dash_id)->child_nodes() as $element) {
                $host_id[] = $this->bringOutForm($element->attributes->getNamedItem("id")->nodeValue);
            }
        }
        return $host_id;
    }

        /**
     * if this function returns false,
     * validation failed and object will be removed.
     *
     * @param <type> $childName name of the child to validate
     */
    function validate($childName) {
        return true;
    }

    function getType($id) {
        $element = $this->xmlDom->getElementById($this->bringInForm($id));
        return $element->nodeName;
    }

    function getName($id) {
        $element = $this->xmlDom->getElementById($this->bringInForm($id));
        //$this->log("Get Name: ".$id." and return: ".$this->bringOutForm($element->attributes->getNamedItem("name")->nodeValue));
        //return $this->bringOutForm($element->attributes->getNamedItem("name")->nodeValue);
        return $this->bringOutForm($element->attributes->getNamedItem("name")->nodeValue);
    }

    function setName($id, $name) {
        $element = $this->xmlDom->getElementById($this->bringInForm($id));
        $element->setAttribute("name",$name);
    }

    function getConfigFile() {
        $rc = new ReflectionClass(get_class($this));
        return dirname($rc->getFileName())."/config.xml";
    }
}

?>
