<?php

include_once ("class.tableScanner.php");
include_once ("class.scannerFactory.php");

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class headerScanner {
    var $name;
    var $scanner;
    var $table;

    function headerScanner($text, $name) {
        $this->scanner=scannerFactory::createScanner($text);
        $this->name=$name;
    }

    function filterData() {
        $this->table=array();
        for ($i=0;$i<count($this->scanner->tables);$i++) {
            $hostCol=-1;
            $table=$this->scanner->tables[$i];
            $this->table[$i]=array();

            foreach ($table as $rowNum=>$row) {
                foreach ($row as $colNum=>$col) {
                    if ($rowNum==0 && stristr($col,$this->name)!==false) {
                        //remember colum position
                        $hostCol=$colNum;
                    } else if ($hostCol==$colNum && trim($col)!=tableScanner::EMTY && $col!="Average") {
                        //matches
                        $this->table[$i][]=$rowNum;
                    }
                }
            }
        }
    }

    /**
     * returns a two dimensional array containing all the colum values for the
     * marked row.
     */
    function getData() {
        $return=array();
        foreach($this->table as $tableNum=>$rowArray) {
            foreach ($rowArray as $rowNum=>$rowContent) {
                foreach ($this->scanner->tables[$tableNum][$rowContent] as $colNum=>$col) {
                    if (stristr($this->scanner->tables[$tableNum][0][$colNum],$this->name)!==false) {
                        $return[]=$col;
                    }
                }
            }
        }
        return $return;
    }
}
?>
