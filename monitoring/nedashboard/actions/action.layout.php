<?php
include "../layout/class.elementDAO.php";
include "../layout/class.element";

/**
 * this action manages the layout configuration.
 *
 * $_GET["element"]: all the elements forming the
 * layout [additional information is passed by appending a . to the elements id]
 */

$elementDAO = new elementDAO($_GET["board"]);
$elementDAO->removeAll();

foreach ($_GET["element"] as $element) {
    echo "<br>";

    $e=split("\\".element::getDelimiter(),$element);
    if (count($e)==2) {
        // type is not tacho => read type information

        $type=$e[0];
        if ($type=="title") {
            //type is title => read title string
            $value=$_GET[$type.element::getTitleDelimiter().$e[1]];

        } else if ($type=="image") {
            //type is image
            $value=$_GET[$type.element::getTitleDelimiter().$e[1]];
            if (($pos=stripos($value,"&end"))!==false) {
                $value=substr_replace($value,"&old", $pos, 4);
            }
        } else {
            //type is simple space
            $value="space";
        }
    } else {
        //type is tacho(either performance or avaiability)=> no type information needed
        $type=$e[1];
        if ($type=="Tacho") {
            //type is performance tacho
            $value=$e[2].".".$e[3].".".$e[4];
        } else {
            //type is avaiability tacho
            $value=$e[2];
            if (count($e)==4) {
                $value.=".".$e[3];
            }
        }
    }

    //add element, if it is not a tacho, it needs autogeneration of xml id.
	//echo "Debug: ".$element." Count: ".count($e)." VALUE: ".$value;
    $elementDAO->addElement($value,$type,!($type=="Tacho" || $type=="Avaiability"));
}
if (!$elementDAO->persist()){
	?><script>
    alert("Error saving layout configuration! Please check write permissions.");
	</script><?
}
?>
<script>
    window.location="../configLayout.php?board=<?= $_GET["board"]; ?>&msg=Board layout applied successfully";
</script>
