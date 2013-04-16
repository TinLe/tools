<?php

include_once ("class.tableScanner.php");
include_once ("class.scannerFactory.php");

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class dataScanner {
    var $name;
    var $scanner;
    var $table;

    function dataScanner($text, $name) {
        $this->scanner=scannerFactory::createScanner($text);
        $this->name=$name;
    }

    /**
     * Filters all the tables found during page parsing for rows having the colum
     * named with $this->name set to the value $value.
     * The rows passing the filter are stored in the $this->table array.
     * NOTE: also rows which have no value for the colum $this->name but comes after a detection,
     * will be stored.
     *
     * EX:
     * | th1 | th2 | th3 | th4 |
     * |-----|-----|-----|-----|
     * | yes | bla | bla | bla |
     * |     | bla | bla | bla |
     * |     | bla | bla | bla |
     * | no  | bla | bla | bla |
     * |-----------------------|
     *
     * if $this->name is "th1" and $value is "yes" than row 1,2 and 3 are marked
     * for this table. row 4 not, because the th1 colum contains a value which is
     * different from yes.
     */
    function filterData($value) {
        $this->table=array();
        for ($i=0;$i<count($this->scanner->tables);$i++) {
            $hostCol=-1;
            $table=$this->scanner->tables[$i];
            $this->table[$i]=array();

            $catched=false;
            foreach ($table as $rowNum=>$row) {
                foreach ($row as $colNum=>$col) {
                    if ($rowNum==0 && stristr($col,$this->name)!==false) {
                        //remember colum position
                        $hostCol=$colNum;
                    } else if ($hostCol==$colNum && stristr($col,$value)) {
                        //first match
                        //remember that match occured in $catched variable
                        $catched=true;
                        $this->table[$i][]=$rowNum;
                    } else if ($hostCol==$colNum && $catched && trim($col)==tableScanner::EMTY) {
                        //additional matches
                        $this->table[$i][]=$rowNum;
                    } else if ($hostCol==$colNum) {
                        //end of matches
                        $catched=false;
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
                $return[]=array();
                foreach ($this->scanner->tables[$tableNum][$rowContent] as $colNum=>$col) {
                    if (stristr($this->scanner->tables[$tableNum][0][$colNum],$this->name)===false) {
                        $return[][]=$col;
                    }
                }
            }
        }
        return $return;
    }

    function getCol($name) {
        $return=array();
        foreach($this->table as $tableNum=>$rowArray) {
            foreach ($rowArray as $rowNum=>$rowContent) {
                foreach ($this->scanner->tables[$tableNum][$rowContent] as $colNum=>$col) {
                    if (stristr($this->scanner->tables[$tableNum][0][$colNum],$name)!==false) {
                        $return[]=$col;
                    }
                }
            }
        }
        return $return;
    }

    function filter($header, $value) {
        $newContent=array();
        
        foreach($this->table as $tableNum=>$rowArray) {
            $newContent[$tableNum]=array();
            foreach ($rowArray as $rowNum=>$rowContent) {
                foreach ($this->scanner->tables[$tableNum][$rowContent] as $colNum=>$col) {
                    if (stristr($this->scanner->tables[$tableNum][0][$colNum],$header)!==false) {
                        if ($col==$value) {
                            $newContent[$tableNum][$rowNum]=$rowContent;
                        }
                    }
                }
            }
        }
        $this->table=$newContent;
    }
}
?>
