<?php
include_once (dirname(__FILE__)."/../parser/class.parserFactory.php");

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class tableScanner {
    var $text;
    const HEADER=1;
    const VALUE=2;

    var $state;
    var $pos;

    var $rowCount;

    var $tables;
    const EMTY="none";
    var $table;

    function tableScanner($text) {
        $this->text=$text;
        $this->tables=array();
    }

    function scan($parser) {
        while ($parser->parse()) {
            if($parser->iNodeType==NODE_TYPE_ELEMENT) {
                if (stristr($parser->iNodeName,"table")!==false) {
                    $this->table=array();
                    $this->rowCount=0;
                } else if(stristr($parser->iNodeName,"th")!==false) {
                    $this->state=self::HEADER;
                } else if(stristr($parser->iNodeName,"td")!==false) {
                    $this->state=self::VALUE;
                    $this->table[$this->rowCount][$this->pos]="none";
                } else if(stristr($parser->iNodeName,"tr")!==false) {
                    $this->table[$this->rowCount]=array();
                    $this->pos=0;
                }
            } else if($parser->iNodeType==NODE_TYPE_ENDELEMENT) {
                if (stristr($parser->iNodeName,"table")!==false) {
                    $this->tables[]=$this->table;
                } else if(stristr($parser->iNodeName,"th")!==false) {
                    $this->pos++;
                } else if(stristr($parser->iNodeName,"td")!==false) {
                    //check for emtyness
                    $this->pos++;
                } else if(stristr($parser->iNodeName,"tr")!==false) {
                    $this->rowCount++;
                }
                $this->state=-1;
            } else if ($parser->iNodeType==NODE_TYPE_TEXT) {
                $this->text($parser->iNodeValue);
            }
        }
    }

    function text($string) {
        $this->table[$this->rowCount][$this->pos]=$string;
    }
}
?>
