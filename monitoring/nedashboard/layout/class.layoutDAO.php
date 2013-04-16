<?php
    class layoutDAO {
        var $xmlDom;
        var $dash_id="root";

        function getConfigFile() {
            $rc = new ReflectionClass(get_class($this));
            return dirname($rc->getFileName())."/configLayout.xml";
        }
    
        function layoutDAO() {
            $this->xmlDom=new DOMDocument();
            $this->xmlDom->formatOutput=true;
            $this->xmlDom->load($this->getConfigFile());
        }
        
        function addBoard($board_id) {
            $element=$this->xmlDom->createElement("board");
            $element->setAttribute("xml:id",$board_id);
            $this->xmlDom->getElementById($this->dash_id)->appendChild($element);
            return $board_id;
        }
        
        function removeBoard($board_id) {
            $element=$this->xmlDom->getElementById($this->dash_id);
            $element->removeChild($this->xmlDom->getElementById($board_id));
        }
        
        function persist() {
            $this->xmlDom->save($this->getConfigFile());            
        }
        
        function getBoards() {
            $board_id=array();
            foreach ($this->xmlDom->getElementById($this->dash_id)->getElementsByTagName("board") as $board) {
                $board_id[] = $board->attributes->getNamedItem("id")->nodeValue;
            }
            return $board_id;
        }
        
    }
?>
