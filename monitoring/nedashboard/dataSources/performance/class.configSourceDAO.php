<?php
include_once("class.configDAO.php");

class configSourceDAO extends configDAO {
	

    function loadElements($base) {
        $base=self::root."/".$base;
                
        $this->elements = array();

        if (is_file($base.".xml")) {
            $handle = fopen($base.".xml", "r");
            $contents = fread($handle, filesize($base.".xml"));

            $xml=new SimpleXMLElement($contents);
            foreach ($xml->DATASOURCE as $source) {
                $this->elements[]=$source->DS;
            }
        }
    }
}
?>