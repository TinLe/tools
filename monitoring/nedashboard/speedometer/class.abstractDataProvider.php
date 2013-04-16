<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
abstract class abstractDataProvider {
    abstract function fetch($ds);
    abstract function status();
    abstract function isActive();
}
?>
