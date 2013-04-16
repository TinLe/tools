<?php
include_once("class.configDAO.php");

class configHostDAO extends configDAO {
	
    function loadElements($base) {
        $base=self::root."/".$base;
        
        if (!is_dir($base)) {
            die("no host detected");
        }
        if (!$handle=opendir($base)) {
            die("could not open directory");
        }
        while (($file = readdir($handle))!==false) {
            if ($file!="." && $file!="..")
            $this->elements[]=$file;
        }
        closedir($handle);
    }
}
?>
