<?php
/**
 * PHP Template.
 */
abstract class configForm {

     abstract function aL($str);

    abstract function createJS($callBack1, $callBack2);

    abstract function createFormTable($info,$selected_right,$title);

    abstract function createFormTableCustom($info,$label_left,$label_right,$title);

    abstract function createOptions1($label_left);

    abstract function createOptions2($label_right);
}
?>