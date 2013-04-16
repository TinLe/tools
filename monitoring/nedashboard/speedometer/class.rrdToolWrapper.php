<?php
include_once 'class.abstractDataProvider.php';

    class rrdToolWrapper extends abstractDataProvider {
        var $db;
        function rrdToolWrapper($db) {
            $this->db=$db;
        }
    
        function status() {
            $output=shell_exec("rrdtool"." "."info"." ".$this->db);
            $output=preg_split("/\n/", $output);

            return $this->getStep($output)*min($this->getRRAsteps($output));
        }
        
        function getStep($stringArr) {
            $count=0;
            while ($count<count($stringArr) and !stristr($stringArr[$count], "step")) {
                $count++;
            }

            if ($count == count($stringArr)) {
                throw new Exception("no step found");
            }

            $val = preg_split("/=/", $stringArr[$count]);
            if (count($val)<2) {
                throw new Exception("no step found");
            }
            return trim($val[1]);
        }
        
        function getRRAsteps($stringArr) {
            $steps=array();
            $count=0;

            while ($count<count($stringArr)) {
                while ($count<count($stringArr) and !stristr($stringArr[$count], "AVERAGE")) {
                    $count++;
                }

                if ($count<count($stringArr)) {
                    $count=$count+2;
                    
                    if ($count > count($stringArr) or !stristr($stringArr[$count], "pdp_per_row")) {
                        throw new Exception("no step found");
                    }
                    
                    $val = preg_split("/=/", $stringArr[$count]);
                    if (count($val)<2) {
                        throw new Exception("no step found");
                    }

                    $steps[]=trim($val[1]);
                }
            }
            return $steps;
        }
    
        function fetch($ds) {
            $output=shell_exec("rrdtool"." "."fetch"." ".$this->db." "."AVERAGE");
            $output=preg_split("/\n/", $output);

            //for last \n split
            $count=count($output)-1;

            $val=preg_split("/ /",$output[$count-1]);
            while(($count>0) and stristr($val[$ds], "nan")) {
                $count--;
                $val=preg_split("/ /",$output[$count]);
            }
            return $val[$ds];
        }

    }

?>
