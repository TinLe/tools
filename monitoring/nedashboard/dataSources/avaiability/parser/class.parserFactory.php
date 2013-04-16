<?php
include_once (dirname(__FILE__)."/class.htmlParser.php");

class ParserFactory {
    static function htmlParser_ForURL ($text) {
        return new htmlParser ($text);
    }

    static function createParser($url) {
        if (stristr($url, "http")!==false) {
            return self::htmlParser_ForURL($url);
        }
    }
}

?>
