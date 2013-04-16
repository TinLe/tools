<?php
require_once(dirname(__FILE__)."/../../includes/config.NetEye.php.inc");
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class globalSettingsDAO {
    const myFile = "../../includes/config.php.inc";
    var $text;
    var $komments;

    function load() {
        $fh = fopen(dirname(__FILE__)."/".self::myFile, 'r');
        $content=fread($fh, filesize(dirname(__FILE__)."/".self::myFile));
        fclose($fh);

        $this->text=array();
        $this->komments=array();
        $parameters=split(";",$content);

        foreach ($parameters as $parameter) {
            if (!(stristr($parameter,"=")===false)) {
                $nameValue=split("=",$parameter,2);
                $nameString=split("\\$",$nameValue[0]);
                $name=trim($nameString[1]);
                $value=trim($nameValue[1]);
                
                $pos=stripos($nameString[0],"//");
                if (!($pos===false)) {
                    $komment=substr($nameString[0],$pos+2);
                    $this->komments[$name]=$komment;
                }
                $this->text[$name]=$value;
            }
        }
    }

    function setValue($name, $value) {
        $this->text[$name]=$value;
    }

    function getValue($name) {
        return $this->text[$name];
    }

    function getParameters() {
        $ret="";
        reset($this->text);
        while (list($key, $val) = each($this->text)) {
            if (isset ($this->komments[$key])) {
                $ret.="//".$this->komments[$key];
            }
            $ret.="$".$key."=".$val.";\n";
        }
        return $ret;
    }

    function persist() {
        $fh = fopen(dirname(__FILE__)."/".self::myFile, 'w') or die("<b>Can't open file: ".self::myFile."</b><br><br><table align=\"left\"><tr><td><a style=\"padding-left:10px;\" href=\"javascript:history.go(-1)\" class=\"button\"><span>&nbsp; <<< Back <<< &nbsp;</span></a></td></tr></table><script>
    alert(\"Error saving global settins! Please check file ".self::myFile." write permissions.\");
    window.location='../configGlobal.php';
	</script>");
        fwrite($fh, "<? \n".$this->getParameters()."\n ?>");
        fclose($fh);
    }
}

?>
